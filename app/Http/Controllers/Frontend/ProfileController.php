<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Rating;
use App\Models\Penalty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Display user's profile page
     */
    public function show(User $user)
    {
        $user->load([
            'items' => fn($q) => $q->available()->limit(6),
            'ratingsReceived.rater:id,name',
        ]);

        // Profile stats
        $profileStats = [
            'total_items_listed' => $user->items()->count(),
            'transactions_completed' => Transaction::where('borrower_id', $user->id)
                ->orWhereHas('item', fn($q) => $q->where('user_id', $user->id))
                ->where('status', 'completed')
                ->count(),
            'average_rating' => round($user->averageRating(), 2),
            'total_ratings' => $user->ratingsReceived()->count(),
            'member_since' => $user->created_at->format('F Y'),
            'reputation_level' => $this->getReputationLevel($user),
        ];

        // Recent ratings
        $recentRatings = $user->ratingsReceived()
            ->with('rater:id,name')
            ->latest()
            ->take(5)
            ->get();

        // Rating distribution
        $ratingDistribution = [
            5 => $user->ratingsReceived()->where('rating', 5)->count(),
            4 => $user->ratingsReceived()->where('rating', 4)->count(),
            3 => $user->ratingsReceived()->where('rating', 3)->count(),
            2 => $user->ratingsReceived()->where('rating', 2)->count(),
            1 => $user->ratingsReceived()->where('rating', 1)->count(),
        ];

        $canViewFull = Auth::check() && (Auth::id() === $user->id || Auth::user()->isAdmin());

        return view('frontend.profile.show', compact(
            'user',
            'profileStats',
            'recentRatings',
            'ratingDistribution',
            'canViewFull'
        ));
    }

    /**
     * Show edit profile form
     */
    public function edit()
    {
        $user = Auth::user();
        return view('frontend.profile.edit', compact('user'));
    }

    /**
     * Update user profile
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:500',
            'location' => 'nullable|string|max:255',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            // Delete old image if exists
            if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
                Storage::disk('public')->delete($user->profile_image);
            }
            $validated['profile_image'] = $request->file('profile_image')
                ->store('profile-images', 'public');
        }

        $user->update($validated);

        return redirect()->route('frontend.profile.show', $user)
            ->with('success', 'Profile updated successfully!');
    }

    /**
     * Show change password form
     */
    public function editPassword()
    {
        return view('frontend.profile.change-password');
    }

    /**
     * Update user password
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('frontend.profile.edit')
            ->with('success', 'Password changed successfully!');
    }

    /**
     * Show preferences/settings
     */
    public function preferences()
    {
        $user = Auth::user();

        $preferences = [
            'email_notifications' => $user->email_notifications ?? true,
            'sms_notifications' => $user->sms_notifications ?? false,
            'push_notifications' => $user->push_notifications ?? true,
            'show_email_publicly' => $user->show_email_publicly ?? false,
            'show_phone_publicly' => $user->show_phone_publicly ?? false,
            'allow_direct_messaging' => $user->allow_direct_messaging ?? true,
        ];

        return view('frontend.profile.preferences', compact('user', 'preferences'));
    }

    /**
     * Update preferences
     */
    public function updatePreferences(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'email_notifications' => 'boolean',
            'sms_notifications' => 'boolean',
            'push_notifications' => 'boolean',
            'show_email_publicly' => 'boolean',
            'show_phone_publicly' => 'boolean',
            'allow_direct_messaging' => 'boolean',
        ]);

        $user->update($validated);

        return back()->with('success', 'Preferences updated successfully!');
    }

    /**
     * Display user's public items
     */
    public function items(User $user)
    {
        $items = $user->items()
            ->with(['activeTransaction.borrower:id,name', 'ratings'])
            ->withCount('ratings')
            ->available()
            ->paginate(12);

        return view('frontend.profile.items', compact('user', 'items'));
    }

    /**
     * Display user's transaction history
     */
    public function transactionHistory(User $user)
    {
        $canViewFull = Auth::check() && (Auth::id() === $user->id || Auth::user()->isAdmin());

        // Borrowing history
        $borrowingHistory = $user->transactionsAsBorrower()
            ->where('status', 'completed')
            ->with(['item:id,title,user_id', 'item.owner:id,name', 'ratings'])
            ->latest()
            ->paginate(10);

        // Lending history
        $lendingHistory = Transaction::whereHas('item', fn($q) => $q->where('user_id', $user->id))
            ->where('status', 'completed')
            ->with(['item:id,title', 'borrower:id,name', 'ratings'])
            ->latest()
            ->paginate(10);

        return view('frontend.profile.transaction-history', compact(
            'user',
            'borrowingHistory',
            'lendingHistory',
            'canViewFull'
        ));
    }

    /**
     * Get user public profile data (JSON API)
     */
    public function publicApi(User $user)
    {
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->show_email_publicly ? $user->email : null,
            'phone' => $user->show_phone_publicly ? $user->phone : null,
            'bio' => $user->bio,
            'location' => $user->location,
            'profile_image' => $user->profile_image 
                ? asset("storage/{$user->profile_image}") 
                : null,
            'average_rating' => round($user->averageRating(), 2),
            'total_ratings' => $user->ratingsReceived()->count(),
            'member_since' => $user->created_at->format('F Y'),
            'reputation_level' => $this->getReputationLevel($user),
            'items_count' => $user->items()->count(),
            'profile_url' => route('frontend.profile.show', $user),
        ]);
    }

    /**
     * Delete user account
     */
    public function deleteAccount()
    {
        return view('frontend.profile.delete-account');
    }

    /**
     * Confirm account deletion
     */
    public function confirmDelete(Request $request)
    {
        $user = Auth::user();

        // Verify password
        $validated = $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        // Check if user has active transactions
        $activeTransactions = Transaction::where('borrower_id', $user->id)
            ->orWhereHas('item', fn($q) => $q->where('user_id', $user->id))
            ->whereIn('status', ['pending', 'active'])
            ->exists();

        if ($activeTransactions) {
            return back()->with('error', 'Cannot delete account with active transactions. Complete or cancel all transactions first.');
        }

        // Delete profile image
        if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
            Storage::disk('public')->delete($user->profile_image);
        }

        // Delete all item images
        $items = $user->items()->get();
        foreach ($items as $item) {
            if ($item->image_path && Storage::disk('public')->exists($item->image_path)) {
                Storage::disk('public')->delete($item->image_path);
            }
        }

        // Delete user and cascade delete related records
        $user->delete();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')
            ->with('success', 'Your account has been deleted successfully.');
    }

    /**
     * Get user's active items
     */
    public function activeItems()
    {
        $items = Auth::user()
            ->items()
            ->where('status', '!=', 'sold')
            ->with(['activeTransaction.borrower:id,name', 'ratings'])
            ->latest()
            ->paginate(10);

        return view('frontend.profile.active-items', compact('items'));
    }

    /**
     * Get user's transaction statistics
     */
    public function statistics(User $user)
    {
        $canViewFull = Auth::check() && (Auth::id() === $user->id || Auth::user()->isAdmin());

        if (!$canViewFull) {
            abort(403, 'Unauthorized to view these statistics.');
        }

        // Transaction stats
        $stats = [
            'total_borrowed' => $user->transactionsAsBorrower()->count(),
            'total_lent' => Transaction::whereHas('item', fn($q) => $q->where('user_id', $user->id))->count(),
            'completed_transactions' => Transaction::where('borrower_id', $user->id)
                ->orWhereHas('item', fn($q) => $q->where('user_id', $user->id))
                ->where('status', 'completed')
                ->count(),
            'cancelled_transactions' => Transaction::where('borrower_id', $user->id)
                ->orWhereHas('item', fn($q) => $q->where('user_id', $user->id))
                ->where('status', 'cancelled')
                ->count(),
            'average_rating_given' => round(Rating::where('rater_id', $user->id)->avg('rating'), 2),
            'average_rating_received' => round($user->averageRating(), 2),
            'total_penalties_incurred' => Penalty::forBorrower($user)->count(),
            'unpaid_penalties' => Penalty::borrowerTotalPending($user),
        ];

        // Success rate
        $stats['success_rate'] = $this->calculateSuccessRate($user);
        $stats['on_time_return_rate'] = $this->calculateOnTimeReturnRate($user);

        // Most active months
        $mostActiveMonths = $this->getMostActiveMonths($user);

        return view('frontend.profile.statistics', compact('user', 'stats', 'mostActiveMonths'));
    }

    /**
     * Export profile data
     */
    public function exportData()
    {
        $user = Auth::user();

        $filename = "profile_data_{$user->id}_" . now()->format('Y-m-d') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($user) {
            $file = fopen('php://output', 'w');

            // User info
            fputcsv($file, ['User Information']);
            fputcsv($file, ['Name', 'Email', 'Phone', 'Member Since']);
            fputcsv($file, [$user->name, $user->email, $user->phone ?? 'N/A', $user->created_at->format('Y-m-d')]);
            fputcsv($file, []);

            // Transactions
            fputcsv($file, ['Transaction History']);
            fputcsv($file, ['Date', 'Item', 'Type', 'Status', 'Amount']);

            $transactions = Transaction::where('borrower_id', $user->id)
                ->orWhereHas('item', fn($q) => $q->where('user_id', $user->id))
                ->with('item:id,title')
                ->latest()
                ->get();

            foreach ($transactions as $t) {
                fputcsv($file, [
                    $t->created_at->format('Y-m-d'),
                    $t->item->title,
                    $t->type,
                    $t->status,
                    $t->final_price ?? $t->deposit_amount ?? 'N/A',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get user's badges/achievements
     */
    public function badges(User $user)
    {
        $badges = [];

        $avgRating = $user->averageRating();
        $totalTransactions = Transaction::where('borrower_id', $user->id)
            ->orWhereHas('item', fn($q) => $q->where('user_id', $user->id))
            ->count();

        // Rating badges
        if ($avgRating >= 4.5) {
            $badges[] = [
                'name' => 'Excellent Rating',
                'icon' => '⭐⭐⭐⭐⭐',
                'description' => 'Maintained 4.5+ average rating',
                'earned_date' => now(),
            ];
        }

        if ($avgRating >= 4) {
            $badges[] = [
                'name' => 'Trusted Member',
                'icon' => '✓',
                'description' => 'Maintained 4+ average rating',
                'earned_date' => now(),
            ];
        }

        // Transaction badges
        if ($totalTransactions >= 10) {
            $badges[] = [
                'name' => 'Active Trader',
                'icon' => '📦',
                'description' => 'Completed 10+ transactions',
                'earned_date' => now(),
            ];
        }

        if ($totalTransactions >= 25) {
            $badges[] = [
                'name' => 'Power Trader',
                'icon' => '⚡',
                'description' => 'Completed 25+ transactions',
                'earned_date' => now(),
            ];
        }

        // Item badges
        if ($user->items()->count() >= 5) {
            $badges[] = [
                'name' => 'Resource Provider',
                'icon' => '🎁',
                'description' => 'Listed 5+ items',
                'earned_date' => now(),
            ];
        }

        // On-time return badge
        if ($this->calculateOnTimeReturnRate($user) >= 95) {
            $badges[] = [
                'name' => 'Reliable Returner',
                'icon' => '⏰',
                'description' => 'Returned 95%+ of items on time',
                'earned_date' => now(),
            ];
        }

        return view('frontend.profile.badges', compact('user', 'badges'));
    }

    // ========================================
    // Helper Methods
    // ========================================

    /**
     * Get user's reputation level
     */
    private function getReputationLevel(User $user): string
    {
        $avgRating = $user->averageRating();
        $transactionCount = Transaction::where('borrower_id', $user->id)
            ->orWhereHas('item', fn($q) => $q->where('user_id', $user->id))
            ->count();

        if ($avgRating >= 4.5 && $transactionCount >= 10) {
            return 'Excellent';
        } elseif ($avgRating >= 4 && $transactionCount >= 5) {
            return 'Very Good';
        } elseif ($avgRating >= 3.5) {
            return 'Good';
        } elseif ($avgRating >= 3) {
            return 'Fair';
        } else {
            return 'New Member';
        }
    }

    /**
     * Calculate success rate
     */
    private function calculateSuccessRate(User $user): float
    {
        $total = Transaction::where('borrower_id', $user->id)
            ->orWhereHas('item', fn($q) => $q->where('user_id', $user->id))
            ->whereIn('status', ['completed', 'late', 'cancelled'])
            ->count();

        if ($total === 0) return 0;

        $completed = Transaction::where('borrower_id', $user->id)
            ->orWhereHas('item', fn($q) => $q->where('user_id', $user->id))
            ->where('status', 'completed')
            ->count();

        return round(($completed / $total) * 100, 2);
    }

    /**
     * Calculate on-time return rate
     */
    private function calculateOnTimeReturnRate(User $user): float
    {
        $completed = $user->transactionsAsBorrower()
            ->where('status', 'completed')
            ->count();

        if ($completed === 0) return 0;

        $onTime = $user->transactionsAsBorrower()
            ->where('status', 'completed')
            ->whereRaw('return_date <= due_date')
            ->count();

        return round(($onTime / $completed) * 100, 2);
    }

    /**
     * Get most active months
     */
    private function getMostActiveMonths(User $user): array
    {
        return Transaction::where('borrower_id', $user->id)
            ->orWhereHas('item', fn($q) => $q->where('user_id', $user->id))
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->groupBy('month')
            ->orderByDesc('count')
            ->get()
            ->map(fn($m) => ['month' => $m->month, 'count' => $m->count])
            ->toArray();
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Penalty;
use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class AdminUserManagementController extends Controller
{
    /**
     * Display list of all users
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Search by name or email
        if ($search = $request->get('search')) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        }

        // Filter by role
        if ($role = $request->get('role')) {
            if ($role === 'admin') {
                $query->where('is_admin', true);
            } elseif ($role === 'user') {
                $query->where('is_admin', false);
            }
        }

        // Filter by status
        if ($status = $request->get('status')) {
            if ($status === 'flagged') {
                $query->whereHas('penalties', function($q) {
                    $q->where('status', 'pending');
                });
            } elseif ($status === 'inactive') {
                $query->where('created_at', '<', now()->subDays(90))
                    ->whereDoesntHave('transactionsAsBorrower', function($q) {
                        $q->where('created_at', '>=', now()->subDays(30));
                    });
            } elseif ($status === 'low_rated') {
                $query->withAvg('ratingsReceived', 'rating')
                    ->having('ratingsReceived_avg_rating', '<', 3);
            }
        }

        // Sort options
        $sort = $request->get('sort', 'recent');
        switch ($sort) {
            case 'name':
                $query->orderBy('name');
                break;
            case 'recent':
                $query->latest('created_at');
                break;
            case 'active':
                $query->withCount('transactionsAsBorrower')
                    ->orderByDesc('transactions_as_borrower_count');
                break;
            default:
                $query->latest('created_at');
        }

        // Paginate with related data
        $users = $query->with(['ratingsReceived'])
            ->withCount(['items', 'transactionsAsBorrower', 'penalties'])
            ->paginate(15);

        // Add computed properties
        $users->getCollection()->transform(function($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_admin' => $user->is_admin,
                'profile_image' => $user->profile_image,
                'created_at' => $user->created_at,
                'items_count' => $user->items_count,
                'transactions_count' => $user->transactions_as_borrower_count,
                'penalties_count' => $user->penalties_count,
                'avg_rating' => round($user->averageRating(), 2),
                'status' => $this->getUserStatus($user),
                'last_activity' => $this->getLastActivity($user),
            ];
        });

        return view('admin.users.index', compact('users', 'request'));
    }

    /**
     * Show user details
     */
    public function show(User $user)
    {
        $user->load([
            'items' => fn($q) => $q->latest()->limit(10),
            'transactionsAsBorrower' => fn($q) => $q->with('item:id,title')->latest()->limit(10),
            'ratingsReceived' => fn($q) => $q->with('rater:id,name')->latest()->limit(5),
            'penalties' => fn($q) => $q->with('transaction.item:id,title')->latest()->limit(5),
        ]);

        // User stats
        $stats = [
            'total_items' => $user->items()->count(),
            'available_items' => $user->items()->where('status', 'available')->count(),
            'borrowed_items' => $user->items()->where('status', 'borrowed')->count(),
            'sold_items' => $user->items()->where('status', 'sold')->count(),
            'total_transactions' => Transaction::where('borrower_id', $user->id)
                ->orWhereHas('item', fn($q) => $q->where('user_id', $user->id))
                ->count(),
            'completed_transactions' => Transaction::where('borrower_id', $user->id)
                ->where('status', 'completed')
                ->count(),
            'average_rating' => round($user->averageRating(), 2),
            'total_ratings' => $user->ratingsReceived()->count(),
            'unpaid_penalties' => Penalty::where('user_id', $user->id)
                ->where('status', 'pending')
                ->sum('amount'),
        ];

        // Activity timeline
        $activities = $this->getUserActivityTimeline($user);

        return view('admin.users.show', compact('user', 'stats', 'activities'));
    }

    /**
     * Show user edit form
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update user information
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'is_admin' => 'boolean',
        ]);

        $user->update($validated);

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'User updated successfully!');
    }

    /**
     * Delete user account
     */
    public function destroy(User $user)
    {
        // Prevent deleting own account
        if (auth()->id() === $user->id) {
            return back()->with('error', 'Cannot delete your own account.');
        }

        // Check for active transactions
        $activeTransactions = Transaction::where('borrower_id', $user->id)
            ->orWhereHas('item', fn($q) => $q->where('user_id', $user->id))
            ->whereIn('status', ['pending', 'active'])
            ->exists();

        if ($activeTransactions) {
            return back()->with('error', 'Cannot delete user with active transactions.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Block/unblock user
     */
    public function toggleBlock(User $user)
    {
        // Prevent blocking own account
        if (auth()->id() === $user->id) {
            return back()->with('error', 'Cannot block your own account.');
        }

        $blocked = $user->is_blocked ?? false;
        $user->update(['is_blocked' => !$blocked]);

        $status = !$blocked ? 'blocked' : 'unblocked';

        return back()->with('success', "User {$status} successfully!");
    }

    /**
     * Promote user to admin
     */
    public function promoteAdmin(User $user)
    {
        if ($user->is_admin) {
            return back()->with('warning', 'User is already an admin.');
        }

        $user->update(['is_admin' => true]);

        return back()->with('success', 'User promoted to admin successfully!');
    }

    /**
     * Demote admin to user
     */
    public function demoteAdmin(User $user)
    {
        // Prevent demoting self
        if (auth()->id() === $user->id) {
            return back()->with('error', 'Cannot demote your own account.');
        }

        if (!$user->is_admin) {
            return back()->with('warning', 'User is not an admin.');
        }

        $user->update(['is_admin' => false]);

        return back()->with('success', 'Admin demoted to user successfully!');
    }

    /**
     * Verify user email (manual)
     */
    public function verifyEmail(User $user)
    {
        if ($user->email_verified_at) {
            return back()->with('warning', 'Email already verified.');
        }

        $user->update(['email_verified_at' => now()]);

        return back()->with('success', 'Email verified manually!');
    }

    /**
     * Resend verification email
     */
    public function resendVerification(User $user)
    {
        if ($user->email_verified_at) {
            return back()->with('warning', 'Email already verified.');
        }

        // TODO: Send verification email
        // Notification::send($user, new VerifyEmailNotification());

        return back()->with('success', 'Verification email sent!');
    }

    /**
     * Reset user password
     */
    public function resetPassword(User $user)
    {
        $newPassword = Str::random(12);
        $user->update(['password' => Hash::make($newPassword)]);

        // TODO: Send password reset email with temporary password

        return back()->with('success', "Password reset to: {$newPassword} (should be emailed)");
    }

    /**
     * Issue warning to user
     */
    public function issueWarning(Request $request, User $user)
    {
        $validated = $request->validate([
            'reason' => 'required|string|min:10|max:500',
            'severity' => ['required', Rule::in(['low', 'medium', 'high'])],
        ]);

        // TODO: Create warning/violation model to track
        // Warning::create([
        //     'user_id' => $user->id,
        //     'reason' => $validated['reason'],
        //     'severity' => $validated['severity'],
        //     'issued_by' => auth()->id(),
        // ]);

        return back()->with('success', 'Warning issued to user.');
    }

    /**
     * View user's transactions
     */
    public function transactions(User $user, Request $request)
    {
        $status = $request->get('status');

        $transactions = Transaction::where('borrower_id', $user->id)
            ->orWhereHas('item', fn($q) => $q->where('user_id', $user->id));

        if ($status) {
            $transactions->where('status', $status);
        }

        $transactions = $transactions->with(['item:id,title', 'borrower:id,name'])
            ->latest()
            ->paginate(15);

        return view('admin.users.transactions', compact('user', 'transactions', 'status'));
    }

    /**
     * View user's penalties
     */
    public function penalties(User $user, Request $request)
    {
        $status = $request->get('status');

        $penalties = Penalty::whereHas('transaction', fn($q) => $q->where('borrower_id', $user->id));

        if ($status) {
            $penalties->where('status', $status);
        }

        $penalties = $penalties->with('transaction.item:id,title')
            ->latest()
            ->paginate(15);

        return view('admin.users.penalties', compact('user', 'penalties', 'status'));
    }

    /**
     * View user's ratings
     */
    public function ratings(User $user, Request $request)
    {
        $ratings = $user->ratingsReceived()
            ->with(['rater:id,name', 'transaction.item:id,title'])
            ->latest()
            ->paginate(15);

        $avgRating = $user->averageRating();
        $distribution = [
            5 => $user->ratingsReceived()->where('rating', 5)->count(),
            4 => $user->ratingsReceived()->where('rating', 4)->count(),
            3 => $user->ratingsReceived()->where('rating', 3)->count(),
            2 => $user->ratingsReceived()->where('rating', 2)->count(),
            1 => $user->ratingsReceived()->where('rating', 1)->count(),
        ];

        return view('admin.users.ratings', compact('user', 'ratings', 'avgRating', 'distribution'));
    }

    /**
     * Delete inappropriate rating
     */
    public function deleteRating(Rating $rating)
    {
        $user = $rating->transaction->item->owner;
        $rating->delete();

        return back()->with('success', 'Rating deleted successfully.');
    }

    /**
     * Waive user's pending penalty
     */
    public function waivePenalty(Penalty $penalty)
    {
        if ($penalty->status !== 'pending') {
            return back()->with('error', 'Only pending penalties can be waived.');
        }

        $penalty->update(['status' => 'waived']);

        return back()->with('success', "Penalty of ৳{$penalty->amount} waived.");
    }

    /**
     * Get user statistics (JSON API)
     */
    public function statistics(User $user)
    {
        return response()->json([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'is_admin' => $user->is_admin,
            'created_at' => $user->created_at->format('Y-m-d'),
            'items_count' => $user->items()->count(),
            'transactions_count' => Transaction::where('borrower_id', $user->id)
                ->orWhereHas('item', fn($q) => $q->where('user_id', $user->id))
                ->count(),
            'completed_transactions' => Transaction::where('borrower_id', $user->id)
                ->where('status', 'completed')
                ->count(),
            'average_rating' => round($user->averageRating(), 2),
            'total_ratings' => $user->ratingsReceived()->count(),
            'unpaid_penalties' => Penalty::whereHas('transaction', fn($q) => $q->where('borrower_id', $user->id))
                ->where('status', 'pending')
                ->sum('amount'),
        ]);
    }

    /**
     * Export user list as CSV
     */
    public function exportUsers(Request $request)
    {
        $users = User::all();

        $filename = "users_" . now()->format('Y-m-d') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($users) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Name', 'Email', 'Role', 'Items', 'Transactions', 'Avg Rating', 'Created At']);

            foreach ($users as $user) {
                fputcsv($file, [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->is_admin ? 'Admin' : 'User',
                    $user->items()->count(),
                    Transaction::where('borrower_id', $user->id)->count(),
                    round($user->averageRating(), 2),
                    $user->created_at->format('Y-m-d'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ========================================
    // Helper Methods
    // ========================================

    /**
     * Get user status
     */
    private function getUserStatus(User $user): string
    {
        if ($user->is_blocked ?? false) {
            return 'Blocked';
        }

        $avgRating = $user->averageRating();
        if ($avgRating < 3 && $user->ratingsReceived()->count() >= 3) {
            return 'Low Rated';
        }

        $unpaidPenalties = Penalty::whereHas('transaction', fn($q) => $q->where('borrower_id', $user->id))
            ->where('status', 'pending')
            ->count();
        
        if ($unpaidPenalties > 0) {
            return 'Flagged';
        }

        if (!$user->email_verified_at) {
            return 'Unverified';
        }

        return 'Active';
    }

    /**
     * Get user's last activity
     */
    private function getLastActivity(User $user): ?string
    {
        $lastTransaction = Transaction::where('borrower_id', $user->id)
            ->orWhereHas('item', fn($q) => $q->where('user_id', $user->id))
            ->latest('updated_at')
            ->first();

        if ($lastTransaction) {
            return $lastTransaction->updated_at->diffForHumans();
        }

        return null;
    }

    /**
     * Get user activity timeline
     */
    private function getUserActivityTimeline(User $user): array
    {
        $activities = [];

        // Account created
        $activities[] = [
            'type' => 'account_created',
            'title' => 'Account Created',
            'description' => 'User joined the platform',
            'timestamp' => $user->created_at,
            'icon' => 'user-plus',
        ];

        // Email verified
        if ($user->email_verified_at) {
            $activities[] = [
                'type' => 'email_verified',
                'title' => 'Email Verified',
                'description' => 'Email verified',
                'timestamp' => $user->email_verified_at,
                'icon' => 'check-circle',
            ];
        }

        // First item listed
        $firstItem = $user->items()->first();
        if ($firstItem) {
            $activities[] = [
                'type' => 'first_item',
                'title' => 'First Item Listed',
                'description' => "Listed '{$firstItem->title}'",
                'timestamp' => $firstItem->created_at,
                'icon' => 'package',
            ];
        }

        // First transaction
        $firstTransaction = Transaction::where('borrower_id', $user->id)->first();
        if ($firstTransaction) {
            $activities[] = [
                'type' => 'first_transaction',
                'title' => 'First Transaction',
                'description' => 'Participated in first transaction',
                'timestamp' => $firstTransaction->created_at,
                'icon' => 'arrow-right',
            ];
        }

        // First penalty (if any)
        $firstPenalty = Penalty::whereHas('transaction', fn($q) => $q->where('borrower_id', $user->id))->first();
        if ($firstPenalty) {
            $activities[] = [
                'type' => 'first_penalty',
                'title' => 'First Penalty',
                'description' => "Received penalty of ৳{$firstPenalty->amount}",
                'timestamp' => $firstPenalty->created_at,
                'icon' => 'alert-triangle',
            ];
        }

        // Sort by timestamp (newest first)
        usort($activities, fn($a, $b) => $b['timestamp'] <=> $a['timestamp']);

        return $activities;
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class AdminItemManagementController extends Controller
{
    /**
     * Display list of all items
     */
    public function index(Request $request)
    {
        $query = Item::query();

        // Search by title or description
        if ($search = $request->get('search')) {
            $query->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        }

        // Filter by status
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        // Filter by availability mode
        if ($mode = $request->get('mode')) {
            $query->where('availability_mode', $mode);
        }

        // Filter by owner
        if ($owner = $request->get('owner')) {
            $query->whereHas('owner', function($q) use ($owner) {
                $q->where('name', 'like', "%{$owner}%");
            });
        }

        // Filter by rating
        if ($minRating = $request->get('min_rating')) {
            $query->withAvg('ratings', 'rating')
                ->having('ratings_avg_rating', '<=', $minRating);
        }

        // Filter flagged items (for review)
        if ($request->get('flagged')) {
            $query->whereHas('ratings', function($q) {
                $q->where('rating', '<=', 2);
            });
        }

        // Sort options
        $sort = $request->get('sort', 'recent');
        switch ($sort) {
            case 'popular':
                $query->withCount('transactions')
                    ->orderByDesc('transactions_count');
                break;
            case 'highest_rated':
                $query->withAvg('ratings', 'rating')
                    ->orderByDesc('ratings_avg_rating');
                break;
            case 'lowest_rated':
                $query->withAvg('ratings', 'rating')
                    ->orderBy('ratings_avg_rating');
                break;
            case 'price_high':
                $query->orderByDesc('price');
                break;
            case 'price_low':
                $query->orderBy('price');
                break;
            case 'recent':
            default:
                $query->latest('created_at');
        }

        // Paginate with related data
        $items = $query->with(['owner:id,name,profile_image', 'activeTransaction.borrower:id,name'])
            ->withCount(['ratings', 'transactions'])
            ->paginate(15);

        // Add computed properties
        $items->getCollection()->transform(function($item) {
            return [
                'id' => $item->id,
                'title' => $item->title,
                'description' => $item->description,
                'owner' => $item->owner,
                'owner_name' => $item->owner->name,
                'price' => $item->price,
                'availability_mode' => $item->availability_mode,
                'status' => $item->status,
                'image_path' => $item->image_path,
                'ratings_count' => $item->ratings_count,
                'transactions_count' => $item->transactions_count,
                'avg_rating' => round($item->averageRating(), 2),
                'created_at' => $item->created_at,
                'health_status' => $this->getItemHealthStatus($item),
            ];
        });

        return view('admin.items.index', compact('items', 'request'));
    }

    /**
     * Show item details
     */
    public function show(Item $item)
    {
        $item->load([
            'owner:id,name,email,profile_image',
            'activeTransaction' => fn($q) => $q->with(['borrower:id,name', 'item']),
            'transactions' => fn($q) => $q->with(['borrower:id,name'])->latest()->limit(10),
            'ratings' => fn($q) => $q->with('rater:id,name')->latest()->limit(10),
        ]);

        // Item statistics
        $stats = [
            'total_transactions' => $item->transactions()->count(),
            'completed_transactions' => $item->transactions()->where('status', 'completed')->count(),
            'active_transactions' => $item->transactions()->where('status', 'active')->count(),
            'total_ratings' => $item->ratings()->count(),
            'avg_rating' => round($item->averageRating(), 2),
            'total_borrowed_times' => $item->totalBorrowCount(),
            'ratings_distribution' => $this->getRatingDistribution($item),
            'days_listed' => $item->created_at->diffInDays(now()),
        ];

        // Owner info
        $ownerStats = [
            'total_items' => $item->owner->items()->count(),
            'avg_rating' => round($item->owner->averageRating(), 2),
            'total_ratings' => $item->owner->ratingsReceived()->count(),
            'member_since' => $item->owner->created_at->format('F Y'),
        ];

        // Item health assessment
        $healthAssessment = $this->assessItemHealth($item);

        return view('admin.items.show', compact(
            'item',
            'stats',
            'ownerStats',
            'healthAssessment'
        ));
    }

    /**
     * Show edit form
     */
    public function edit(Item $item)
    {
        return view('admin.items.edit', compact('item'));
    }

    /**
     * Update item
     */
    public function update(Request $request, Item $item)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'availability_mode' => ['required', Rule::in(['lend', 'sell', 'both'])],
            'price' => 'nullable|numeric|min:0',
            'lending_duration_days' => 'nullable|integer|min:1|max:30',
            'pickup_location' => 'required|string|max:255',
            'status' => ['required', Rule::in(['available', 'borrowed', 'sold', 'reserved'])],
        ]);

        $item->update($validated);

        return redirect()->route('admin.items.show', $item)
            ->with('success', 'Item updated successfully!');
    }

    /**
     * Remove/delete item
     */
    public function destroy(Item $item)
    {
        // Check for active transactions
        if ($item->activeTransaction && $item->activeTransaction->status !== 'cancelled') {
            return back()->with('error', 'Cannot delete item with active transaction.');
        }

        // Delete image from storage
        if ($item->image_path && Storage::disk('public')->exists($item->image_path)) {
            Storage::disk('public')->delete($item->image_path);
        }

        $item->delete();

        return redirect()->route('admin.items.index')
            ->with('success', 'Item deleted successfully.');
    }

    /**
     * Flag item for review
     */
    public function flag(Request $request, Item $item)
    {
        $validated = $request->validate([
            'reason' => 'required|string|min:10|max:500',
            'severity' => ['required', Rule::in(['low', 'medium', 'high'])],
        ]);

        // TODO: Create ItemFlag model to track
        // ItemFlag::create([
        //     'item_id' => $item->id,
        //     'reason' => $validated['reason'],
        //     'severity' => $validated['severity'],
        //     'flagged_by' => auth()->id(),
        // ]);

        return back()->with('success', 'Item flagged for review.');
    }

    /**
     * Unflag item
     */
    public function unflag(Item $item)
    {
        // TODO: Remove flag from item
        // ItemFlag::where('item_id', $item->id)->delete();

        return back()->with('success', 'Item flag removed.');
    }

    /**
     * Approve item (if pending review)
     */
    public function approve(Item $item)
    {
        // TODO: Implement item approval system if needed
        $item->update(['status' => 'available']);

        return back()->with('success', 'Item approved.');
    }

    /**
     * Reject/remove inappropriate item
     */
    public function reject(Request $request, Item $item)
    {
        $validated = $request->validate([
            'reason' => 'required|string|min:10|max:500',
        ]);

        // Soft delete or mark as rejected
        $item->update(['status' => 'rejected']);

        // TODO: Notify owner about rejection
        // Notification::send($item->owner, new ItemRejectedNotification($item, $validated['reason']));

        return back()->with('success', 'Item rejected and owner notified.');
    }

    /**
     * View item's transactions
     */
    public function transactions(Item $item, Request $request)
    {
        $status = $request->get('status');

        $transactions = $item->transactions();

        if ($status) {
            $transactions->where('status', $status);
        }

        $transactions = $transactions->with(['borrower:id,name,email'])
            ->latest()
            ->paginate(15);

        return view('admin.items.transactions', compact('item', 'transactions', 'status'));
    }

    /**
     * View item's ratings/reviews
     */
    public function ratings(Item $item, Request $request)
    {
        $minRating = $request->get('min_rating');

        $ratings = $item->ratings()
            ->with(['rater:id,name', 'transaction.borrower:id,name']);

        if ($minRating) {
            $ratings->where('rating', '<=', $minRating);
        }

        $ratings = $ratings->latest()->paginate(15);

        $avgRating = $item->averageRating();
        $distribution = $this->getRatingDistribution($item);

        return view('admin.items.ratings', compact(
            'item',
            'ratings',
            'avgRating',
            'distribution'
        ));
    }

    /**
     * Delete inappropriate rating
     */
    public function deleteRating(Rating $rating)
    {
        $item = $rating->transaction->item;
        $rating->delete();

        return back()->with('success', 'Rating deleted successfully.');
    }

    /**
     * Resolve rating dispute
     */
    public function resolveDisputeRating(Request $request, Rating $rating)
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['keep', 'remove'])],
            'reason' => 'nullable|string|max:500',
        ]);

        if ($validated['action'] === 'remove') {
            $rating->delete();
            return back()->with('success', 'Rating removed.');
        }

        return back()->with('success', 'Dispute resolved - rating kept.');
    }

    /**
     * Get similar items (for quality check)
     */
    public function similar(Item $item)
    {
        $similarItems = Item::where('user_id', '!=', $item->user_id)
            ->where(function($q) use ($item) {
                $q->where('title', 'like', "%{$item->title}%")
                  ->orWhere('description', 'like', "%{$item->description}%");
            })
            ->with(['owner:id,name', 'ratings'])
            ->limit(10)
            ->get();

        return view('admin.items.similar', compact('item', 'similarItems'));
    }

    /**
     * Bulk actions on items
     */
    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['approve', 'reject', 'flag', 'delete'])],
            'items' => 'required|array',
            'items.*' => 'integer|exists:items,id',
            'reason' => 'nullable|string|max:500',
        ]);

        $items = Item::whereIn('id', $validated['items'])->get();

        switch ($validated['action']) {
            case 'approve':
                $items->each->update(['status' => 'available']);
                break;
            case 'reject':
                $items->each->update(['status' => 'rejected']);
                break;
            case 'flag':
                // TODO: Flag items
                break;
            case 'delete':
                $items->each(fn($item) => $item->delete());
                break;
        }

        return back()->with('success', ucfirst($validated['action']) . ' action completed.');
    }

    /**
     * Get items statistics (JSON API)
     */
    public function statistics(Item $item)
    {
        return response()->json([
            'item_id' => $item->id,
            'title' => $item->title,
            'owner_id' => $item->user_id,
            'owner_name' => $item->owner->name,
            'status' => $item->status,
            'availability_mode' => $item->availability_mode,
            'price' => $item->price,
            'created_at' => $item->created_at->format('Y-m-d'),
            'total_transactions' => $item->transactions()->count(),
            'completed_transactions' => $item->transactions()->where('status', 'completed')->count(),
            'active_transactions' => $item->transactions()->where('status', 'active')->count(),
            'avg_rating' => round($item->averageRating(), 2),
            'total_ratings' => $item->ratings()->count(),
            'total_borrowed_times' => $item->totalBorrowCount(),
            'days_listed' => $item->created_at->diffInDays(now()),
        ]);
    }

    /**
     * Export items list as CSV
     */
    public function exportItems(Request $request)
    {
        $items = Item::with('owner:id,name')
            ->get();

        $filename = "items_" . now()->format('Y-m-d') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($items) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Title', 'Owner', 'Mode', 'Status', 'Price', 'Transactions', 'Avg Rating', 'Created At']);

            foreach ($items as $item) {
                fputcsv($file, [
                    $item->id,
                    $item->title,
                    $item->owner->name,
                    $item->availability_mode,
                    $item->status,
                    $item->price ?? 'N/A',
                    $item->transactions()->count(),
                    round($item->averageRating(), 2),
                    $item->created_at->format('Y-m-d'),
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
     * Get item health status
     */
    private function getItemHealthStatus(Item $item): string
    {
        $avgRating = $item->averageRating();

        if ($avgRating < 2.5 && $item->ratings()->count() >= 3) {
            return 'Poor';
        } elseif ($avgRating < 3.5 && $item->ratings()->count() >= 3) {
            return 'Fair';
        } elseif ($avgRating >= 4 && $item->ratings()->count() >= 3) {
            return 'Excellent';
        } else {
            return 'Neutral';
        }
    }

    /**
     * Get rating distribution
     */
    private function getRatingDistribution(Item $item): array
    {
        return [
            5 => $item->ratings()->where('rating', 5)->count(),
            4 => $item->ratings()->where('rating', 4)->count(),
            3 => $item->ratings()->where('rating', 3)->count(),
            2 => $item->ratings()->where('rating', 2)->count(),
            1 => $item->ratings()->where('rating', 1)->count(),
        ];
    }

    /**
     * Assess overall item health
     */
    private function assessItemHealth(Item $item): array
    {
        $avgRating = $item->averageRating();
        $totalTransactions = $item->transactions()->count();
        $completedTransactions = $item->transactions()->where('status', 'completed')->count();
        $lateReturns = Transaction::where('item_id', $item->id)
            ->where('status', 'late')
            ->count();

        $successRate = $totalTransactions > 0 
            ? ($completedTransactions / $totalTransactions) * 100 
            : 0;

        $issues = [];

        if ($avgRating < 3 && $item->ratings()->count() >= 3) {
            $issues[] = 'Low average rating';
        }

        if ($lateReturns > $totalTransactions * 0.2) {
            $issues[] = 'High rate of late returns';
        }

        if ($successRate < 80 && $totalTransactions > 0) {
            $issues[] = 'Low transaction success rate';
        }

        return [
            'overall_score' => $this->calculateHealthScore($avgRating, $successRate, $lateReturns, $totalTransactions),
            'rating' => round($avgRating, 2),
            'success_rate' => round($successRate, 2),
            'late_returns' => $lateReturns,
            'total_transactions' => $totalTransactions,
            'issues' => $issues,
            'recommendation' => count($issues) > 0 ? 'Review' : 'OK',
        ];
    }

    /**
     * Calculate health score (0-100)
     */
    private function calculateHealthScore(float $avgRating, float $successRate, int $lateReturns, int $totalTransactions): int
    {
        $score = 100;

        // Rating impact
        if ($avgRating < 2) {
            $score -= 40;
        } elseif ($avgRating < 3) {
            $score -= 20;
        } elseif ($avgRating < 4) {
            $score -= 5;
        }

        // Success rate impact
        if ($successRate < 60) {
            $score -= 30;
        } elseif ($successRate < 80) {
            $score -= 15;
        }

        // Late returns impact
        if ($totalTransactions > 0) {
            $latePercentage = ($lateReturns / $totalTransactions) * 100;
            if ($latePercentage > 40) {
                $score -= 20;
            } elseif ($latePercentage > 20) {
                $score -= 10;
            }
        }

        return max($score, 0);
    }
}

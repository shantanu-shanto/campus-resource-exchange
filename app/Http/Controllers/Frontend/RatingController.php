<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Rating;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class RatingController extends Controller
{
    /**
     * Display ratings page for a user
     */
    public function index(User $user)
    {
        $user->load(['ratingsReceived.rater:id,name', 'ratingsReceived.transaction:id,type,status']);

        $ratings = $user->ratingsReceived()
            ->with(['rater:id,name,email', 'transaction.item:id,title'])
            ->latest()
            ->paginate(10);

        $avgRating = $user->averageRating();
        $totalRatings = $user->ratingsReceived()->count();

        $ratingDistribution = [
            5 => $user->ratingsReceived()->where('rating', 5)->count(),
            4 => $user->ratingsReceived()->where('rating', 4)->count(),
            3 => $user->ratingsReceived()->where('rating', 3)->count(),
            2 => $user->ratingsReceived()->where('rating', 2)->count(),
            1 => $user->ratingsReceived()->where('rating', 1)->count(),
        ];

        return view('frontend.ratings.user-ratings', compact(
            'user',
            'ratings',
            'avgRating',
            'totalRatings',
            'ratingDistribution'
        ));
    }

    /**
     * Show form to create rating for a transaction
     */
    public function create(Transaction $transaction)
    {
        // Authorize user can rate
        $this->authorizeRating($transaction);

        // Check if transaction can be rated
        if (!Rating::canRateTransaction($transaction)) {
            return back()->with('error', 'Transaction is not eligible for rating.');
        }

        // Check if user already rated
        if (Rating::userHasRatedTransaction(Auth::user(), $transaction)) {
            return back()->with('error', 'You have already rated this transaction.');
        }

        // Determine who is being rated
        $isRatingBorrower = Auth::id() === $transaction->item->user_id;
        $ratedUser = $isRatingBorrower ? $transaction->borrower : $transaction->item->owner;

        $transaction->load(['item:id,title', 'borrower:id,name', 'item.owner:id,name']);

        return view('frontend.ratings.create', compact(
            'transaction',
            'ratedUser',
            'isRatingBorrower'
        ));
    }

    /**
     * Store new rating
     */
    public function store(Request $request, Transaction $transaction)
    {
        // Authorize user can rate
        $this->authorizeRating($transaction);

        // Check if transaction can be rated
        if (!Rating::canRateTransaction($transaction)) {
            return back()->with('error', 'Transaction is not eligible for rating.');
        }

        // Check for duplicate rating
        if (Rating::userHasRatedTransaction(Auth::user(), $transaction)) {
            return back()->with('error', 'You have already rated this transaction.');
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', Rule::in([1, 2, 3, 4, 5])],
            'comment' => 'nullable|string|min:5|max:500',
        ]);

        $validated['transaction_id'] = $transaction->id;
        $validated['rater_id'] = Auth::id();

        Rating::create($validated);

        // Determine what user was rated
        $isRatingBorrower = Auth::id() === $transaction->item->user_id;
        $ratedUserName = $isRatingBorrower ? $transaction->borrower->name : $transaction->item->owner->name;

        return redirect()->route('frontend.transactions.show', $transaction)
            ->with('success', "Rating for {$ratedUserName} submitted successfully!");
    }

    /**
     * Show specific rating
     */
    public function show(Rating $rating)
    {
        // Authorize user can view
        if (Auth::id() !== $rating->rater_id && 
            Auth::id() !== $rating->transaction->borrower_id && 
            Auth::id() !== $rating->transaction->item->user_id) {
            abort(403, 'Unauthorized to view this rating.');
        }

        $rating->load([
            'rater:id,name,email',
            'transaction.item:id,title',
            'transaction.borrower:id,name',
            'transaction.item.owner:id,name'
        ]);

        return view('frontend.ratings.show', compact('rating'));
    }

    /**
     * Show form to edit rating
     */
    public function edit(Rating $rating)
    {
        // Only rater can edit their rating
        if (Auth::id() !== $rating->rater_id) {
            abort(403, 'Unauthorized to edit this rating.');
        }

        // Check transaction is still eligible
        $transaction = $rating->transaction;
        if (!in_array($transaction->status, ['completed', 'late'])) {
            return back()->with('error', 'Cannot edit rating for incomplete transactions.');
        }

        $rating->load(['transaction.item:id,title', 'transaction.borrower:id,name', 'transaction.item.owner:id,name']);

        return view('frontend.ratings.edit', compact('rating'));
    }

    /**
     * Update existing rating
     */
    public function update(Request $request, Rating $rating)
    {
        // Only rater can update
        if (Auth::id() !== $rating->rater_id) {
            abort(403, 'Unauthorized to update this rating.');
        }

        $transaction = $rating->transaction;
        if (!in_array($transaction->status, ['completed', 'late'])) {
            return back()->with('error', 'Cannot update rating for incomplete transactions.');
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', Rule::in([1, 2, 3, 4, 5])],
            'comment' => 'nullable|string|min:5|max:500',
        ]);

        $rating->update($validated);

        return redirect()->route('frontend.transactions.show', $transaction)
            ->with('success', 'Rating updated successfully!');
    }

    /**
     * Delete rating
     */
    public function destroy(Rating $rating)
    {
        // Only rater or admin can delete
        if (Auth::id() !== $rating->rater_id && !Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized to delete this rating.');
        }

        $transaction = $rating->transaction;
        $rating->delete();

        return back()->with('success', 'Rating deleted successfully.');
    }

    /**
     * Get ratings for an item
     */
    public function itemRatings(Request $request)
    {
        $itemId = $request->get('item_id');

        $ratings = Rating::whereHas('transaction', function($q) use ($itemId) {
            $q->where('item_id', $itemId);
        })
            ->with(['rater:id,name', 'transaction:id,type,status'])
            ->latest()
            ->get()
            ->groupBy('rating')
            ->map(fn($group) => $group->count());

        return response()->json([
            'distribution' => $ratings,
            'total' => collect($ratings)->sum(),
            'average' => Rating::whereHas('transaction', function($q) use ($itemId) {
                $q->where('item_id', $itemId);
            })->avg('rating') ?? 0
        ]);
    }

    /**
     * Get user profile rating summary
     */
    public function userProfileRatings(User $user)
    {
        $ratings = $user->ratingsReceived()
            ->with(['rater:id,name', 'transaction.item:id,title'])
            ->latest()
            ->get();

        $avgRating = $user->averageRating();
        $totalRatings = $ratings->count();

        $ratingBreakdown = [
            5 => $ratings->where('rating', 5)->count(),
            4 => $ratings->where('rating', 4)->count(),
            3 => $ratings->where('rating', 3)->count(),
            2 => $ratings->where('rating', 2)->count(),
            1 => $ratings->where('rating', 1)->count(),
        ];

        return response()->json([
            'user_id' => $user->id,
            'name' => $user->name,
            'average_rating' => round($avgRating, 2),
            'total_ratings' => $totalRatings,
            'rating_breakdown' => $ratingBreakdown,
            'recent_ratings' => $ratings->take(5)->map(fn($r) => [
                'id' => $r->id,
                'rating' => $r->rating,
                'comment' => $r->comment,
                'rater_name' => $r->rater->name,
                'date' => $r->created_at->format('M d, Y'),
            ]),
        ]);
    }

    /**
     * Get top rated users
     */
    public function topRatedUsers(Request $request)
    {
        $limit = $request->get('limit', 10);

        $users = User::with('ratingsReceived')
            ->withCount('ratingsReceived as rating_count')
            ->get()
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'average_rating' => round($user->averageRating(), 2),
                    'rating_count' => $user->rating_count,
                ];
            })
            ->filter(fn($u) => $u['rating_count'] > 0)
            ->sortByDesc('average_rating')
            ->take($limit)
            ->values();

        return response()->json($users);
    }

    /**
     * Get lowest rated users (for admin review)
     */
    public function lowestRatedUsers(Request $request)
    {
        $limit = $request->get('limit', 10);
        $minRatings = $request->get('min_ratings', 3);

        $users = User::with('ratingsReceived')
            ->withCount('ratingsReceived as rating_count')
            ->get()
            ->filter(fn($u) => $u->rating_count >= $minRatings)
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'average_rating' => round($user->averageRating(), 2),
                    'rating_count' => $user->rating_count,
                ];
            })
            ->sortBy('average_rating')
            ->take($limit)
            ->values();

        return response()->json($users);
    }

    /**
     * Get rating statistics
     */
    public function statistics()
    {
        $totalRatings = Rating::count();
        $avgRating = Rating::avg('rating');
        $ratingsWithComments = Rating::whereNotNull('comment')
            ->where('comment', '!=', '')
            ->count();

        $ratingDistribution = [
            5 => Rating::where('rating', 5)->count(),
            4 => Rating::where('rating', 4)->count(),
            3 => Rating::where('rating', 3)->count(),
            2 => Rating::where('rating', 2)->count(),
            1 => Rating::where('rating', 1)->count(),
        ];

        $percentages = array_map(function($count) use ($totalRatings) {
            return $totalRatings > 0 ? round(($count / $totalRatings) * 100, 2) : 0;
        }, $ratingDistribution);

        return response()->json([
            'total_ratings' => $totalRatings,
            'average_rating' => round($avgRating, 2),
            'ratings_with_comments' => $ratingsWithComments,
            'distribution' => $ratingDistribution,
            'distribution_percentage' => $percentages,
            'most_common_rating' => array_key_first(array_filter($ratingDistribution, fn($c) => $c === max($ratingDistribution))),
        ]);
    }

    /**
     * Get ratings given by user
     */
    public function userGivenRatings(User $user)
    {
        // Only user or admin can view their given ratings
        if (Auth::id() !== $user->id && !Auth::user()?->isAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $ratings = $user->ratingsGiven()
            ->with(['transaction.item:id,title', 'transaction.borrower:id,name', 'transaction.item.owner:id,name'])
            ->latest()
            ->paginate(15);

        return view('frontend.ratings.given-ratings', compact('user', 'ratings'));
    }

    /**
     * Export user ratings as PDF/CSV
     */
    public function exportRatings(User $user, Request $request)
    {
        // Only user can export their own ratings
        if (Auth::id() !== $user->id) {
            abort(403, 'Unauthorized.');
        }

        $format = $request->get('format', 'csv');
        $ratings = $user->ratingsReceived()
            ->with(['rater:id,name', 'transaction.item:id,title'])
            ->get();

        if ($format === 'csv') {
            return $this->exportAsCSV($ratings, $user);
        } elseif ($format === 'pdf') {
            // TODO: Implement PDF export using Laravel PDF library
            return back()->with('error', 'PDF export coming soon.');
        }

        return back()->with('error', 'Invalid format.');
    }

    // ========================================
    // Helper Methods
    // ========================================

    /**
     * Authorize user can rate transaction
     */
    private function authorizeRating(Transaction $transaction)
    {
        $user = Auth::user();
        $isOwner = $user->id === $transaction->item->user_id;
        $isBorrower = $user->id === $transaction->borrower_id;

        // Must be owner or borrower
        if (!$isOwner && !$isBorrower) {
            abort(403, 'Unauthorized to rate this transaction.');
        }

        // Cannot rate self
        if ($isOwner && $isBorrower) {
            abort(403, 'Cannot rate yourself.');
        }
    }

    /**
     * Export ratings as CSV
     */
    private function exportAsCSV($ratings, User $user)
    {
        $filename = "ratings_{$user->id}_" . now()->format('Y-m-d') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($ratings) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'Rating', 'Rater', 'Comment']);

            foreach ($ratings as $rating) {
                fputcsv($file, [
                    $rating->created_at->format('Y-m-d H:i'),
                    $rating->rating . ' / 5',
                    $rating->rater->name,
                    $rating->comment ?? 'N/A',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

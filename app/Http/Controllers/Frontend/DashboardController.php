<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Penalty;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    /**
     * Main dashboard view with comprehensive user stats.
     *
     * FIX 1: activeTransactions and totalTransactions now use owner_id directly
     *         instead of whereHas('item') — avoids unnecessary joins.
     * FIX 2: activeLending uses owner_id directly.
     * FIX 3: recentLending uses owner_id directly.
     * FIX 4: getUnreadMessageCount() corrected to unreadMessageCount().
     * FIX 5: analyticsDashboard repeated query block extracted to helper.
     */
    public function index(): View
    {
        $user = auth()->user();

        // FIX: use owner_id directly — no whereHas needed
        $activeTransactions = Transaction::where(function ($query) use ($user) {
            $query->where('borrower_id', $user->id)
                  ->whereIn('status', ['pending', 'active']);
        })
        ->orWhere(function ($query) use ($user) {
            $query->where('owner_id', $user->id)
                  ->whereIn('status', ['pending', 'active']);
        })
        ->count();

        $activeBorrowing = Transaction::where('borrower_id', $user->id)
            ->whereIn('status', ['pending', 'active'])
            ->with(['item.owner'])
            ->orderBy('due_date', 'asc')
            ->take(5)
            ->get();

        // FIX: owner_id direct query
        $activeLending = Transaction::where('owner_id', $user->id)
            ->whereIn('status', ['pending', 'active'])
            ->with(['item', 'borrower'])
            ->orderBy('due_date', 'asc')
            ->take(5)
            ->get();

        $userItems = $user->items()
            ->with(['transactions' => function ($q) {
                $q->whereIn('status', ['pending', 'active']);
            }])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $totalItems = $user->items()->count();

        // FIX: owner_id direct query
        $totalTransactions = Transaction::where('borrower_id', $user->id)
            ->orWhere('owner_id', $user->id)
            ->count();

        $averageRating = $user->averageRating();

        $unpaidPenalties = Penalty::whereHas('transaction', function ($q) use ($user) {
            $q->where('borrower_id', $user->id);
        })
        ->where('status', 'pending')
        ->sum('amount');

        $hasOverdueItems = $user->transactionsAsBorrower()
            ->where('status', 'active')
            ->where('due_date', '<', Carbon::today())
            ->exists();

        // Recent borrowing activity
        $recentBorrowing = Transaction::where('borrower_id', $user->id)
            ->with(['item'])
            ->latest()
            ->take(3)
            ->get()
            ->map(function ($transaction) {
                return [
                    'title'       => 'Borrowed: ' . $transaction->item->title,
                    'description' => 'Due on ' . $transaction->due_date?->format('M d, Y'),
                    'timestamp'   => $transaction->created_at,
                ];
            });

        // FIX: owner_id direct query
        $recentLending = Transaction::where('owner_id', $user->id)
            ->with(['item', 'borrower'])
            ->latest()
            ->take(3)
            ->get()
            ->map(function ($transaction) {
                return [
                    'title'       => 'Lent: ' . $transaction->item->title,
                    'description' => 'Borrowed by ' . $transaction->borrower->name,
                    'timestamp'   => $transaction->created_at,
                ];
            });

        $recentActivity = $recentBorrowing->concat($recentLending)
            ->sortByDesc('timestamp')
            ->take(5);

        return view('frontend.dashboard.index', [
            'activeTransactions' => $activeTransactions,
            'activeBorrowing'    => $activeBorrowing,
            'activeLending'      => $activeLending,
            'userItems'          => $userItems,
            'totalItems'         => $totalItems,
            'totalTransactions'  => $totalTransactions,
            'averageRating'      => $averageRating,
            'pendingPenalties'   => $unpaidPenalties,
            'hasOverdueItems'    => $hasOverdueItems,
            'recentActivity'     => $recentActivity,
        ]);
    }

    /**
     * Borrower-specific dashboard
     */
    public function borrowerDashboard(): View
    {
        $user = auth()->user();

        $activeBorrowing = Transaction::where('borrower_id', $user->id)
            ->whereIn('status', ['pending', 'active'])
            ->with(['item.owner'])
            ->orderBy('due_date', 'asc')
            ->get();

        $completedBorrowing = Transaction::where('borrower_id', $user->id)
            ->where('status', 'completed')
            ->with(['item.owner', 'ratings.rater'])
            ->latest()
            ->take(10)
            ->get();

        return view('frontend.dashboard.borrower', [
            'activeBorrowing'    => $activeBorrowing,
            'completedBorrowing' => $completedBorrowing,
        ]);
    }

    /**
     * Lender-specific dashboard — FIX: owner_id direct query
     */
    public function lenderDashboard(): View
    {
        $user = auth()->user();

        $activeLending = Transaction::where('owner_id', $user->id)
            ->whereIn('status', ['pending', 'active'])
            ->with(['item', 'borrower'])
            ->orderBy('due_date', 'asc')
            ->get();

        $completedLending = Transaction::where('owner_id', $user->id)
            ->where('status', 'completed')
            ->with(['item', 'borrower', 'ratings.rater'])
            ->latest()
            ->take(10)
            ->get();

        return view('frontend.dashboard.lender', [
            'activeLending'    => $activeLending,
            'completedLending' => $completedLending,
        ]);
    }

    /**
     * Profile dashboard — FIX: corrected unreadMessageCount() method name
     */
    public function profileDashboard(): View
    {
        $user = auth()->user();

        return view('frontend.dashboard.profile', [
            'user'           => $user,
            'averageRating'  => $user->averageRating(),
            'totalItems'     => $user->items()->count(),
            // FIX: was getUnreadMessageCount() which doesn't exist
            'unreadMessages' => $user->unreadMessageCount(),
        ]);
    }

    /**
     * Analytics dashboard.
     *
     * FIX: repeated owner/borrower query block extracted to
     *      userTransactionQuery() helper — no more duplication.
     */
    public function analyticsDashboard(): View
    {
        $user = auth()->user();

        $completedTransactions = $this->userTransactionQuery($user)
            ->where('status', 'completed')
            ->count();

        $lateTransactions = $this->userTransactionQuery($user)
            ->where('status', 'late')
            ->count();

        return view('frontend.dashboard.analytics', [
            'completedTransactions' => $completedTransactions,
            'lateTransactions'      => $lateTransactions,
            'averageRating'         => $user->averageRating(),
            'totalItems'            => $user->items()->count(),
        ]);
    }

    /**
     * Notifications dashboard
     */
    public function notifications(): View
    {
        $user = auth()->user();

        // scopeUnread() exists on Message model
        $unreadMessages = $user->messagesReceived()
            ->unread()
            ->with(['sender', 'conversation'])
            ->latest()
            ->take(20)
            ->get();

        $overdueItems = $user->transactionsAsBorrower()
            ->where('status', 'active')
            ->where('due_date', '<', Carbon::today())
            ->with(['item', 'item.owner'])
            ->get();

        return view('frontend.dashboard.notifications', [
            'unreadMessages' => $unreadMessages,
            'overdueItems'   => $overdueItems,
        ]);
    }

    // ========================================
    // Helper Methods
    // ========================================

    /**
     * Base query for transactions involving the user as owner OR borrower.
     * Extracted to avoid repeating the same orWhere block across methods.
     */
    private function userTransactionQuery($user)
    {
        return Transaction::where('borrower_id', $user->id)
            ->orWhere('owner_id', $user->id);
    }
}
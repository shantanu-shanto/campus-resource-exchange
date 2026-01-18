<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    /**
     * Main dashboard view with comprehensive user stats
     */
    public function index(): View
    {
        $user = auth()->user();

        // Count active transactions (both as borrower and owner)
        $activeTransactions = Transaction::where(function ($query) use ($user) {
            // As borrower
            $query->where('borrower_id', $user->id)
                ->whereIn('status', ['pending', 'active']);
        })
        ->orWhere(function ($query) use ($user) {
            // As owner (through item)
            $query->whereHas('item', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->whereIn('status', ['pending', 'active']);
        })
        ->count();

        // Get active borrowing transactions (user is borrowing)
        $activeBorrowing = Transaction::where('borrower_id', $user->id)
            ->whereIn('status', ['pending', 'active'])
            ->with(['item.owner'])  // Fix: Changed from 'item', 'item.owner'
            ->orderBy('due_date', 'asc')
            ->take(5)
            ->get();

        // Get active lending transactions (user is lending - owner of item)
        $activeLending = Transaction::whereHas('item', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->whereIn('status', ['pending', 'active'])
        ->with(['item', 'borrower'])
        ->orderBy('due_date', 'asc')
        ->take(5)
        ->get();

        // Get user's items with transaction counts
        $userItems = $user->items()
            ->with(['transactions' => function ($q) {
                $q->whereIn('status', ['pending', 'active']);
            }])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Get user stats
        $totalItems = $user->items()->count();
        $totalTransactions = Transaction::where(function ($query) use ($user) {
            $query->where('borrower_id', $user->id)->orWhereHas('item', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        })->count();

        // Get user's average rating
        $averageRating = $user->averageRating();

        // Get user's unpaid penalties (Fixed: Use correct relationship)
        $unpaidPenalties = \App\Models\Penalty::whereHas('transaction', function ($q) use ($user) {
            $q->where('borrower_id', $user->id);
        })
        ->where('status', 'pending')
        ->sum('amount');

        // Check for overdue items
        $hasOverdueItems = $user->transactionsAsBorrower()
            ->where('status', 'active')
            ->where('due_date', '<', Carbon::today())
            ->exists();

        // Get recent activity (transactions created by user or involving their items)
        $recentActivity = collect();

        // Recent borrowing activity
        $recentBorrowing = Transaction::where('borrower_id', $user->id)
            ->with(['item'])
            ->latest()
            ->take(3)
            ->get()
            ->map(function ($transaction) {
                return [
                    'title' => 'Borrowed: ' . $transaction->item->title,
                    'description' => 'Due on ' . $transaction->due_date->format('M d, Y'),
                    'timestamp' => $transaction->created_at,
                ];
            });

        // Recent lending activity
        $recentLending = Transaction::whereHas('item', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->with(['item', 'borrower'])
        ->latest()
        ->take(3)
        ->get()
        ->map(function ($transaction) {
            return [
                'title' => 'Lent: ' . $transaction->item->title,
                'description' => 'Borrowed by ' . $transaction->borrower->name,
                'timestamp' => $transaction->created_at,
            ];
        });

        // Combine and sort by timestamp
        $recentActivity = $recentBorrowing->concat($recentLending)
            ->sortByDesc('timestamp')
            ->take(5);


        return view('frontend.dashboard.index', [
            'activeTransactions' => $activeTransactions,
            'activeBorrowing' => $activeBorrowing,
            'activeLending' => $activeLending,
            'userItems' => $userItems,
            'totalItems' => $totalItems,
            'totalTransactions' => $totalTransactions,
            'averageRating' => $averageRating,
            'pendingPenalties' => $unpaidPenalties,
            'hasOverdueItems' => $hasOverdueItems,
            'recentActivity' => $recentActivity,
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
            'activeBorrowing' => $activeBorrowing,
            'completedBorrowing' => $completedBorrowing,
        ]);
    }

    /**
     * Lender-specific dashboard
     */
    public function lenderDashboard(): View
    {
        $user = auth()->user();

        $activeLending = Transaction::whereHas('item', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->whereIn('status', ['pending', 'active'])
        ->with(['item', 'borrower'])
        ->orderBy('due_date', 'asc')
        ->get();

        $completedLending = Transaction::whereHas('item', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->where('status', 'completed')
        ->with(['item', 'borrower', 'ratings.rater'])
        ->latest()
        ->take(10)
        ->get();

        return view('frontend.dashboard.lender', [
            'activeLending' => $activeLending,
            'completedLending' => $completedLending,
        ]);
    }

    /**
     * Profile dashboard
     */
    public function profileDashboard(): View
    {
        $user = auth()->user();

        return view('frontend.dashboard.profile', [
            'user' => $user,
            'averageRating' => $user->averageRating(),
            'totalItems' => $user->items()->count(),
            'unreadMessages' => $user->getUnreadMessageCount(),
        ]);
    }

    /**
     * Analytics dashboard
     */
    public function analyticsDashboard(): View
    {
        $user = auth()->user();

        $completedTransactions = Transaction::where(function ($query) use ($user) {
            $query->where('borrower_id', $user->id)->orWhereHas('item', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        })
        ->where('status', 'completed')
        ->count();

        $lateTransactions = Transaction::where(function ($query) use ($user) {
            $query->where('borrower_id', $user->id)->orWhereHas('item', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        })
        ->where('status', 'late')
        ->count();

        return view('frontend.dashboard.analytics', [
            'completedTransactions' => $completedTransactions,
            'lateTransactions' => $lateTransactions,
            'averageRating' => $user->averageRating(),
            'totalItems' => $user->items()->count(),
        ]);
    }

    /**
     * Notifications dashboard
     */
    public function notifications(): View
    {
        $user = auth()->user();

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
            'overdueItems' => $overdueItems,
        ]);
    }
}

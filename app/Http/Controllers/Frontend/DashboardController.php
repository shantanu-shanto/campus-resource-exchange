<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Transaction;
use App\Models\Rating;
use App\Models\Penalty;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display user's main dashboard
     */
    public function index()
    {
        $user = Auth::user();

        // Load user relationships
        $user->load([
            'items' => function($q) { $q->latest()->limit(5); },
            'transactionsAsBorrower' => function($q) { $q->where('status', 'active')->with('item:id,title'); },
        ]);

        // Get dashboard data
        $stats = $this->getStats($user);
        $activities = $this->getRecentActivities($user);
        $alerts = $this->getAlerts($user);
        $upcomingDueDates = $this->getUpcomingDueDates($user);

        return view('frontend.dashboard.index', compact(
            'stats',
            'activities',
            'alerts',
            'upcomingDueDates',
            'user'
        ));
    }

    /**
     * Get borrower dashboard
     */
    public function borrowerDashboard()
    {
        $user = Auth::user();

        $stats = [
            'active_borrows' => $user->transactionsAsBorrower()
                ->where('status', 'active')
                ->count(),
            'completed_borrows' => $user->transactionsAsBorrower()
                ->where('status', 'completed')
                ->count(),
            'pending_requests' => $user->transactionsAsBorrower()
                ->where('status', 'pending')
                ->count(),
            'overdue_items' => $user->transactionsAsBorrower()
                ->where('status', 'late')
                ->count(),
            'total_penalties' => Penalty::borrowerTotalPending($user),
            'average_rating' => round($user->averageRating(), 2),
        ];

        // Active borrowings
        $activeBorrows = $user->transactionsAsBorrower()
            ->where('status', 'active')
            ->with(['item:id,title,user_id', 'item.owner:id,name'])
            ->latest()
            ->get();

        // Pending requests
        $pendingRequests = $user->transactionsAsBorrower()
            ->where('status', 'pending')
            ->with(['item:id,title,user_id', 'item.owner:id,name'])
            ->latest()
            ->get();

        // Overdue items
        $overdueItems = $user->transactionsAsBorrower()
            ->where('status', 'late')
            ->orWhere(function($q) {
                $q->where('status', 'active')
                  ->where('due_date', '<', now()->toDateString());
            })
            ->with(['item:id,title', 'item.owner:id,name'])
            ->get();

        // Unpaid penalties
        $unpaidPenalties = Penalty::forBorrower($user)
            ->pending()
            ->with('transaction.item:id,title')
            ->latest()
            ->get();

        return view('frontend.dashboard.borrower', compact(
            'stats',
            'activeBorrows',
            'pendingRequests',
            'overdueItems',
            'unpaidPenalties'
        ));
    }

    /**
     * Get lender dashboard
     */
    public function lenderDashboard()
    {
        $user = Auth::user();

        $stats = [
            'total_items' => $user->items()->count(),
            'available_items' => $user->items()->where('status', 'available')->count(),
            'borrowed_items' => $user->items()->where('status', 'borrowed')->count(),
            'sold_items' => $user->items()->where('status', 'sold')->count(),
            'active_loans' => Transaction::whereHas('item', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->where('status', 'active')->count(),
            'completed_transactions' => Transaction::whereHas('item', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->where('status', 'completed')->count(),
            'average_rating' => round($user->averageRating(), 2),
        ];

        // Items listed
        $items = $user->items()
            ->with(['activeTransaction.borrower:id,name', 'ratings'])
            ->withCount('transactions')
            ->latest()
            ->paginate(10);

        // Pending requests
        $pendingRequests = Transaction::whereHas('item', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })
            ->where('status', 'pending')
            ->with(['item:id,title', 'borrower:id,name,email'])
            ->latest()
            ->get();

        // Active loans
        $activeLoans = Transaction::whereHas('item', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })
            ->where('status', 'active')
            ->with(['item:id,title', 'borrower:id,name,email'])
            ->get();

        // Due soon items
        $dueItems = Transaction::whereHas('item', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })
            ->where('status', 'active')
            ->whereDate('due_date', '<=', now()->addDays(3))
            ->with(['item:id,title', 'borrower:id,name'])
            ->get();

        return view('frontend.dashboard.lender', compact(
            'stats',
            'items',
            'pendingRequests',
            'activeLoans',
            'dueItems'
        ));
    }

    /**
     * Get user profile/settings dashboard
     */
    public function profileDashboard()
    {
        $user = Auth::user();

        $user->load(['items', 'ratingsReceived', 'ratingsGiven']);

        $profileStats = [
            'total_items_listed' => $user->items()->count(),
            'total_transactions' => Transaction::where('borrower_id', $user->id)
                ->orWhereHas('item', fn($q) => $q->where('user_id', $user->id))
                ->count(),
            'average_rating' => round($user->averageRating(), 2),
            'total_ratings_received' => $user->ratingsReceived()->count(),
            'member_since' => $user->created_at->format('F Y'),
            'total_penalties' => Penalty::borrowerTotalPending($user),
        ];

        $recentActivity = $this->getRecentActivities($user, 10);

        return view('frontend.dashboard.profile', compact('user', 'profileStats', 'recentActivity'));
    }

    /**
     * Get analytics dashboard
     */
    public function analyticsDashboard()
    {
        $user = Auth::user();

        // Transaction analytics
        $transactionStats = [
            'total_borrowed' => $user->transactionsAsBorrower()->count(),
            'total_lent' => Transaction::whereHas('item', fn($q) => $q->where('user_id', $user->id))->count(),
            'success_rate' => $this->calculateSuccessRate($user),
            'total_value_borrowed' => $user->transactionsAsBorrower()
                ->where('type', 'sell')
                ->sum('final_price'),
            'total_value_lent' => Transaction::whereHas('item', fn($q) => $q->where('user_id', $user->id))
                ->where('type', 'sell')
                ->sum('final_price'),
        ];

        // Rating trends (last 30 days)
        $ratingTrends = $user->ratingsReceived()
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->selectRaw('DATE(created_at) as date, AVG(rating) as avg_rating, COUNT(*) as count')
            ->get();

        // Monthly activity
        $monthlyActivity = $this->getMonthlyActivity($user);

        // Top rated items
        $topRatedItems = $user->items()
            ->with('ratings')
            ->get()
            ->sortByDesc(fn($item) => $item->averageRating())
            ->take(5);

        // Borrowing patterns
        $borrowingPatterns = [
            'most_borrowed_category' => $this->getMostBorrowedCategory($user),
            'avg_lending_duration' => $user->items()->avg('lending_duration_days'),
            'return_rate_ontime' => $this->calculateReturnRateOnTime($user),
        ];

        return view('frontend.dashboard.analytics', compact(
            'transactionStats',
            'ratingTrends',
            'monthlyActivity',
            'topRatedItems',
            'borrowingPatterns'
        ));
    }

    /**
     * Get notifications/alerts
     */
    public function notifications()
    {
        $user = Auth::user();

        // Overdue items
        $overdueItems = $user->transactionsAsBorrower()
            ->where('status', 'active')
            ->where('due_date', '<', now()->toDateString())
            ->with(['item:id,title', 'item.owner:id,name'])
            ->get();

        // Pending requests (for lender)
        $pendingRequests = Transaction::whereHas('item', fn($q) => $q->where('user_id', $user->id))
            ->where('status', 'pending')
            ->with(['item:id,title', 'borrower:id,name,email'])
            ->get();

        // Unpaid penalties
        $unpaidPenalties = Penalty::forBorrower($user)
            ->pending()
            ->with('transaction.item:id,title')
            ->get();

        // Items due soon (for lender)
        $dueItems = Transaction::whereHas('item', fn($q) => $q->where('user_id', $user->id))
            ->where('status', 'active')
            ->whereDate('due_date', '<=', now()->addDays(3))
            ->with(['item:id,title', 'borrower:id,name'])
            ->get();

        // Notifications
        $notifications = [];

        foreach ($overdueItems as $transaction) {
            $notifications[] = [
                'type' => 'overdue',
                'level' => 'danger',
                'title' => 'Item Overdue',
                'message' => "Return '{$transaction->item->title}' to {$transaction->item->owner->name}",
                'date' => $transaction->due_date,
                'action' => route('frontend.transactions.show', $transaction),
            ];
        }

        foreach ($pendingRequests as $transaction) {
            $notifications[] = [
                'type' => 'pending_request',
                'level' => 'info',
                'title' => 'New Request',
                'message' => "{$transaction->borrower->name} requested '{$transaction->item->title}'",
                'date' => $transaction->created_at,
                'action' => route('frontend.transactions.show', $transaction),
            ];
        }

        foreach ($unpaidPenalties as $penalty) {
            $notifications[] = [
                'type' => 'penalty',
                'level' => 'warning',
                'title' => 'Unpaid Penalty',
                'message' => "You have an unpaid penalty of ৳{$penalty->amount}",
                'date' => $penalty->created_at,
                'action' => route('frontend.transactions.penalties', $penalty->transaction),
            ];
        }

        foreach ($dueItems as $transaction) {
            $notifications[] = [
                'type' => 'due_soon',
                'level' => 'warning',
                'title' => 'Item Due Soon',
                'message' => "'{$transaction->item->title}' from {$transaction->borrower->name} due on {$transaction->due_date->format('M d')}",
                'date' => $transaction->due_date,
                'action' => route('frontend.transactions.show', $transaction),
            ];
        }

        // Sort by date (newest first)
        usort($notifications, fn($a, $b) => $b['date'] <=> $a['date']);

        return view('frontend.dashboard.notifications', compact('notifications'));
    }

    /**
     * Get quick stats (JSON for AJAX)
     */
    public function quickStats()
    {
        $user = Auth::user();

        return response()->json([
            'active_borrows' => $user->transactionsAsBorrower()->where('status', 'active')->count(),
            'active_loans' => Transaction::whereHas('item', fn($q) => $q->where('user_id', $user->id))
                ->where('status', 'active')->count(),
            'pending_requests' => Transaction::whereHas('item', fn($q) => $q->where('user_id', $user->id))
                ->where('status', 'pending')->count(),
            'unpaid_penalties' => Penalty::borrowerTotalPending($user),
            'average_rating' => round($user->averageRating(), 2),
            'overdue_items' => $user->hasOverdueItems() ? 1 : 0,
        ]);
    }

    // ========================================
    // Helper Methods
    // ========================================

    /**
     * Get dashboard statistics
     */
    private function getStats($user): array
    {
        return [
            'active_borrows' => $user->transactionsAsBorrower()
                ->where('status', 'active')->count(),
            'active_loans' => Transaction::whereHas('item', fn($q) => $q->where('user_id', $user->id))
                ->where('status', 'active')->count(),
            'items_listed' => $user->items()->count(),
            'pending_requests' => Transaction::whereHas('item', fn($q) => $q->where('user_id', $user->id))
                ->where('status', 'pending')->count(),
            'unpaid_penalties' => Penalty::borrowerTotalPending($user),
            'average_rating' => round($user->averageRating(), 2),
        ];
    }

    /**
     * Get recent activities
     */
    private function getRecentActivities($user, $limit = 5): array
    {
        $activities = [];

        // Recent borrows
        $borrows = $user->transactionsAsBorrower()
            ->latest()
            ->take($limit)
            ->get();

        foreach ($borrows as $transaction) {
            $activities[] = [
                'type' => 'borrow',
                'title' => "Requested '{$transaction->item->title}'",
                'date' => $transaction->created_at,
                'status' => $transaction->status,
            ];
        }

        // Recent ratings given
        $ratings = $user->ratingsGiven()
            ->latest()
            ->take(3)
            ->get();

        foreach ($ratings as $rating) {
            $activities[] = [
                'type' => 'rating',
                'title' => "Rated: {$rating->getRatingLabel()}",
                'date' => $rating->created_at,
                'status' => 'completed',
            ];
        }

        // Recent items listed
        $items = $user->items()
            ->latest()
            ->take(3)
            ->get();

        foreach ($items as $item) {
            $activities[] = [
                'type' => 'item_listed',
                'title' => "Listed '{$item->title}'",
                'date' => $item->created_at,
                'status' => $item->status,
            ];
        }

        // Sort by date
        usort($activities, fn($a, $b) => $b['date'] <=> $a['date']);

        return array_slice($activities, 0, $limit);
    }

    /**
     * Get alerts for user
     */
    private function getAlerts($user): array
    {
        $alerts = [];

        // Overdue items alert
        if ($user->hasOverdueItems()) {
            $alerts[] = [
                'type' => 'danger',
                'message' => 'You have overdue items!',
                'icon' => 'exclamation-circle',
                'action' => route('frontend.dashboard.borrower'),
            ];
        }

        // Unpaid penalties alert
        $unpaidPenalties = Penalty::borrowerTotalPending($user);
        if ($unpaidPenalties > 0) {
            $alerts[] = [
                'type' => 'warning',
                'message' => "Unpaid penalties: ৳{$unpaidPenalties}",
                'icon' => 'alert-triangle',
                'action' => route('frontend.dashboard.borrower'),
            ];
        }

        // Pending requests alert (for lenders)
        $pendingCount = Transaction::whereHas('item', fn($q) => $q->where('user_id', $user->id))
            ->where('status', 'pending')->count();
        
        if ($pendingCount > 0) {
            $alerts[] = [
                'type' => 'info',
                'message' => "{$pendingCount} pending request(s)",
                'icon' => 'info-circle',
                'action' => route('frontend.dashboard.lender'),
            ];
        }

        return $alerts;
    }

    /**
     * Get upcoming due dates
     */
    private function getUpcomingDueDates($user): array
    {
        return $user->transactionsAsBorrower()
            ->where('status', 'active')
            ->where('due_date', '!=', null)
            ->with(['item:id,title', 'item.owner:id,name'])
            ->orderBy('due_date')
            ->take(5)
            ->get()
            ->map(fn($t) => [
                'item' => $t->item->title,
                'owner' => $t->item->owner->name,
                'due_date' => $t->due_date->format('M d, Y'),
                'days_left' => $t->due_date->diffInDays(now()),
                'is_overdue' => $t->due_date->lt(now()),
            ])
            ->toArray();
    }

    /**
     * Get monthly activity data
     */
    private function getMonthlyActivity($user): array
    {
        return Transaction::where('borrower_id', $user->id)
            ->orWhereHas('item', fn($q) => $q->where('user_id', $user->id))
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->groupBy('month')
            ->get()
            ->pluck('count', 'month')
            ->toArray();
    }

    /**
     * Calculate transaction success rate
     */
    private function calculateSuccessRate($user): float
    {
        $total = $user->transactionsAsBorrower()
            ->whereIn('status', ['completed', 'late', 'cancelled'])
            ->count();

        if ($total === 0) return 0;

        $completed = $user->transactionsAsBorrower()
            ->where('status', 'completed')
            ->count();

        return round(($completed / $total) * 100, 2);
    }

    /**
     * Calculate on-time return rate
     */
    private function calculateReturnRateOnTime($user): float
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
     * Get most borrowed category/type
     */
    private function getMostBorrowedCategory($user): ?string
    {
        // This is a placeholder - extend Item model with category field
        return 'Books'; // Or implement actual categorization
    }
}

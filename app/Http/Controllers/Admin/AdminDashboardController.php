<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Item;
use App\Models\Transaction;
use App\Models\Rating;
use App\Models\Penalty;
use App\Models\Message;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    /**
     * Display admin dashboard overview
     */
    public function index()
    {
        $stats = $this->getOverallStats();
        $activities = $this->getRecentActivities();
        $alerts = $this->getAdminAlerts();
        $charts = $this->getChartData();

        return view('admin.dashboard', compact(
            'stats',
            'activities',
            'alerts',
            'charts'
        ));
    }

    /**
     * Get overall platform statistics
     */
    private function getOverallStats(): array
    {
        return [
            // User Stats
            'total_users' => User::count(),
            'new_users_this_month' => User::where('created_at', '>=', now()->startOfMonth())->count(),
            'new_users_this_week' => User::where('created_at', '>=', now()->startOfWeek())->count(),
            'active_users' => User::whereHas('transactionsAsBorrower', function($q) {
                $q->where('created_at', '>=', now()->subDays(30));
            })->orWhereHas('items', function($q) {
                $q->where('created_at', '>=', now()->subDays(30));
            })->count(),
            'admin_count' => User::where('is_admin', true)->count(),

            // Item Stats
            'total_items' => Item::count(),
            'available_items' => Item::where('status', 'available')->count(),
            'borrowed_items' => Item::where('status', 'borrowed')->count(),
            'sold_items' => Item::where('status', 'sold')->count(),
            'reserved_items' => Item::where('status', 'reserved')->count(),
            'new_items_this_month' => Item::where('created_at', '>=', now()->startOfMonth())->count(),

            // Transaction Stats
            'total_transactions' => Transaction::count(),
            'active_transactions' => Transaction::where('status', 'active')->count(),
            'completed_transactions' => Transaction::where('status', 'completed')->count(),
            'late_transactions' => Transaction::where('status', 'late')->count(),
            'pending_transactions' => Transaction::where('status', 'pending')->count(),
            'cancelled_transactions' => Transaction::where('status', 'cancelled')->count(),

            // Rating Stats
            'total_ratings' => Rating::count(),
            'avg_rating' => round(Rating::avg('rating'), 2),
            'ratings_with_comments' => Rating::whereNotNull('comment')->where('comment', '!=', '')->count(),

            // Penalty Stats
            'total_penalties' => Penalty::count(),
            'pending_penalties' => Penalty::where('status', 'pending')->count(),
            'paid_penalties' => Penalty::where('status', 'paid')->count(),
            'waived_penalties' => Penalty::where('status', 'waived')->count(),
            'total_pending_amount' => Penalty::where('status', 'pending')->sum('amount'),

            // Message Stats
            'total_conversations' => DB::table('conversations')->count(),
            'total_messages' => Message::count(),
            'unread_messages' => Message::whereNull('read_at')->count(),

            // Platform Health
            'platform_rating' => round(Rating::avg('rating'), 2),
            'user_satisfaction' => $this->calculateUserSatisfaction(),
            'platform_health_score' => $this->calculatePlatformHealthScore(),
        ];
    }

    /**
     * Get recent platform activities
     */
    private function getRecentActivities(): array
    {
        $activities = [];

        // Recent new users
        $newUsers = User::latest()->take(5)->get();
        foreach ($newUsers as $user) {
            $activities[] = [
                'type' => 'user_registered',
                'title' => 'New User Registration',
                'description' => "{$user->name} joined the platform",
                'user' => $user,
                'timestamp' => $user->created_at,
                'icon' => 'user-plus',
                'level' => 'info',
            ];
        }

        // Recent items listed
        $newItems = Item::with('owner:id,name')->latest()->take(5)->get();
        foreach ($newItems as $item) {
            $activities[] = [
                'type' => 'item_listed',
                'title' => 'New Item Listed',
                'description' => "{$item->owner->name} listed '{$item->title}'",
                'item' => $item,
                'timestamp' => $item->created_at,
                'icon' => 'package',
                'level' => 'info',
            ];
        }

        // Recent late transactions
        $lateTransactions = Transaction::where('status', 'late')
            ->with(['item:id,title', 'borrower:id,name'])
            ->latest()
            ->take(3)
            ->get();
        foreach ($lateTransactions as $transaction) {
            $activities[] = [
                'type' => 'transaction_late',
                'title' => 'Late Return',
                'description' => "{$transaction->borrower->name} returned '{$transaction->item->title}' late",
                'transaction' => $transaction,
                'timestamp' => $transaction->updated_at,
                'icon' => 'alert-triangle',
                'level' => 'warning',
            ];
        }

        // Recent disputes/penalties
        $recentPenalties = Penalty::with('transaction.item:id,title', 'transaction.borrower:id,name')
            ->latest()
            ->take(3)
            ->get();
        foreach ($recentPenalties as $penalty) {
            $activities[] = [
                'type' => 'penalty_issued',
                'title' => 'Penalty Issued',
                'description' => "{$penalty->transaction->borrower->name} incurred ৳{$penalty->amount} penalty",
                'penalty' => $penalty,
                'timestamp' => $penalty->created_at,
                'icon' => 'x-circle',
                'level' => 'danger',
            ];
        }

        // Recent low-rated users
        $lowRatedUsers = User::with('ratingsReceived')
            ->withCount('ratingsReceived as rating_count')
            ->get()
            ->filter(fn($u) => $u->rating_count >= 3 && $u->averageRating() < 3)
            ->take(3);

        foreach ($lowRatedUsers as $user) {
            $activities[] = [
                'type' => 'low_rating',
                'title' => 'Low User Rating',
                'description' => "{$user->name} has low rating: " . round($user->averageRating(), 2),
                'user' => $user,
                'timestamp' => now(),
                'icon' => 'star',
                'level' => 'warning',
            ];
        }

        // Sort by timestamp (newest first)
        usort($activities, fn($a, $b) => $b['timestamp'] <=> $a['timestamp']);

        return array_slice($activities, 0, 10);
    }

    /**
     * Get alerts for admin attention
     */
    private function getAdminAlerts(): array
    {
        $alerts = [];

        // High number of overdue items
        $overdueCount = Transaction::where('status', 'active')
            ->where('due_date', '<', now()->toDateString())
            ->count();

        if ($overdueCount > 0) {
            $alerts[] = [
                'type' => 'overdue_items',
                'level' => 'danger',
                'title' => 'Overdue Items Alert',
                'message' => "{$overdueCount} items are overdue",
                'icon' => 'alert-circle',
                'action' => 'Review overdue transactions',
                'url' => route('admin.transactions.index', ['status' => 'late']),
            ];
        }

        // High number of pending penalties
        $unpaidPenalties = Penalty::where('status', 'pending')->count();
        $totalUnpaidAmount = Penalty::where('status', 'pending')->sum('amount');

        if ($unpaidPenalties > 5) {
            $alerts[] = [
                'type' => 'unpaid_penalties',
                'level' => 'warning',
                'title' => 'Unpaid Penalties',
                'message' => "{$unpaidPenalties} penalties awaiting payment (৳{$totalUnpaidAmount})",
                'icon' => 'credit-card',
                'action' => 'Manage penalties',
                'url' => route('admin.penalties.index', ['status' => 'pending']),
            ];
        }

        // Pending transaction requests
        $pendingRequests = Transaction::where('status', 'pending')->count();

        if ($pendingRequests > 0) {
            $alerts[] = [
                'type' => 'pending_requests',
                'level' => 'info',
                'title' => 'Pending Requests',
                'message' => "{$pendingRequests} transaction requests awaiting approval",
                'icon' => 'clock',
                'action' => 'Review requests',
                'url' => route('admin.transactions.index', ['status' => 'pending']),
            ];
        }

        // Users with multiple penalties
        $problematicUsers = User::whereHas('penalties', function($q) {
            $q->where('status', 'pending');
        })
            ->withCount('penalties')
            ->having('penalties_count', '>=', 3)
            ->get();

        if ($problematicUsers->count() > 0) {
            $alerts[] = [
                'type' => 'problematic_users',
                'level' => 'warning',
                'title' => 'Users with Multiple Penalties',
                'message' => "{$problematicUsers->count()} users have 3+ pending penalties",
                'icon' => 'alert-triangle',
                'action' => 'Review users',
                'url' => route('admin.users.index', ['status' => 'flagged']),
            ];
        }

        // Low platform rating trend
        $avgRating = Rating::avg('rating');
        if ($avgRating < 3.5) {
            $alerts[] = [
                'type' => 'low_platform_rating',
                'level' => 'warning',
                'title' => 'Low Platform Rating',
                'message' => "Average platform rating is {$avgRating} stars",
                'icon' => 'trending-down',
                'action' => 'View analytics',
                'url' => route('admin.reports.index'),
            ];
        }

        // High number of new items (potential spam check)
        $newItemsToday = Item::where('created_at', '>=', now()->startOfDay())->count();
        if ($newItemsToday > 20) {
            $alerts[] = [
                'type' => 'high_item_volume',
                'level' => 'info',
                'title' => 'High Item Listing Volume',
                'message' => "{$newItemsToday} items listed today",
                'icon' => 'activity',
                'action' => 'Review items',
                'url' => route('admin.items.index'),
            ];
        }

        return $alerts;
    }

    /**
     * Get chart data for dashboard
     */
    private function getChartData(): array
    {
        return [
            // Daily transaction volume (last 30 days)
            'daily_transactions' => $this->getDailyTransactionData(),
            
            // User growth (last 30 days)
            'user_growth' => $this->getUserGrowthData(),
            
            // Transaction status breakdown
            'transaction_status' => $this->getTransactionStatusData(),
            
            // Item status breakdown
            'item_status' => $this->getItemStatusData(),
            
            // Average rating trend (last 30 days)
            'rating_trend' => $this->getRatingTrendData(),
            
            // Penalty distribution
            'penalty_distribution' => $this->getPenaltyDistributionData(),
        ];
    }

    /**
     * Get daily transaction data (last 30 days)
     */
    private function getDailyTransactionData(): array
    {
        $data = [];
        
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->startOfDay();
            $count = Transaction::whereDate('created_at', $date)->count();
            
            $data[] = [
                'date' => $date->format('M d'),
                'count' => $count,
            ];
        }
        
        return $data;
    }

    /**
     * Get user growth data (last 30 days)
     */
    private function getUserGrowthData(): array
    {
        $data = [];
        
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->startOfDay();
            $count = User::where('created_at', '<=', $date)->count();
            
            $data[] = [
                'date' => $date->format('M d'),
                'count' => $count,
            ];
        }
        
        return $data;
    }

    /**
     * Get transaction status breakdown
     */
    private function getTransactionStatusData(): array
    {
        return [
            'pending' => Transaction::where('status', 'pending')->count(),
            'active' => Transaction::where('status', 'active')->count(),
            'completed' => Transaction::where('status', 'completed')->count(),
            'late' => Transaction::where('status', 'late')->count(),
            'cancelled' => Transaction::where('status', 'cancelled')->count(),
        ];
    }

    /**
     * Get item status breakdown
     */
    private function getItemStatusData(): array
    {
        return [
            'available' => Item::where('status', 'available')->count(),
            'borrowed' => Item::where('status', 'borrowed')->count(),
            'sold' => Item::where('status', 'sold')->count(),
            'reserved' => Item::where('status', 'reserved')->count(),
        ];
    }

    /**
     * Get rating trend (last 30 days)
     */
    private function getRatingTrendData(): array
    {
        $data = [];
        
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->startOfDay();
            $avgRating = Rating::whereDate('created_at', $date)->avg('rating');
            
            $data[] = [
                'date' => $date->format('M d'),
                'avg_rating' => $avgRating ? round($avgRating, 2) : 0,
            ];
        }
        
        return $data;
    }

    /**
     * Get penalty distribution
     */
    private function getPenaltyDistributionData(): array
    {
        return [
            'pending' => Penalty::where('status', 'pending')->count(),
            'paid' => Penalty::where('status', 'paid')->count(),
            'waived' => Penalty::where('status', 'waived')->count(),
        ];
    }

    /**
     * Calculate user satisfaction score
     */
    private function calculateUserSatisfaction(): float
    {
        $completedTransactions = Transaction::where('status', 'completed')->count();
        $totalTransactions = Transaction::count();

        if ($totalTransactions === 0) return 0;

        return round(($completedTransactions / $totalTransactions) * 100, 2);
    }

    /**
     * Calculate platform health score (0-100)
     */
    private function calculatePlatformHealthScore(): int
    {
        $score = 100;

        // Deduct for overdue items
        $overdueCount = Transaction::where('status', 'active')
            ->where('due_date', '<', now()->toDateString())
            ->count();
        $score -= min($overdueCount * 2, 20);

        // Deduct for low ratings
        $avgRating = Rating::avg('rating');
        if ($avgRating < 3.5) {
            $score -= 15;
        } elseif ($avgRating < 4) {
            $score -= 5;
        }

        // Deduct for unpaid penalties
        $unpaidCount = Penalty::where('status', 'pending')->count();
        $score -= min($unpaidCount, 10);

        // Deduct for cancelled transactions
        $cancelledCount = Transaction::where('status', 'cancelled')->count();
        $score -= min($cancelledCount * 0.5, 10);

        return max($score, 0); // Don't go below 0
    }

    /**
     * Get quick stats API endpoint
     */
    public function quickStats()
    {
        $stats = $this->getOverallStats();

        return response()->json([
            'total_users' => $stats['total_users'],
            'active_users' => $stats['active_users'],
            'total_items' => $stats['total_items'],
            'available_items' => $stats['available_items'],
            'active_transactions' => $stats['active_transactions'],
            'pending_penalties' => $stats['pending_penalties'],
            'platform_health_score' => $stats['platform_health_score'],
            'avg_rating' => $stats['avg_rating'],
        ]);
    }

    /**
     * Export dashboard report as PDF
     */
    public function exportReport()
    {
        $stats = $this->getOverallStats();
        $activities = $this->getRecentActivities();

        // TODO: Implement PDF export using Laravel PDF library
        // For now, return as JSON
        return response()->json([
            'stats' => $stats,
            'activities' => $activities,
            'generated_at' => now()->format('Y-m-d H:i:s'),
        ]);
    }
}

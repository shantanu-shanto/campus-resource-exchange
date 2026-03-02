<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Penalty;
use App\Models\Transaction;
use App\Models\University;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class SuperAdminDashboardController extends Controller
{
    /**
     * Super admin dashboard — platform-wide overview.
     */
    public function index(): View
    {
        // ── University stats ─────────────────────────────────────
        $totalUniversities   = University::count();
        $pendingUniversities = University::pending()->count();
        $approvedUniversities = University::approved()->count();

        // ── User stats (all universities) ────────────────────────
        $totalUsers    = User::where('role', 'user')->count();
        $pendingUsers  = User::where('role', 'user')->where('status', 'pending')->count();
        $verifiedUsers = User::where('role', 'user')->where('status', 'verified')->count();

        // ── Item & transaction stats ─────────────────────────────
        $totalItems          = Item::count();
        $totalTransactions   = Transaction::count();
        $lateTransactions    = Transaction::where('status', 'late')->count();
        $totalPendingPenalties = Penalty::pending()->sum('amount');

        // ── Recent activity ──────────────────────────────────────
        $pendingUniversityApplications = University::pending()
            ->latest()
            ->take(5)
            ->get();

        $recentUniversities = University::approved()
            ->latest('approved_at')
            ->take(5)
            ->get();

        $recentUsers = User::where('role', 'user')
            ->with('university')
            ->latest()
            ->take(5)
            ->get();

        return view('super-admin.dashboard.index', compact(
            'totalUniversities', 'pendingUniversities', 'approvedUniversities',
            'totalUsers', 'pendingUsers', 'verifiedUsers',
            'totalItems', 'totalTransactions', 'lateTransactions', 'totalPendingPenalties',
            'pendingUniversityApplications', 'recentUniversities', 'recentUsers'
        ));
    }

    /**
     * AJAX quick stats for dashboard refresh.
     */
    public function quickStats(): JsonResponse
    {
        return response()->json([
            'pending_universities' => University::pending()->count(),
            'total_users'         => User::where('role', 'user')->count(),
            'active_transactions' => Transaction::whereIn('status', ['active', 'pending'])->count(),
            'late_transactions'   => Transaction::where('status', 'late')->count(),
            'pending_penalties'   => Penalty::pending()->count(),
        ]);
    }
}
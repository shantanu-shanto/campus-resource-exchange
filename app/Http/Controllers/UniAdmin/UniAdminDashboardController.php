<?php

namespace App\Http\Controllers\UniAdmin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Penalty;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UniAdminDashboardController extends Controller
{
    /**
     * University admin dashboard.
     * All data is scoped to the uni admin's own university.
     */
    public function index(): View
    {
        $universityId = auth()->user()->university_id;

        // ── User stats ──────────────────────────────────────────
        $totalUsers   = User::where('university_id', $universityId)->where('role', 'user')->count();
        $pendingUsers = User::where('university_id', $universityId)
            ->where('role', 'user')
            ->where('status', 'pending')
            ->count();
        $verifiedUsers  = User::where('university_id', $universityId)
            ->where('role', 'user')
            ->where('status', 'verified')
            ->count();

        // ── Item stats ───────────────────────────────────────────
        $totalItems     = Item::where('university_id', $universityId)->count();
        $availableItems = Item::where('university_id', $universityId)->where('status', 'available')->count();
        $borrowedItems  = Item::where('university_id', $universityId)->where('status', 'borrowed')->count();

        // ── Transaction stats ────────────────────────────────────
        $activeTransactions = Transaction::forUniversity($universityId)
            ->whereIn('status', ['active', 'pending'])
            ->count();
        $lateTransactions = Transaction::forUniversity($universityId)
            ->where('status', 'late')
            ->count();

        // ── Penalty stats ────────────────────────────────────────
        $pendingPenalties = Penalty::forUniversity($universityId)->pending()->count();
        $totalPenaltyAmount = Penalty::forUniversity($universityId)->pending()->sum('amount');

        // ── Recent activity ──────────────────────────────────────
        $recentPendingUsers = User::where('university_id', $universityId)
            ->where('role', 'user')
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        $recentTransactions = Transaction::forUniversity($universityId)
            ->with(['item', 'borrower', 'owner'])
            ->latest()
            ->take(5)
            ->get();

        $recentPenalties = Penalty::forUniversity($universityId)
            ->pending()
            ->with(['transaction.item', 'transaction.borrower'])
            ->latest()
            ->take(5)
            ->get();

        return view('uni-admin.dashboard.index', compact(
            'totalUsers', 'pendingUsers', 'verifiedUsers',
            'totalItems', 'availableItems', 'borrowedItems',
            'activeTransactions', 'lateTransactions',
            'pendingPenalties', 'totalPenaltyAmount',
            'recentPendingUsers', 'recentTransactions', 'recentPenalties'
        ));
    }
}
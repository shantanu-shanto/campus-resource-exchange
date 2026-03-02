<?php

namespace App\Http\Controllers\UniAdmin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Penalty;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UniAdminReportController extends Controller
{
    /**
     * Helper to get the authenticated uni admin's university ID.
     * Replaces the old constructor middleware pattern removed in Laravel 12.
     */
    private function universityId(): int
    {
        return auth()->user()->university_id;
    }

    /**
     * Report index — overview of all report types.
     */
    public function index(): View
    {
        return view('uni-admin.reports.index');
    }

    /**
     * User report — registrations, verifications, activity.
     */
    public function userReport(Request $request): View
    {
        $uniId  = $this->universityId();
        $period = $request->get('period', 30);

        $totalUsers    = User::where('university_id', $uniId)->where('role', 'user')->count();
        $newUsers      = User::where('university_id', $uniId)
            ->where('role', 'user')
            ->where('created_at', '>=', now()->subDays($period))
            ->count();
        $pendingUsers  = User::where('university_id', $uniId)
            ->where('role', 'user')->where('status', 'pending')->count();
        $verifiedUsers = User::where('university_id', $uniId)
            ->where('role', 'user')->where('status', 'verified')->count();
        $rejectedUsers = User::where('university_id', $uniId)
            ->where('role', 'user')->where('status', 'rejected')->count();

        $monthlyRegistrations = User::where('university_id', $uniId)
            ->where('role', 'user')
            ->where('created_at', '>=', now()->subMonths(6))
            ->selectRaw('MONTH(created_at) as month, YEAR(created_at) as year, COUNT(*) as count')
            ->groupByRaw('YEAR(created_at), MONTH(created_at)')
            ->orderByRaw('YEAR(created_at), MONTH(created_at)')
            ->get();

        return view('uni-admin.reports.users', compact(
            'totalUsers', 'newUsers', 'pendingUsers',
            'verifiedUsers', 'rejectedUsers',
            'monthlyRegistrations', 'period'
        ));
    }

    /**
     * Transaction report — lending/selling activity overview.
     */
    public function transactionReport(Request $request): View
    {
        $uniId  = $this->universityId();
        $period = $request->get('period', 30);

        $totalTransactions  = Transaction::forUniversity($uniId)->count();
        $activeTransactions = Transaction::forUniversity($uniId)
            ->whereIn('status', ['active', 'pending'])->count();
        $lateTransactions   = Transaction::forUniversity($uniId)->where('status', 'late')->count();
        $completedCount     = Transaction::forUniversity($uniId)->where('status', 'completed')->count();
        $newTransactions    = Transaction::forUniversity($uniId)
            ->where('created_at', '>=', now()->subDays($period))->count();

        $lendCount  = Transaction::forUniversity($uniId)->where('type', 'lend')->count();
        $sellCount  = Transaction::forUniversity($uniId)->where('type', 'sell')->count();
        $shareCount = Transaction::forUniversity($uniId)->where('type', 'share')->count();

        return view('uni-admin.reports.transactions', compact(
            'totalTransactions', 'activeTransactions', 'lateTransactions',
            'completedCount', 'newTransactions',
            'lendCount', 'sellCount', 'shareCount', 'period'
        ));
    }

    /**
     * Penalty report — outstanding, paid, waived breakdown.
     */
    public function penaltyReport(Request $request): View
    {
        $uniId  = $this->universityId();
        $period = $request->get('period', 30);

        $totalPenalties   = Penalty::forUniversity($uniId)->count();
        $pendingPenalties = Penalty::forUniversity($uniId)->pending()->count();
        $paidPenalties    = Penalty::forUniversity($uniId)->paid()->count();
        $waivedPenalties  = Penalty::forUniversity($uniId)->waived()->count();

        $totalOutstanding = Penalty::forUniversity($uniId)->pending()->sum('amount');
        $totalCollected   = Penalty::forUniversity($uniId)->paid()->sum('amount');

        $recentPenalties  = Penalty::forUniversity($uniId)
            ->with(['transaction.item', 'transaction.borrower'])
            ->latest()
            ->take(10)
            ->get();

        return view('uni-admin.reports.penalties', compact(
            'totalPenalties', 'pendingPenalties', 'paidPenalties', 'waivedPenalties',
            'totalOutstanding', 'totalCollected',
            'recentPenalties', 'period'
        ));
    }

    /**
     * Export report as CSV.
     */
    public function export(Request $request)
    {
        $type = $request->get('type', 'users');

        $filename = "uni-report-{$type}-" . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = match($type) {
            'users'        => $this->exportUsers(),
            'transactions' => $this->exportTransactions(),
            'penalties'    => $this->exportPenalties(),
            default        => $this->exportUsers(),
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportUsers(): \Closure
    {
        $uniId = $this->universityId();

        return function () use ($uniId) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Name', 'Email', 'Status', 'Registered At']);

            User::where('university_id', $uniId)
                ->where('role', 'user')
                ->each(function (User $user) use ($handle) {
                    fputcsv($handle, [
                        $user->name,
                        $user->email,
                        $user->status,
                        $user->created_at->format('Y-m-d'),
                    ]);
                });

            fclose($handle);
        };
    }

    private function exportTransactions(): \Closure
    {
        $uniId = $this->universityId();

        return function () use ($uniId) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Item', 'Borrower', 'Owner', 'Type', 'Status', 'Start Date', 'Due Date']);

            Transaction::forUniversity($uniId)
                ->with(['item', 'borrower', 'owner'])
                ->each(function (Transaction $t) use ($handle) {
                    fputcsv($handle, [
                        $t->item->title ?? 'N/A',
                        $t->borrower->name ?? 'N/A',
                        $t->owner->name ?? 'N/A',
                        $t->type,
                        $t->status,
                        $t->start_date?->format('Y-m-d'),
                        $t->due_date?->format('Y-m-d'),
                    ]);
                });

            fclose($handle);
        };
    }

    private function exportPenalties(): \Closure
    {
        $uniId = $this->universityId();

        return function () use ($uniId) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Borrower', 'Item', 'Days Late', 'Amount', 'Status', 'Date']);

            Penalty::forUniversity($uniId)
                ->with(['transaction.borrower', 'transaction.item'])
                ->each(function (Penalty $p) use ($handle) {
                    fputcsv($handle, [
                        $p->transaction->borrower->name ?? 'N/A',
                        $p->transaction->item->title ?? 'N/A',
                        $p->days_late,
                        $p->amount,
                        $p->status,
                        $p->created_at->format('Y-m-d'),
                    ]);
                });

            fclose($handle);
        };
    }
}
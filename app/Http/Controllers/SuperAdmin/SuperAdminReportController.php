<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Penalty;
use App\Models\Transaction;
use App\Models\University;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SuperAdminReportController extends Controller
{
    /**
     * Report index page.
     */
    public function index(): View
    {
        return view('super-admin.reports.index');
    }

    /**
     * University-level report — status breakdown, growth over time.
     */
    public function universityReport(Request $request): View
    {
        $period = $request->get('period', 30);

        $totalUniversities    = University::count();
        $approvedUniversities = University::approved()->count();
        $pendingUniversities  = University::pending()->count();
        $rejectedUniversities = University::rejected()->count();
        $newApplications      = University::where('created_at', '>=', now()->subDays($period))->count();

        // Universities with most users
        $topUniversities = University::approved()
            ->withCount(['users' => fn($q) => $q->where('role', 'user')])
            ->orderByDesc('users_count')
            ->take(10)
            ->get();

        // Monthly applications for chart
        $monthlyApplications = University::where('created_at', '>=', now()->subMonths(6))
            ->selectRaw('MONTH(created_at) as month, YEAR(created_at) as year, COUNT(*) as count')
            ->groupByRaw('YEAR(created_at), MONTH(created_at)')
            ->orderByRaw('YEAR(created_at), MONTH(created_at)')
            ->get();

        // State-wise distribution
        $byState = University::approved()
            ->selectRaw('state, COUNT(*) as count')
            ->groupBy('state')
            ->orderByDesc('count')
            ->get();

        return view('super-admin.reports.universities', compact(
            'totalUniversities', 'approvedUniversities', 'pendingUniversities',
            'rejectedUniversities', 'newApplications',
            'topUniversities', 'monthlyApplications', 'byState', 'period'
        ));
    }

    /**
     * Platform-wide overview report.
     */
    public function platformOverview(Request $request): View
    {
        $period = $request->get('period', 30);

        // Totals
        $totalUsers        = User::where('role', 'user')->count();
        $totalItems        = Item::count();
        $totalTransactions = Transaction::count();
        $totalPenalties    = Penalty::count();

        // Period activity
        $newUsers         = User::where('role', 'user')
            ->where('created_at', '>=', now()->subDays($period))->count();
        $newItems         = Item::where('created_at', '>=', now()->subDays($period))->count();
        $newTransactions  = Transaction::where('created_at', '>=', now()->subDays($period))->count();

        // Transaction type breakdown
        $lendCount  = Transaction::where('type', 'lend')->count();
        $sellCount  = Transaction::where('type', 'sell')->count();
        $shareCount = Transaction::where('type', 'share')->count();

        // Penalty summary
        $outstandingPenalties = Penalty::pending()->sum('amount');
        $collectedPenalties   = Penalty::paid()->sum('amount');

        return view('super-admin.reports.platform-overview', compact(
            'totalUsers', 'totalItems', 'totalTransactions', 'totalPenalties',
            'newUsers', 'newItems', 'newTransactions',
            'lendCount', 'sellCount', 'shareCount',
            'outstandingPenalties', 'collectedPenalties', 'period'
        ));
    }

    /**
     * User growth report — registrations over time.
     */
    public function userGrowth(Request $request): View
    {
        $monthlyUsers = User::where('role', 'user')
            ->where('created_at', '>=', now()->subMonths(12))
            ->selectRaw('MONTH(created_at) as month, YEAR(created_at) as year, COUNT(*) as count')
            ->groupByRaw('YEAR(created_at), MONTH(created_at)')
            ->orderByRaw('YEAR(created_at), MONTH(created_at)')
            ->get();

        $usersByUniversity = University::approved()
            ->withCount(['users' => fn($q) => $q->where('role', 'user')->where('status', 'verified')])
            ->orderByDesc('users_count')
            ->get();

        return view('super-admin.reports.user-growth', compact('monthlyUsers', 'usersByUniversity'));
    }

    /**
     * Export a platform-wide report as CSV.
     */
    public function export(Request $request)
    {
        $type = $request->get('type', 'universities');

        $filename = "platform-report-{$type}-" . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = match($type) {
            'universities' => $this->exportUniversities(),
            'users'        => $this->exportUsers(),
            'transactions' => $this->exportTransactions(),
            default        => $this->exportUniversities(),
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportUniversities(): \Closure
    {
        return function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Name', 'Domain', 'State', 'City', 'Status', 'Applicant', 'Applied At', 'Approved At']);

            University::each(function (University $u) use ($handle) {
                fputcsv($handle, [
                    $u->name,
                    $u->domain,
                    $u->state,
                    $u->city,
                    $u->status,
                    $u->applicant_name,
                    $u->created_at->format('Y-m-d'),
                    $u->approved_at?->format('Y-m-d'),
                ]);
            });

            fclose($handle);
        };
    }

    private function exportUsers(): \Closure
    {
        return function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Name', 'Email', 'Role', 'Status', 'University', 'Registered At']);

            User::with('university')->each(function (User $u) use ($handle) {
                fputcsv($handle, [
                    $u->name,
                    $u->email,
                    $u->role,
                    $u->status,
                    $u->university->name ?? 'N/A',
                    $u->created_at->format('Y-m-d'),
                ]);
            });

            fclose($handle);
        };
    }

    private function exportTransactions(): \Closure
    {
        return function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Item', 'University', 'Type', 'Status', 'Borrower', 'Owner', 'Start Date', 'Due Date']);

            Transaction::with(['item.university', 'borrower', 'owner'])
                ->each(function (Transaction $t) use ($handle) {
                    fputcsv($handle, [
                        $t->item->title ?? 'N/A',
                        $t->item->university->name ?? 'N/A',
                        $t->type,
                        $t->status,
                        $t->borrower->name ?? 'N/A',
                        $t->owner->name ?? 'N/A',
                        $t->start_date?->format('Y-m-d'),
                        $t->due_date?->format('Y-m-d'),
                    ]);
                });

            fclose($handle);
        };
    }
}
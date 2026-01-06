<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Penalty;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class AdminPenaltyManagementController extends Controller
{
    /**
     * Display list of all penalties
     */
    public function index(Request $request)
    {
        $query = Penalty::query();

        // Filter by status
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        // Filter by date range
        if ($startDate = $request->get('start_date')) {
            $query->where('created_at', '>=', Carbon::parse($startDate)->startOfDay());
        }
        if ($endDate = $request->get('end_date')) {
            $query->where('created_at', '<=', Carbon::parse($endDate)->endOfDay());
        }

        // Filter by amount range
        if ($minAmount = $request->get('min_amount')) {
            $query->where('amount', '>=', $minAmount);
        }
        if ($maxAmount = $request->get('max_amount')) {
            $query->where('amount', '<=', $maxAmount);
        }

        // Search by borrower
        if ($search = $request->get('search')) {
            $query->whereHas('transaction.borrower', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->orWhereHas('transaction.item', function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%");
            });
        }

        // Filter overdue payments (unpaid for 7+ days)
        if ($request->get('overdue_payment')) {
            $query->where('status', 'pending')
                ->where('created_at', '<=', now()->subDays(7));
        }

        // Filter high-value penalties
        if ($request->get('high_value')) {
            $query->where('amount', '>=', 500);
        }

        // Sort options
        $sort = $request->get('sort', 'recent');
        switch ($sort) {
            case 'oldest':
                $query->oldest('created_at');
                break;
            case 'amount_high':
                $query->orderByDesc('amount');
                break;
            case 'amount_low':
                $query->orderBy('amount');
                break;
            case 'days_late_high':
                $query->orderByDesc('days_late');
                break;
            case 'days_late_low':
                $query->orderBy('days_late');
                break;
            case 'recent':
            default:
                $query->latest('created_at');
        }

        // Paginate with relationships
        $penalties = $query->with([
            'transaction:id,borrower_id,item_id',
            'transaction.borrower:id,name,email',
            'transaction.item:id,title',
        ])
            ->paginate(15);

        // Add computed properties
        $penalties->getCollection()->transform(function($penalty) {
            return [
                'id' => $penalty->id,
                'transaction_id' => $penalty->transaction_id,
                'borrower_name' => $penalty->transaction->borrower->name,
                'borrower_email' => $penalty->transaction->borrower->email,
                'item_title' => $penalty->transaction->item->title,
                'days_late' => $penalty->days_late,
                'amount' => $penalty->amount,
                'status' => $penalty->status,
                'created_at' => $penalty->created_at,
                'days_unpaid' => $penalty->status === 'pending' 
                    ? now()->diffInDays($penalty->created_at) 
                    : 0,
                'overdue_for_payment' => $penalty->status === 'pending' && $penalty->created_at->diffInDays(now()) >= 7,
            ];
        });

        return view('admin.penalties.index', compact('penalties', 'request'));
    }

    /**
     * Show penalty details
     */
    public function show(Penalty $penalty)
    {
        $penalty->load([
            'transaction' => fn($q) => $q->with([
                'item:id,title,description,user_id',
                'item.owner:id,name,email,profile_image',
                'borrower:id,name,email,profile_image',
            ]),
        ]);

        // Penalty details
        $details = [
            'issue_date' => $penalty->created_at,
            'days_unpaid' => $penalty->status === 'pending' 
                ? now()->diffInDays($penalty->created_at) 
                : 0,
            'overdue_for_payment' => $penalty->status === 'pending' && $penalty->created_at->diffInDays(now()) >= 7,
            'borrower_previous_penalties' => Penalty::whereHas('transaction', fn($q) => $q->where('borrower_id', $penalty->transaction->borrower_id))
                ->where('id', '!=', $penalty->id)
                ->count(),
            'borrower_total_penalties' => Penalty::whereHas('transaction', fn($q) => $q->where('borrower_id', $penalty->transaction->borrower_id))
                ->sum('amount'),
        ];

        // Borrower history
        $borrowerHistory = [
            'total_transactions' => Transaction::where('borrower_id', $penalty->transaction->borrower_id)->count(),
            'completed_transactions' => Transaction::where('borrower_id', $penalty->transaction->borrower_id)
                ->where('status', 'completed')
                ->count(),
            'late_transactions' => Transaction::where('borrower_id', $penalty->transaction->borrower_id)
                ->where('status', 'late')
                ->count(),
            'avg_rating' => round($penalty->transaction->borrower->averageRating(), 2),
            'total_penalties' => $details['borrower_total_penalties'],
        ];

        // Penalty payment history
        $paymentHistory = $this->getPenaltyPaymentHistory($penalty);

        // Waiver requests
        $waiverRequests = $this->getWaiverRequests($penalty);

        return view('admin.penalties.show', compact(
            'penalty',
            'details',
            'borrowerHistory',
            'paymentHistory',
            'waiverRequests'
        ));
    }

    /**
     * Mark penalty as paid
     */
    public function markPaid(Penalty $penalty)
    {
        if ($penalty->status !== 'pending') {
            return back()->with('error', 'Only pending penalties can be marked as paid.');
        }

        $penalty->markAsPaid();

        // TODO: Send payment confirmation to borrower
        // Notification::send($penalty->transaction->borrower, new PenaltyPaidNotification($penalty));

        return back()->with('success', "Penalty of ৳{$penalty->amount} marked as paid.");
    }

    /**
     * Show waive penalty form
     */
    public function showWaiveForm(Penalty $penalty)
    {
        if ($penalty->status !== 'pending') {
            return back()->with('error', 'Only pending penalties can be waived.');
        }

        return view('admin.penalties.waive', compact('penalty'));
    }

    /**
     * Waive penalty
     */
    public function waive(Request $request, Penalty $penalty)
    {
        if ($penalty->status !== 'pending') {
            return back()->with('error', 'Only pending penalties can be waived.');
        }

        $validated = $request->validate([
            'reason' => 'required|string|min:20|max:500',
            'approved_by_note' => 'nullable|string|max:300',
        ]);

        $penalty->update([
            'status' => 'waived',
            'waived_reason' => $validated['reason'],
            'waived_by' => auth()->id(),
            'waived_at' => now(),
        ]);

        // TODO: Notify borrower about waiver approval
        // Notification::send($penalty->transaction->borrower, new PenaltyWaivedNotification($penalty, $validated['reason']));

        return redirect()->route('admin.penalties.show', $penalty)
            ->with('success', "Penalty of ৳{$penalty->amount} has been waived.");
    }

    /**
     * Request payment from borrower
     */
    public function requestPayment(Penalty $penalty)
    {
        if ($penalty->status !== 'pending') {
            return back()->with('error', 'Only pending penalties require payment.');
        }

        // TODO: Send payment request notification
        // Notification::send($penalty->transaction->borrower, new PaymentRequestNotification($penalty));

        // Mark that payment was requested
        $penalty->update(['payment_requested_at' => now()]);

        return back()->with('success', 'Payment request sent to borrower.');
    }

    /**
     * Send reminder for unpaid penalty
     */
    public function sendReminder(Penalty $penalty)
    {
        if ($penalty->status !== 'pending') {
            return back()->with('error', 'Only pending penalties need reminders.');
        }

        // TODO: Send reminder notification
        // Notification::send($penalty->transaction->borrower, new PenaltyPaymentReminderNotification($penalty));

        $penalty->update(['last_reminder_at' => now()]);

        return back()->with('success', 'Payment reminder sent to borrower.');
    }

    /**
     * Batch approve/pay penalties
     */
    public function bulkApprove(Request $request)
    {
        $validated = $request->validate([
            'penalties' => 'required|array',
            'penalties.*' => 'integer|exists:penalties,id',
        ]);

        $penalties = Penalty::whereIn('id', $validated['penalties'])
            ->where('status', 'pending')
            ->update(['status' => 'paid']);

        return back()->with('success', "{$penalties} penalties marked as paid.");
    }

    /**
     * Batch waive penalties
     */
    public function bulkWaive(Request $request)
    {
        $validated = $request->validate([
            'penalties' => 'required|array',
            'penalties.*' => 'integer|exists:penalties,id',
            'reason' => 'required|string|min:10|max:500',
        ]);

        $penalties = Penalty::whereIn('id', $validated['penalties'])
            ->where('status', 'pending')
            ->update([
                'status' => 'waived',
                'waived_reason' => $validated['reason'],
                'waived_by' => auth()->id(),
                'waived_at' => now(),
            ]);

        return back()->with('success', "{$penalties} penalties waived.");
    }

    /**
     * Get penalty statistics
     */
    public function statistics()
    {
        $totalPenalties = Penalty::count();
        $totalAmount = Penalty::sum('amount');

        $byStatus = [
            'pending' => Penalty::where('status', 'pending')->count(),
            'paid' => Penalty::where('status', 'paid')->count(),
            'waived' => Penalty::where('status', 'waived')->count(),
        ];

        $pendingAmount = Penalty::where('status', 'pending')->sum('amount');
        $paidAmount = Penalty::where('status', 'paid')->sum('amount');
        $waivedAmount = Penalty::where('status', 'waived')->sum('amount');

        // Overdue payments (unpaid for 7+ days)
        $overdueCount = Penalty::where('status', 'pending')
            ->where('created_at', '<=', now()->subDays(7))
            ->count();

        $overdueAmount = Penalty::where('status', 'pending')
            ->where('created_at', '<=', now()->subDays(7))
            ->sum('amount');

        // High-value penalties (৳500+)
        $highValueCount = Penalty::where('amount', '>=', 500)->count();
        $highValueAmount = Penalty::where('amount', '>=', 500)->sum('amount');

        // Average penalty
        $avgPenalty = $totalPenalties > 0 ? $totalAmount / $totalPenalties : 0;

        // Recovery rate (paid out of total issued)
        $recoveryRate = $totalPenalties > 0 
            ? round(($byStatus['paid'] / ($byStatus['pending'] + $byStatus['paid'])) * 100, 2)
            : 0;

        // Most common days late
        $commonDaysLate = Penalty::selectRaw('days_late, COUNT(*) as count')
            ->groupBy('days_late')
            ->orderByDesc('count')
            ->limit(5)
            ->get()
            ->map(fn($p) => [
                'days_late' => $p->days_late,
                'count' => $p->count,
            ]);

        // Trend (last 30 days)
        $trend = $this->getPenaltyTrend();

        return response()->json([
            'total_penalties' => $totalPenalties,
            'total_amount' => round($totalAmount, 2),
            'by_status' => $byStatus,
            'amount_by_status' => [
                'pending' => round($pendingAmount, 2),
                'paid' => round($paidAmount, 2),
                'waived' => round($waivedAmount, 2),
            ],
            'overdue_count' => $overdueCount,
            'overdue_amount' => round($overdueAmount, 2),
            'high_value_count' => $highValueCount,
            'high_value_amount' => round($highValueAmount, 2),
            'avg_penalty' => round($avgPenalty, 2),
            'recovery_rate' => $recoveryRate,
            'common_days_late' => $commonDaysLate,
            'trend' => $trend,
        ]);
    }

    /**
     * Get penalty report
     */
    public function report(Request $request)
    {
        $startDate = $request->get('start_date', now()->subMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        $penalties = Penalty::whereBetween('created_at', [
            Carbon::parse($startDate)->startOfDay(),
            Carbon::parse($endDate)->endOfDay(),
        ])->with(['transaction.borrower:id,name', 'transaction.item:id,title'])
            ->get();

        $report = [
            'period' => "{$startDate} to {$endDate}",
            'total_issued' => $penalties->count(),
            'total_amount' => $penalties->sum('amount'),
            'by_status' => [
                'pending' => $penalties->where('status', 'pending')->count(),
                'paid' => $penalties->where('status', 'paid')->count(),
                'waived' => $penalties->where('status', 'waived')->count(),
            ],
            'amount_by_status' => [
                'pending' => $penalties->where('status', 'pending')->sum('amount'),
                'paid' => $penalties->where('status', 'paid')->sum('amount'),
                'waived' => $penalties->where('status', 'waived')->sum('amount'),
            ],
            'avg_days_late' => round($penalties->avg('days_late'), 2),
            'top_borrowers' => $this->getTopBorrowersByPenalties($penalties),
            'top_items' => $this->getTopItemsByPenalties($penalties),
        ];

        return view('admin.penalties.report', compact('report', 'penalties', 'startDate', 'endDate'));
    }

    /**
     * Export penalties as CSV
     */
    public function exportPenalties(Request $request)
    {
        $penalties = Penalty::with([
            'transaction.borrower:id,name,email',
            'transaction.item:id,title',
        ])->get();

        $filename = "penalties_" . now()->format('Y-m-d') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($penalties) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Borrower', 'Item', 'Days Late', 'Amount', 'Status', 'Created At']);

            foreach ($penalties as $penalty) {
                fputcsv($file, [
                    $penalty->id,
                    $penalty->transaction->borrower->name,
                    $penalty->transaction->item->title,
                    $penalty->days_late,
                    $penalty->amount,
                    $penalty->status,
                    $penalty->created_at->format('Y-m-d'),
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
     * Get penalty payment history
     */
    private function getPenaltyPaymentHistory(Penalty $penalty): array
    {
        $history = [];

        $history[] = [
            'type' => 'created',
            'title' => 'Penalty Created',
            'description' => "Penalty of ৳{$penalty->amount} for {$penalty->days_late} days late",
            'timestamp' => $penalty->created_at,
        ];

        if ($penalty->payment_requested_at) {
            $history[] = [
                'type' => 'payment_requested',
                'title' => 'Payment Requested',
                'timestamp' => $penalty->payment_requested_at,
            ];
        }

        if ($penalty->last_reminder_at) {
            $history[] = [
                'type' => 'reminder_sent',
                'title' => 'Payment Reminder Sent',
                'timestamp' => $penalty->last_reminder_at,
            ];
        }

        if ($penalty->status === 'paid') {
            $history[] = [
                'type' => 'paid',
                'title' => 'Penalty Paid',
                'timestamp' => $penalty->updated_at,
            ];
        } elseif ($penalty->status === 'waived') {
            $history[] = [
                'type' => 'waived',
                'title' => 'Penalty Waived',
                'description' => $penalty->waived_reason,
                'timestamp' => $penalty->waived_at,
            ];
        }

        return $history;
    }

    /**
     * Get waiver requests (placeholder)
     */
    private function getWaiverRequests(Penalty $penalty): array
    {
        // TODO: Implement WaiverRequest model
        return [];
    }

    /**
     * Get penalty trend (last 30 days)
     */
    private function getPenaltyTrend(): array
    {
        $trend = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->startOfDay();
            $count = Penalty::whereDate('created_at', $date)->count();
            $amount = Penalty::whereDate('created_at', $date)->sum('amount');

            $trend[] = [
                'date' => $date->format('M d'),
                'count' => $count,
                'amount' => round($amount, 2),
            ];
        }

        return $trend;
    }

    /**
     * Get top borrowers by penalties
     */
    private function getTopBorrowersByPenalties($penalties): array
    {
        return $penalties->groupBy('transaction.borrower_id')
            ->map(function($group) {
                $borrower = $group->first()->transaction->borrower;
                return [
                    'name' => $borrower->name,
                    'email' => $borrower->email,
                    'penalty_count' => $group->count(),
                    'total_amount' => $group->sum('amount'),
                ];
            })
            ->sortByDesc('total_amount')
            ->take(10)
            ->values()
            ->toArray();
    }

    /**
     * Get top items by penalties
     */
    private function getTopItemsByPenalties($penalties): array
    {
        return $penalties->groupBy('transaction.item_id')
            ->map(function($group) {
                $item = $group->first()->transaction->item;
                return [
                    'title' => $item->title,
                    'penalty_count' => $group->count(),
                    'total_amount' => $group->sum('amount'),
                ];
            })
            ->sortByDesc('total_amount')
            ->take(10)
            ->values()
            ->toArray();
    }
}

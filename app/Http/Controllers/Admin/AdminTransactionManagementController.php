<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Item;
use App\Models\User;
use App\Models\Penalty;
use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class AdminTransactionManagementController extends Controller
{
    /**
     * Display list of all transactions
     */
    public function index(Request $request)
    {
        $query = Transaction::query();

        // Filter by status
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        // Filter by type
        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        // Filter by date range
        if ($startDate = $request->get('start_date')) {
            $query->where('created_at', '>=', Carbon::parse($startDate)->startOfDay());
        }
        if ($endDate = $request->get('end_date')) {
            $query->where('created_at', '<=', Carbon::parse($endDate)->endOfDay());
        }

        // Search by borrower or item
        if ($search = $request->get('search')) {
            $query->whereHas('borrower', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->orWhereHas('item', function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%");
            });
        }

        // Filter overdue transactions
        if ($request->get('overdue')) {
            $query->where('status', 'active')
                ->where('due_date', '<', now()->toDateString());
        }

        // Filter disputed transactions
        if ($request->get('disputed')) {
            $query->whereHas('penalties', function($q) {
                $q->where('status', 'pending');
            });
        }

        // Sort options
        $sort = $request->get('sort', 'recent');
        switch ($sort) {
            case 'oldest':
                $query->oldest('created_at');
                break;
            case 'amount_high':
                $query->orderByDesc('final_price');
                break;
            case 'amount_low':
                $query->orderBy('final_price');
                break;
            case 'recent':
            default:
                $query->latest('created_at');
        }

        // Paginate with relationships
        $transactions = $query->with([
            'item:id,title,user_id',
            'item.owner:id,name',
            'borrower:id,name,email',
            'penalties' => fn($q) => $q->where('status', 'pending'),
        ])
            ->withCount('penalties')
            ->paginate(15);

        // Add computed properties
        $transactions->getCollection()->transform(function($transaction) {
            return [
                'id' => $transaction->id,
                'item_title' => $transaction->item->title,
                'owner_name' => $transaction->item->owner->name,
                'borrower_name' => $transaction->borrower->name,
                'borrower_email' => $transaction->borrower->email,
                'type' => $transaction->type,
                'status' => $transaction->status,
                'start_date' => $transaction->start_date,
                'due_date' => $transaction->due_date,
                'return_date' => $transaction->return_date,
                'amount' => $transaction->final_price ?? $transaction->deposit_amount,
                'penalties_count' => $transaction->penalties_count,
                'created_at' => $transaction->created_at,
                'health_status' => $this->getTransactionHealthStatus($transaction),
            ];
        });

        return view('admin.transactions.index', compact('transactions', 'request'));
    }

    /**
     * Show transaction details
     */
    public function show(Transaction $transaction)
    {
        $transaction->load([
            'item:id,title,description,user_id,price,availability_mode',
            'item.owner:id,name,email,profile_image',
            'borrower:id,name,email,profile_image',
            'ratings' => fn($q) => $q->with('rater:id,name'),
            'penalties' => fn($q) => $q->latest(),
        ]);

        // Transaction statistics
        $stats = [
            'duration' => $transaction->start_date && $transaction->return_date
                ? $transaction->return_date->diffInDays($transaction->start_date)
                : null,
            'days_overdue' => $this->calculateDaysOverdue($transaction),
            'total_penalties' => $transaction->penalties()->sum('amount'),
            'unpaid_penalties' => $transaction->penalties()->where('status', 'pending')->sum('amount'),
            'ratings_count' => $transaction->ratings()->count(),
        ];

        // Owner info
        $ownerInfo = [
            'name' => $transaction->item->owner->name,
            'email' => $transaction->item->owner->email,
            'avg_rating' => round($transaction->item->owner->averageRating(), 2),
            'total_items' => $transaction->item->owner->items()->count(),
        ];

        // Borrower info
        $borrowerInfo = [
            'name' => $transaction->borrower->name,
            'email' => $transaction->borrower->email,
            'avg_rating' => round($transaction->borrower->averageRating(), 2),
            'total_transactions' => $transaction->borrower->transactionsAsBorrower()->count(),
            'unpaid_penalties' => Penalty::whereHas('transaction', fn($q) => $q->where('borrower_id', $transaction->borrower->id))
                ->where('status', 'pending')
                ->sum('amount'),
        ];

        // Dispute history
        $disputes = $this->getTransactionDisputes($transaction);

        return view('admin.transactions.show', compact(
            'transaction',
            'stats',
            'ownerInfo',
            'borrowerInfo',
            'disputes'
        ));
    }

    /**
     * Update transaction
     */
    public function update(Request $request, Transaction $transaction)
    {
        $action = $request->get('action');

        switch ($action) {
            case 'approve':
                return $this->approveTransaction($transaction);
            case 'mark-active':
                return $this->markTransactionActive($transaction);
            case 'mark-completed':
                return $this->markTransactionCompleted($transaction);
            case 'mark-late':
                return $this->markTransactionLate($transaction);
            case 'cancel':
                return $this->cancelTransaction($request, $transaction);
            default:
                return back()->with('error', 'Invalid action.');
        }
    }

    /**
     * Approve pending transaction
     */
    private function approveTransaction(Transaction $transaction)
    {
        if ($transaction->status !== 'pending') {
            return back()->with('error', 'Only pending transactions can be approved.');
        }

        $transaction->markAsActive();

        return back()->with('success', 'Transaction approved and marked as active.');
    }

    /**
     * Mark transaction as active
     */
    private function markTransactionActive(Transaction $transaction)
    {
        if ($transaction->status !== 'pending') {
            return back()->with('error', 'Transaction must be pending.');
        }

        $transaction->markAsActive();

        return back()->with('success', 'Transaction marked as active.');
    }

    /**
     * Mark transaction as completed
     */
    private function markTransactionCompleted(Transaction $transaction)
    {
        if (!in_array($transaction->status, ['active', 'late'])) {
            return back()->with('error', 'Transaction cannot be marked as completed.');
        }

        $transaction->markAsCompleted();
        $transaction->item->markAsAvailable();

        return back()->with('success', 'Transaction marked as completed.');
    }

    /**
     * Mark transaction as late
     */
    private function markTransactionLate(Transaction $transaction)
    {
        if ($transaction->status !== 'active') {
            return back()->with('error', 'Only active transactions can be marked as late.');
        }

        $transaction->markAsLate();

        // Auto-create penalty if not exists
        if (!$transaction->penalties()->exists()) {
            $daysLate = $transaction->daysOverdue();
            $penaltyAmount = Penalty::calculateAmount($daysLate);

            Penalty::create([
                'transaction_id' => $transaction->id,
                'days_late' => $daysLate,
                'amount' => $penaltyAmount,
                'status' => 'pending',
            ]);
        }

        return back()->with('success', 'Transaction marked as late and penalty created.');
    }

    /**
     * Cancel transaction
     */
    private function cancelTransaction(Request $request, Transaction $transaction)
    {
        $validated = $request->validate([
            'reason' => 'required|string|min:10|max:500',
        ]);

        if (!in_array($transaction->status, ['pending', 'active'])) {
            return back()->with('error', 'Cannot cancel completed/late transactions.');
        }

        $transaction->markAsCancelled();
        $transaction->item->markAsAvailable();

        // TODO: Notify both parties about cancellation
        // Notification::send($transaction->borrower, new TransactionCancelledNotification($transaction, $validated['reason']));
        // Notification::send($transaction->item->owner, new TransactionCancelledNotification($transaction, $validated['reason']));

        return back()->with('success', 'Transaction cancelled and both parties notified.');
    }

    /**
     * View transaction penalties
     */
    public function penalties(Transaction $transaction, Request $request)
    {
        $status = $request->get('status');

        $penalties = $transaction->penalties();

        if ($status) {
            $penalties->where('status', $status);
        }

        $penalties = $penalties->latest()->paginate(15);

        return view('admin.transactions.penalties', compact('transaction', 'penalties', 'status'));
    }

    /**
     * Create penalty for transaction
     */
    public function createPenalty(Request $request, Transaction $transaction)
    {
        // Check if transaction is eligible
        if (!in_array($transaction->status, ['active', 'late'])) {
            return back()->with('error', 'Cannot create penalty for this transaction status.');
        }

        $validated = $request->validate([
            'days_late' => 'required|integer|min:1',
            'amount' => 'required|numeric|min:0',
        ]);

        // Check for existing penalty
        if ($transaction->penalties()->exists()) {
            return back()->with('warning', 'Penalty already exists for this transaction.');
        }

        Penalty::create([
            'transaction_id' => $transaction->id,
            'days_late' => $validated['days_late'],
            'amount' => $validated['amount'],
            'status' => 'pending',
        ]);

        return back()->with('success', "Penalty of ৳{$validated['amount']} created.");
    }

    /**
     * Approve/pay penalty
     */
    public function approvePenalty(Penalty $penalty)
    {
        if ($penalty->status !== 'pending') {
            return back()->with('error', 'Only pending penalties can be approved.');
        }

        $penalty->markAsPaid();

        return back()->with('success', "Penalty of ৳{$penalty->amount} marked as paid.");
    }

    /**
     * Waive penalty
     */
    public function waivePenalty(Request $request, Penalty $penalty)
    {
        $validated = $request->validate([
            'reason' => 'required|string|min:10|max:500',
        ]);

        if ($penalty->status !== 'pending') {
            return back()->with('error', 'Only pending penalties can be waived.');
        }

        $penalty->update(['status' => 'waived']);

        // TODO: Notify borrower about waiver
        // Notification::send($penalty->transaction->borrower, new PenaltyWaivedNotification($penalty, $validated['reason']));

        return back()->with('success', "Penalty of ৳{$penalty->amount} waived.");
    }

    /**
     * View transaction ratings
     */
    public function ratings(Transaction $transaction)
    {
        $ratings = $transaction->ratings()
            ->with(['rater:id,name', 'transaction.borrower:id,name', 'transaction.item:id,title'])
            ->latest()
            ->paginate(15);

        return view('admin.transactions.ratings', compact('transaction', 'ratings'));
    }

    /**
     * Resolve rating dispute
     */
    public function resolveRatingDispute(Request $request, Rating $rating)
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['keep', 'remove'])],
            'reason' => 'nullable|string|max:500',
        ]);

        if ($validated['action'] === 'remove') {
            $rating->delete();
            return back()->with('success', 'Rating removed.');
        }

        return back()->with('success', 'Rating kept - dispute resolved.');
    }

    /**
     * Request mediation for dispute
     */
    public function requestMediation(Request $request, Transaction $transaction)
    {
        $validated = $request->validate([
            'issue' => 'required|string|min:20|max:1000',
            'party' => ['required', Rule::in(['borrower', 'owner', 'both'])],
        ]);

        // TODO: Create Dispute/Mediation model
        // Dispute::create([
        //     'transaction_id' => $transaction->id,
        //     'issue' => $validated['issue'],
        //     'party' => $validated['party'],
        //     'created_by' => auth()->id(),
        // ]);

        return back()->with('success', 'Mediation requested.');
    }

    /**
     * Resolve dispute
     */
    public function resolveDispute(Request $request, Transaction $transaction)
    {
        $validated = $request->validate([
            'resolution' => 'required|string|min:20|max:1000',
            'action' => ['required', Rule::in(['refund', 'keep_penalty', 'waive_penalty', 'other'])],
        ]);

        if ($validated['action'] === 'waive_penalty') {
            $transaction->penalties()
                ->where('status', 'pending')
                ->update(['status' => 'waived']);
        }

        // TODO: Update Dispute status to resolved
        // Dispute::where('transaction_id', $transaction->id)->latest()->first()->update(['status' => 'resolved', 'resolution' => $validated['resolution']]);

        return back()->with('success', 'Dispute resolved.');
    }

    /**
     * Get transaction statistics (JSON API)
     */
    public function statistics()
    {
        $totalTransactions = Transaction::count();
        $byStatus = [
            'pending' => Transaction::where('status', 'pending')->count(),
            'active' => Transaction::where('status', 'active')->count(),
            'completed' => Transaction::where('status', 'completed')->count(),
            'late' => Transaction::where('status', 'late')->count(),
            'cancelled' => Transaction::where('status', 'cancelled')->count(),
        ];

        $byType = [
            'lend' => Transaction::where('type', 'lend')->count(),
            'sell' => Transaction::where('type', 'sell')->count(),
        ];

        $avgTransactionValue = Transaction::whereNotNull('final_price')
            ->avg('final_price');

        $totalValue = Transaction::whereNotNull('final_price')
            ->sum('final_price');

        $overdue = Transaction::where('status', 'active')
            ->where('due_date', '<', now()->toDateString())
            ->count();

        return response()->json([
            'total_transactions' => $totalTransactions,
            'by_status' => $byStatus,
            'by_type' => $byType,
            'avg_transaction_value' => round($avgTransactionValue, 2),
            'total_value' => round($totalValue, 2),
            'overdue_count' => $overdue,
            'completion_rate' => $totalTransactions > 0 
                ? round(($byStatus['completed'] / $totalTransactions) * 100, 2)
                : 0,
        ]);
    }

    /**
     * Export transactions as CSV
     */
    public function exportTransactions(Request $request)
    {
        $transactions = Transaction::with([
            'item:id,title',
            'item.owner:id,name',
            'borrower:id,name',
        ])->get();

        $filename = "transactions_" . now()->format('Y-m-d') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($transactions) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Item', 'Owner', 'Borrower', 'Type', 'Status', 'Amount', 'Start Date', 'Due Date', 'Return Date']);

            foreach ($transactions as $transaction) {
                fputcsv($file, [
                    $transaction->id,
                    $transaction->item->title,
                    $transaction->item->owner->name,
                    $transaction->borrower->name,
                    $transaction->type,
                    $transaction->status,
                    $transaction->final_price ?? $transaction->deposit_amount ?? 'N/A',
                    $transaction->start_date?->format('Y-m-d'),
                    $transaction->due_date?->format('Y-m-d'),
                    $transaction->return_date?->format('Y-m-d'),
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
     * Get transaction health status
     */
    private function getTransactionHealthStatus(Transaction $transaction): string
    {
        if ($transaction->status === 'late') {
            return 'Late';
        }

        if ($transaction->status === 'active' && $transaction->due_date && $transaction->due_date->lt(now())) {
            return 'Overdue';
        }

        if ($transaction->status === 'active' && $transaction->due_date && $transaction->due_date->diffInDays(now()) <= 3) {
            return 'Due Soon';
        }

        if ($transaction->penalties()->where('status', 'pending')->exists()) {
            return 'Pending Penalty';
        }

        if ($transaction->status === 'cancelled') {
            return 'Cancelled';
        }

        return 'OK';
    }

    /**
     * Calculate days overdue
     */
    private function calculateDaysOverdue(Transaction $transaction): int
    {
        if (!in_array($transaction->status, ['active', 'late']) || !$transaction->due_date) {
            return 0;
        }

        if ($transaction->return_date) {
            return $transaction->return_date->diffInDays($transaction->due_date);
        }

        return now()->diffInDays($transaction->due_date);
    }

    /**
     * Get transaction disputes
     */
    private function getTransactionDisputes(Transaction $transaction): array
    {
        // TODO: Implement once Dispute model is created
        // return Dispute::where('transaction_id', $transaction->id)->latest()->get()->toArray();
        return [];
    }
}

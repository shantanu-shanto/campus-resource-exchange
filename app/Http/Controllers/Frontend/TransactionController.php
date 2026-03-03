<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Item;
use App\Models\Penalty;
use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    /**
     * Display all transactions for authenticated user
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $borrowerTransactions = Transaction::where('borrower_id', $user->id)
            ->with(['item:id,title,user_id', 'item.owner:id,name,email', 'ratings'])
            ->latest()
            ->get();

        // FIX: use owner_id directly instead of whereHas
        $ownerTransactions = Transaction::where('owner_id', $user->id)
            ->with(['item:id,title,user_id', 'borrower:id,name,email', 'ratings'])
            ->latest()
            ->get();

        $allTransactions = $borrowerTransactions->concat($ownerTransactions)
            ->sortByDesc('created_at');

        $status = $request->get('status');

        if ($status) {
            $allTransactions = $allTransactions->where('status', $status);
        }

        $allCount       = $allTransactions->count();
        $pendingCount   = $allTransactions->where('status', 'pending')->count();
        $activeCount    = $allTransactions->where('status', 'active')->count();
        $completedCount = $allTransactions->where('status', 'completed')->count();
        $lateCount      = $allTransactions->where('status', 'late')->count();
        $cancelledCount = $allTransactions->where('status', 'cancelled')->count();

        return view('frontend.transactions.index', [
            'allTransactions'    => $allTransactions,
            'borrowerTransactions' => $borrowerTransactions,
            'ownerTransactions'  => $ownerTransactions,
            'allCount'           => $allCount,
            'pendingCount'       => $pendingCount,
            'activeCount'        => $activeCount,
            'completedCount'     => $completedCount,
            'lateCount'          => $lateCount,
            'cancelledCount'     => $cancelledCount,
            'status'             => $status,
        ]);
    }

    /**
     * Show detailed view of a specific transaction
     */
    public function show(Transaction $transaction)
    {
        $this->authorizeTransactionView($transaction);

        $transaction->load([
            'item:id,title,description,user_id,lending_duration_days,pickup_location,image_path',
            'item.owner:id,name,email',
            'borrower:id,name,email',
            'ratings.rater:id,name',
            'penalties'
        ]);

        $isOwner   = Auth::id() === $transaction->owner_id;
        $isBorrower = Auth::id() === $transaction->borrower_id;

        $canRate   = false;
        $userRating = null;

        if (Rating::canRateTransaction($transaction)) {
            $canRate    = !Rating::userHasRatedTransaction(Auth::user(), $transaction);
            $userRating = Rating::where('transaction_id', $transaction->id)
                ->where('rater_id', Auth::id())
                ->first();
        }

        $timeline = $this->getTransactionTimeline($transaction);

        return view('frontend.transactions.show', compact(
            'transaction',
            'isOwner',
            'isBorrower',
            'canRate',
            'userRating',
            'timeline'
        ));
    }

    /**
     * Update transaction status via action param
     */
    public function update(Request $request, Transaction $transaction)
    {
        $user    = Auth::user();
        $isOwner = $user->id === $transaction->owner_id;

        if ($transaction->status === 'pending' && !$isOwner) {
            abort(403, 'Only item owner can approve requests.');
        }

        $action = $request->get('action');

        return match($action) {
            'approve'        => $this->approveTransaction($transaction),
            'reject'         => $this->rejectTransaction($transaction),
            'mark-returned'  => $this->markAsReturned($transaction),
            'mark-completed' => $this->markAsCompleted($transaction),
            'cancel'         => $this->cancelTransaction($transaction),
            default          => back()->with('error', 'Invalid action.'),
        };
    }

    /**
     * Approve pending transaction (owner only)
     */
    private function approveTransaction(Transaction $transaction)
    {
        if ($transaction->status !== 'pending') {
            return back()->with('error', 'Transaction is not pending.');
        }

        $transaction->markAsActive();
        $transaction->item->markAsBorrowed();

        return back()->with('success', 'Transaction approved! Item marked as borrowed.');
    }

    /**
     * Reject pending transaction (owner only)
     */
    private function rejectTransaction(Transaction $transaction)
    {
        if ($transaction->status !== 'pending') {
            return back()->with('error', 'Transaction is not pending.');
        }

        $transaction->markAsCancelled();
        $transaction->item->markAsAvailable();

        return back()->with('success', 'Request rejected. Item is available again.');
    }

    /**
     * Mark item as returned (borrower initiated).
     *
     * FIX: return_date is recorded before markAsLate() so it is not lost
     *      when the owner later confirms via markAsCompleted().
     */
    private function markAsReturned(Transaction $transaction)
    {
        if (Auth::id() !== $transaction->borrower_id) {
            abort(403, 'Only borrower can mark item as returned.');
        }

        if ($transaction->status !== 'active') {
            return back()->with('error', 'Transaction is not active.');
        }

        if ($transaction->isOverdue()) {
            // Record actual return date before status change
            $transaction->update(['return_date' => now()->toDateString()]);

            $penalty = $this->createPenaltyForTransaction($transaction);
            $transaction->markAsLate();

            return back()->with('warning', "Item returned late! Penalty: ₹{$penalty->amount} for {$penalty->days_late} days.");
        }

        $transaction->markAsCompleted();

        return back()->with('success', 'Item marked as returned. You can now rate the owner.');
    }

    /**
     * Mark transaction as completed (owner confirmation after return).
     *
     * FIX: item is marked available here. return_date is NOT set here —
     *      Transaction::markAsCompleted() only sets it if not already present.
     */
    private function markAsCompleted(Transaction $transaction)
    {
        if (Auth::id() !== $transaction->owner_id) {
            abort(403, 'Only owner can confirm return.');
        }

        if (!in_array($transaction->status, ['active', 'late'])) {
            return back()->with('error', 'Transaction cannot be marked as completed.');
        }

        $transaction->markAsCompleted();
        $transaction->item->markAsAvailable();

        return back()->with('success', 'Transaction completed. You can now rate the borrower.');
    }

    /**
     * Cancel transaction.
     *
     * FIX: borrowers can only cancel PENDING transactions.
     *      Only the owner can cancel an ACTIVE transaction
     *      (e.g. item not picked up, dispute, etc).
     */
    private function cancelTransaction(Transaction $transaction)
    {
        $user       = Auth::user();
        $isOwner    = $user->id === $transaction->owner_id;
        $isBorrower = $user->id === $transaction->borrower_id;

        if (!$isOwner && !$isBorrower) {
            abort(403, 'Unauthorized to cancel this transaction.');
        }

        if (!in_array($transaction->status, ['pending', 'active'])) {
            return back()->with('error', 'Cannot cancel completed transaction.');
        }

        // Borrowers can only cancel while still pending
        if ($isBorrower && !$isOwner && $transaction->status === 'active') {
            return back()->with('error', 'Cannot cancel an active transaction. Contact the owner.');
        }

        $transaction->markAsCancelled();
        $transaction->item->markAsAvailable();

        return back()->with('success', 'Transaction cancelled.');
    }

    /**
     * View all penalties for a transaction
     */
    public function penalties(Transaction $transaction)
    {
        $this->authorizeTransactionView($transaction);

        $penalties = $transaction->penalties()->get();

        return view('frontend.transactions.penalties', compact('transaction', 'penalties'));
    }

    /**
     * Pay penalty (borrower only)
     */
    public function payPenalty(Request $request, Penalty $penalty)
    {
        $transaction = $penalty->transaction;

        if (Auth::id() !== $transaction->borrower_id) {
            abort(403, 'Unauthorized.');
        }

        if (!$penalty->isPending()) {
            return back()->with('error', 'Penalty is not pending.');
        }

        // TODO: Integrate with payment gateway
        $penalty->markAsPaid();

        return back()->with('success', "Penalty of ₹{$penalty->amount} paid successfully!");
    }

    /**
     * Request penalty waiver (borrower).
     *
     * FIX: previously this did nothing (re-saved 'pending' status).
     *      Now it stores the reason and sets status to 'waiver_requested'
     *      so the uni admin can see and act on it.
     *      Requires 'waiver_reason' and 'waiver_requested' status in penalties table/model.
     */
    public function requestWaiver(Request $request, Penalty $penalty)
    {
        $transaction = $penalty->transaction;

        if (Auth::id() !== $transaction->borrower_id) {
            abort(403, 'Unauthorized.');
        }

        if (!$penalty->isPending()) {
            return back()->with('error', 'Can only request waiver on a pending penalty.');
        }

        $validated = $request->validate([
            'reason' => 'required|string|min:10|max:500'
        ]);

        $penalty->update([
            'status'        => 'waiver_requested',
            'waiver_reason' => $validated['reason'],
        ]);

        return back()->with('success', 'Waiver request submitted for admin review.');
    }

    /**
     * Get borrowing history (completed transactions)
     */
    public function borrowingHistory()
    {
        $history = Auth::user()
            ->transactionsAsBorrower()
            ->where('status', 'completed')
            ->with(['item:id,title', 'item.owner:id,name', 'ratings'])
            ->latest()
            ->paginate(10);

        return view('frontend.transactions.borrowing-history', compact('history'));
    }

    /**
     * Get lending history — FIX: use owner_id directly
     */
    public function lendingHistory()
    {
        $history = Transaction::where('owner_id', Auth::id())
            ->where('status', 'completed')
            ->with(['item:id,title', 'borrower:id,name', 'ratings'])
            ->latest()
            ->paginate(10);

        return view('frontend.transactions.lending-history', compact('history'));
    }

    /**
     * Get dashboard stats
     */
    public function stats()
    {
        $user = Auth::user();

        $stats = [
            'active_borrowing'      => $user->transactionsAsBorrower()->where('status', 'active')->count(),
            'active_lending'        => Transaction::where('owner_id', $user->id)->where('status', 'active')->count(),
            'completed_transactions' => $user->transactionsAsBorrower()->where('status', 'completed')->count(),
            'pending_penalties'     => Penalty::borrowerTotalPending($user),
            'average_rating'        => $user->averageRating(),
            'overdue_items'         => $user->hasOverdueItems(),
        ];

        return response()->json($stats);
    }

    // ========================================
    // Helper Methods
    // ========================================

    /**
     * Authorize user can view transaction
     */
    private function authorizeTransactionView(Transaction $transaction): void
    {
        $user       = Auth::user();
        $isOwner    = $user->id === $transaction->owner_id;
        $isBorrower = $user->id === $transaction->borrower_id;

        if (!$isOwner && !$isBorrower) {
            abort(403, 'Unauthorized to view this transaction.');
        }
    }

    /**
     * Create a penalty record for an overdue transaction.
     * Shared with ItemController to avoid duplicate logic.
     */
    private function createPenaltyForTransaction(Transaction $transaction): Penalty
    {
        $daysLate      = $transaction->daysOverdue();
        $penaltyAmount = Penalty::calculateAmount($daysLate);

        return Penalty::create([
            'transaction_id' => $transaction->id,
            'days_late'      => $daysLate,
            'amount'         => $penaltyAmount,
            'status'         => 'pending',
        ]);
    }

    /**
     * Get transaction timeline events
     */
    private function getTransactionTimeline(Transaction $transaction): array
    {
        $events = [];

        $events[] = [
            'date'        => $transaction->created_at,
            'title'       => 'Request Created',
            'description' => "{$transaction->borrower->name} requested this item",
            'icon'        => 'request',
        ];

        if ($transaction->status !== 'pending') {
            $events[] = [
                'date'        => $transaction->updated_at,
                'title'       => 'Request Approved',
                'description' => 'Owner approved the request',
                'icon'        => 'approved',
            ];

            if ($transaction->start_date) {
                $events[] = [
                    'date'        => $transaction->start_date,
                    'title'       => 'Transaction Started',
                    'description' => $transaction->type === 'lend'
                        ? "Due: {$transaction->due_date?->format('M d, Y')}"
                        : 'Item purchased',
                    'icon'        => 'start',
                ];
            }
        }

        if ($transaction->return_date) {
            $events[] = [
                'date'        => $transaction->return_date,
                'title'       => 'Item Returned',
                'description' => in_array($transaction->status, ['late'])
                    ? 'Returned late'
                    : 'Returned on time',
                'icon'        => in_array($transaction->status, ['late']) ? 'late' : 'returned',
            ];
        }

        if (in_array($transaction->status, ['completed', 'late'])) {
            $events[] = [
                'date'        => $transaction->updated_at,
                'title'       => 'Transaction Completed',
                'description' => 'Both parties can now rate each other',
                'icon'        => 'completed',
            ];
        }

        return $events;
    }
}
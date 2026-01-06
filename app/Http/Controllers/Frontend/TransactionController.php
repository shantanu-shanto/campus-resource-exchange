<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Item;
use App\Models\Penalty;
use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TransactionController extends Controller
{
    /**
     * Display all transactions for authenticated user
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Get transactions where user is borrower
        $borrowerTransactions = Transaction::where('borrower_id', $user->id)
            ->with(['item:id,title,user_id', 'item.owner:id,name,email', 'ratings'])
            ->latest()
            ->get();

        // Get transactions where user is owner (lender)
        $ownerTransactions = Transaction::whereHas('item', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })
            ->with(['item:id,title,user_id', 'borrower:id,name,email', 'ratings'])
            ->latest()
            ->get();

        // Filter by status if requested
        $status = $request->get('status');
        if ($status) {
            $borrowerTransactions = $borrowerTransactions->where('status', $status);
            $ownerTransactions = $ownerTransactions->where('status', $status);
        }

        return view('frontend.transactions.index', compact(
            'borrowerTransactions',
            'ownerTransactions',
            'status'
        ));
    }

    /**
     * Show detailed view of a specific transaction
     */
    public function show(Transaction $transaction)
    {
        // Authorize user can view this transaction
        $this->authorizeTransactionView($transaction);

        $transaction->load([
            'item:id,title,description,user_id,lending_duration_days,pickup_location,image_path',
            'item.owner:id,name,email',
            'borrower:id,name,email',
            'ratings.rater:id,name',
            'penalties'
        ]);

        // Determine user role in transaction
        $isOwner = Auth::id() === $transaction->item->user_id;
        $isBorrower = Auth::id() === $transaction->borrower_id;

        // Check if user can rate this transaction
        $canRate = false;
        $userRating = null;
        
        if (Rating::canRateTransaction($transaction)) {
            $canRate = !Rating::userHasRatedTransaction(Auth::user(), $transaction);
            $userRating = Rating::where('transaction_id', $transaction->id)
                ->where('rater_id', Auth::id())
                ->first();
        }

        // Get transaction timeline
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
     * Update transaction status
     */
    public function update(Request $request, Transaction $transaction)
    {
        $user = Auth::user();
        $isOwner = $user->id === $transaction->item->user_id;

        // Only owner can approve/reject pending transactions
        if ($transaction->status === 'pending' && !$isOwner) {
            abort(403, 'Only item owner can approve requests.');
        }

        $action = $request->get('action');

        switch ($action) {
            case 'approve':
                return $this->approveTransaction($transaction);
            case 'reject':
                return $this->rejectTransaction($transaction);
            case 'mark-returned':
                return $this->markAsReturned($transaction);
            case 'mark-completed':
                return $this->markAsCompleted($transaction);
            case 'cancel':
                return $this->cancelTransaction($transaction);
            default:
                return back()->with('error', 'Invalid action.');
        }
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

        // Send notification to borrower
        // TODO: Implement notifications

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
     * Mark item as returned (borrower initiated)
     */
    private function markAsReturned(Transaction $transaction)
    {
        // Only borrower can mark as returned
        if (Auth::id() !== $transaction->borrower_id) {
            abort(403, 'Only borrower can mark item as returned.');
        }

        if ($transaction->status !== 'active') {
            return back()->with('error', 'Transaction is not active.');
        }

        // Check if late and create penalty
        if ($transaction->isOverdue()) {
            $daysLate = $transaction->daysOverdue();
            $penaltyAmount = Penalty::calculateAmount($daysLate);

            Penalty::create([
                'transaction_id' => $transaction->id,
                'days_late' => $daysLate,
                'amount' => $penaltyAmount,
                'status' => 'pending',
            ]);

            $transaction->markAsLate();

            return back()->with('warning', "Item returned late! Penalty: ৳{$penaltyAmount} for {$daysLate} days.");
        } else {
            $transaction->markAsCompleted();
            return back()->with('success', 'Item marked as returned. You can now rate the owner.');
        }
    }

    /**
     * Mark transaction as completed (owner confirmation after return)
     */
    private function markAsCompleted(Transaction $transaction)
    {
        // Only owner can confirm return
        $isOwner = Auth::id() === $transaction->item->user_id;
        if (!$isOwner) {
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
     * Cancel transaction
     */
    private function cancelTransaction(Transaction $transaction)
    {
        $user = Auth::user();
        $isOwner = $user->id === $transaction->item->user_id;
        $isBorrower = $user->id === $transaction->borrower_id;

        // Only owner or borrower can cancel
        if (!$isOwner && !$isBorrower) {
            abort(403, 'Unauthorized to cancel this transaction.');
        }

        // Can only cancel if pending or active
        if (!in_array($transaction->status, ['pending', 'active'])) {
            return back()->with('error', 'Cannot cancel completed transaction.');
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

        // Only borrower can pay their penalties
        if (Auth::id() !== $transaction->borrower_id) {
            abort(403, 'Unauthorized.');
        }

        if (!$penalty->isPending()) {
            return back()->with('error', 'Penalty is not pending.');
        }

        // TODO: Integrate with payment gateway
        $penalty->markAsPaid();

        return back()->with('success', "Penalty of ৳{$penalty->amount} paid successfully!");
    }

    /**
     * Request penalty waiver (borrower)
     */
    public function requestWaiver(Request $request, Penalty $penalty)
    {
        $transaction = $penalty->transaction;

        // Only borrower can request waiver
        if (Auth::id() !== $transaction->borrower_id) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'reason' => 'required|string|min:10|max:500'
        ]);

        // TODO: Create waiver request and notify admin
        // For now, just mark for review
        $penalty->update(['status' => 'pending']);

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
     * Get lending history (completed transactions where user is owner)
     */
    public function lendingHistory()
    {
        $history = Transaction::whereHas('item', function($q) {
            $q->where('user_id', Auth::id());
        })
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
            'active_borrowing' => $user->transactionsAsBorrower()
                ->where('status', 'active')->count(),
            'active_lending' => Transaction::whereHas('item', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->where('status', 'active')->count(),
            'completed_transactions' => $user->transactionsAsBorrower()
                ->where('status', 'completed')->count(),
            'pending_penalties' => Penalty::borrowerTotalPending($user),
            'average_rating' => $user->averageRating(),
            'overdue_items' => $user->hasOverdueItems(),
        ];

        return response()->json($stats);
    }

    // ========================================
    // Helper Methods
    // ========================================

    /**
     * Authorize user can view transaction
     */
    private function authorizeTransactionView(Transaction $transaction)
    {
        $user = Auth::user();
        $isOwner = $user->id === $transaction->item->user_id;
        $isBorrower = $user->id === $transaction->borrower_id;

        if (!$isOwner && !$isBorrower) {
            abort(403, 'Unauthorized to view this transaction.');
        }
    }

    /**
     * Get transaction timeline events
     */
    private function getTransactionTimeline(Transaction $transaction): array
    {
        $events = [];

        $events[] = [
            'date' => $transaction->created_at,
            'title' => 'Request Created',
            'description' => "{$transaction->borrower->name} requested this item",
            'icon' => 'request'
        ];

        if ($transaction->status !== 'pending') {
            $events[] = [
                'date' => $transaction->updated_at,
                'title' => 'Request Approved',
                'description' => 'Owner approved the request',
                'icon' => 'approved'
            ];

            if ($transaction->start_date) {
                $events[] = [
                    'date' => $transaction->start_date,
                    'title' => 'Transaction Started',
                    'description' => $transaction->type === 'lend' 
                        ? "Due: {$transaction->due_date?->format('M d, Y')}"
                        : 'Item purchased',
                    'icon' => 'start'
                ];
            }
        }

        if ($transaction->return_date) {
            $events[] = [
                'date' => $transaction->return_date,
                'title' => 'Item Returned',
                'description' => $transaction->isOverdue() ? 'Returned late' : 'Returned on time',
                'icon' => $transaction->isOverdue() ? 'late' : 'returned'
            ];
        }

        if (in_array($transaction->status, ['completed', 'late'])) {
            $events[] = [
                'date' => $transaction->updated_at,
                'title' => 'Transaction Completed',
                'description' => 'Both parties can now rate each other',
                'icon' => 'completed'
            ];
        }

        return $events;
    }
}

<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\HandoverVerification;
use App\Models\Penalty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class QrHandoverController extends Controller
{
    // ========================================
    // Generate QR
    // ========================================

    /**
     * Generate a QR handover verification for a transaction.
     *
     * WHO CALLS THIS:
     * - For pickup: the OWNER generates it after approving the request
     * - For return: the BORROWER generates it when returning the item
     *
     * WHAT IT DOES:
     * 1. Validates the transaction is in the correct state
     * 2. Expires any previously pending verification of the same type
     * 3. Creates a new HandoverVerification record with a 15-minute token
     * 4. Returns a view containing the scan URL — the Blade view renders the QR
     */
    public function generate(Request $request, Transaction $transaction)
    {
        $user = Auth::user();

        // Determine what type of handover this is based on transaction status
        // and who is calling — owner generates pickup QR, borrower generates return QR
        $type = $this->resolveHandoverType($transaction, $user->id);

        if (!$type) {
            return back()->with('error', 'This transaction is not in a state that requires a handover scan.');
        }

        // Expire any existing pending verification of the same type
        // so only one active token exists per transaction per type
        HandoverVerification::where('transaction_id', $transaction->id)
            ->where('type', $type)
            ->where('status', 'pending')
            ->update(['status' => 'expired']);

        // Generate the token and create the verification record
        $token = HandoverVerification::generateToken();

        $verification = HandoverVerification::create([
            'transaction_id' => $transaction->id,
            'type'           => $type,
            'token'          => $token,
            'expires_at'     => HandoverVerification::expiresAt(),
            'status'         => 'pending',
        ]);

        // Transition transaction to the appropriate awaiting status
        if ($type === 'pickup') {
            $transaction->markAsAwaitingHandover();
        } else {
            $transaction->markAsAwaitingReturn();
        }

        // Build the scan URL — this is what gets encoded into the QR code
        // The borrower's camera opens this URL when they scan
        $scanUrl = route('frontend.handover.scan', ['token' => $token]);

        return view('frontend.handover.generate', [
            'transaction'  => $transaction,
            'verification' => $verification,
            'scanUrl'      => $scanUrl,
            'type'         => $type,
        ]);
    }

    // ========================================
    // Scan / Confirm
    // ========================================

    /**
     * Show the confirmation page when a user scans the QR code.
     *
     * WHO CALLS THIS:
     * - The borrower scans the QR, their camera opens this URL
     * - The owner can also visit it to confirm their side
     *
     * WHAT IT DOES:
     * 1. Finds the verification by token
     * 2. Checks it is not expired or already completed
     * 3. Shows a confirmation page with item and transaction details
     * 4. Does NOT confirm yet — user must tap the confirm button
     */
    public function scan(string $token)
    {
        $verification = HandoverVerification::where('token', $token)
            ->with(['transaction.item', 'transaction.owner', 'transaction.borrower'])
            ->firstOrFail();

        // Check expiry first
        if ($verification->isExpired()) {
            $verification->markAsExpired();
            return view('frontend.handover.expired', [
                'verification' => $verification,
            ]);
        }

        // Already completed — both parties already confirmed
        if ($verification->isCompleted()) {
            return view('frontend.handover.already-confirmed', [
                'verification' => $verification,
                'transaction'  => $verification->transaction,
            ]);
        }

        $user        = Auth::user();
        $transaction = $verification->transaction;

        // Determine if this user is the owner or borrower
        $isOwner    = $user->id === $transaction->owner_id;
        $isBorrower = $user->id === $transaction->borrower_id;

        // Must be a party to this transaction
        if (!$isOwner && !$isBorrower) {
            abort(403, 'You are not a party to this transaction.');
        }

        // Check if this user has already confirmed
        $alreadyConfirmed = ($isOwner && $verification->ownerHasConfirmed())
            || ($isBorrower && $verification->borrowerHasConfirmed());

        return view('frontend.handover.scan', [
            'verification'     => $verification,
            'transaction'      => $transaction,
            'isOwner'          => $isOwner,
            'isBorrower'       => $isBorrower,
            'alreadyConfirmed' => $alreadyConfirmed,
        ]);
    }

    /**
     * Process the confirmation tap from the scan page.
     *
     * WHO CALLS THIS:
     * - Either party after viewing the scan confirmation page and tapping confirm
     *
     * WHAT IT DOES:
     * 1. Re-validates the token is still valid
     * 2. Records this party's confirmation timestamp
     * 3. If both confirmed — transitions the transaction and item status
     * 4. Redirects to transaction page with appropriate message
     */
    public function confirm(Request $request, string $token)
    {
        $verification = HandoverVerification::where('token', $token)
            ->with(['transaction.item'])
            ->firstOrFail();

        // Re-check expiry at confirm time
        if ($verification->isExpired()) {
            $verification->markAsExpired();
            return redirect()->route('frontend.transactions.show', $verification->transaction)
                ->with('error', 'QR token expired. Please generate a new one.');
        }

        if ($verification->isCompleted()) {
            return redirect()->route('frontend.transactions.show', $verification->transaction)
                ->with('info', 'Handover already confirmed by both parties.');
        }

        $user        = Auth::user();
        $transaction = $verification->transaction;

        $isOwner    = $user->id === $transaction->owner_id;
        $isBorrower = $user->id === $transaction->borrower_id;

        if (!$isOwner && !$isBorrower) {
            abort(403, 'You are not a party to this transaction.');
        }

        // Record this party's confirmation
        if ($isOwner && !$verification->ownerHasConfirmed()) {
            $verification->confirmOwner();
        } elseif ($isBorrower && !$verification->borrowerHasConfirmed()) {
            $verification->confirmBorrower();
        }

        // Refresh from DB to get the latest confirmation state
        $verification->refresh();

        // If both parties have now confirmed — transition the transaction
        if ($verification->bothConfirmed()) {
            $this->transitionAfterHandover($verification);

            return redirect()->route('frontend.transactions.show', $transaction)
                ->with('success', $this->getSuccessMessage($verification->type));
        }

        // Only one party confirmed so far — tell them to wait
        return redirect()->route('frontend.transactions.show', $transaction)
            ->with('info', 'Your confirmation recorded. Waiting for the other party to scan.');
    }

    // ========================================
    // Status Polling
    // ========================================

    /**
     * JSON endpoint polled by the generate page every few seconds.
     *
     * The owner's generate page polls this while showing the QR,
     * so it can automatically update when the borrower confirms —
     * no manual page refresh needed.
     */
    public function checkStatus(Transaction $transaction)
    {
        $user = Auth::user();

        $isOwner    = $user->id === $transaction->owner_id;
        $isBorrower = $user->id === $transaction->borrower_id;

        if (!$isOwner && !$isBorrower) {
            abort(403);
        }

        // Get the most recent pending verification for this transaction
        $verification = HandoverVerification::where('transaction_id', $transaction->id)
            ->where('status', 'pending')
            ->latest()
            ->first();

        if (!$verification) {
            // No pending verification — check if there's a completed one
            $completed = HandoverVerification::where('transaction_id', $transaction->id)
                ->where('status', 'completed')
                ->latest()
                ->first();

            return response()->json([
                'status'          => $completed ? 'completed' : 'none',
                'both_confirmed'  => (bool) $completed,
                'transaction_status' => $transaction->fresh()->status,
            ]);
        }

        // Check if it expired since the last poll
        if ($verification->isExpired() && $verification->status === 'pending') {
            $verification->markAsExpired();
        }

        return response()->json(
            array_merge(
                $verification->getConfirmationSummary(),
                ['transaction_status' => $transaction->fresh()->status]
            )
        );
    }

    // ========================================
    // Private Helpers
    // ========================================

    /**
     * Determine what type of handover QR to generate
     * based on transaction status and who is requesting it.
     *
     * pickup → owner generates when transaction is pending
     * return → borrower generates when transaction is active
     *
     * Returns null if the transaction is not in a valid state
     * for either type of handover.
     */
    private function resolveHandoverType(Transaction $transaction, int $userId): ?string
    {
        $isOwner    = $userId === $transaction->owner_id;
        $isBorrower = $userId === $transaction->borrower_id;

        // Pickup: owner generates when transaction is pending
        if ($isOwner && $transaction->status === 'pending') {
            return 'pickup';
        }

        // Return: borrower generates when transaction is active
        if ($isBorrower && $transaction->status === 'active') {
            return 'return';
        }

        return null;
    }

    /**
     * Transition the transaction and item to the correct next status
     * after both parties have confirmed the handover.
     *
     * pickup confirmed → transaction becomes active, item becomes borrowed
     * return confirmed → transaction becomes completed or late, item becomes available
     */
    private function transitionAfterHandover(HandoverVerification $verification): void
    {
        $verification->markAsCompleted();

        $transaction = $verification->transaction;
        $item        = $transaction->item;

        if ($verification->type === 'pickup') {
            // Both confirmed the physical handover — item is now with the borrower
            $transaction->markAsActive();
            $item->markAsBorrowed();

        } elseif ($verification->type === 'return') {
            // Both confirmed the physical return — check if it is overdue
            if ($transaction->isOverdue()) {
                $transaction->update(['return_date' => now()->toDateString()]);
                $this->createPenaltyForTransaction($transaction);
                $transaction->markAsLate();
            } else {
                $transaction->markAsCompleted();
            }

            $item->markAsAvailable();
        }
    }

    /**
     * Create a penalty record for an overdue return.
     * Mirrors the same logic in ItemController and TransactionController.
     * Will be moved to a shared service class in a future refactor.
     */
    private function createPenaltyForTransaction(Transaction $transaction): void
    {
        $daysLate      = $transaction->daysOverdue();
        $penaltyAmount = Penalty::calculateAmount($daysLate);

        Penalty::create([
            'transaction_id' => $transaction->id,
            'days_late'      => $daysLate,
            'amount'         => $penaltyAmount,
            'status'         => 'pending',
        ]);
    }

    /**
     * Human-readable success message based on handover type
     */
    private function getSuccessMessage(string $type): string
    {
        return match($type) {
            'pickup' => 'Handover confirmed by both parties! The transaction is now active.',
            'return' => 'Return confirmed by both parties! The transaction is now complete.',
            default  => 'Handover confirmed successfully.',
        };
    }
}
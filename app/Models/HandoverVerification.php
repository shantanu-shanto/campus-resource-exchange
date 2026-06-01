<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Str;

class HandoverVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'type',
        'token',
        'owner_confirmed_at',
        'borrower_confirmed_at',
        'expires_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'owner_confirmed_at'    => 'datetime',
            'borrower_confirmed_at' => 'datetime',
            'expires_at'            => 'datetime',
        ];
    }

    // ========================================
    // Relationships
    // ========================================

    /**
     * The transaction this verification belongs to
     */
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    // ========================================
    // Scopes
    // ========================================

    /**
     * Only verifications still waiting for both scans
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Only verifications whose 15-minute window has passed
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    /**
     * Only completed verifications (both parties confirmed)
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Pending verifications that have passed their expiry time
     * Used by the scheduled job to bulk-expire tokens
     */
    public function scopeExpiredAndNotMarked($query)
    {
        return $query->where('status', 'pending')
                     ->where('expires_at', '<', Carbon::now());
    }

    // ========================================
    // Status Check Helpers
    // ========================================

    /**
     * Has the 15-minute window passed?
     */
    public function isExpired(): bool
    {
        return $this->expires_at->lt(Carbon::now())
            || $this->status === 'expired';
    }

    /**
     * Have both parties confirmed?
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Is this verification still waiting?
     */
    public function isPending(): bool
    {
        return $this->status === 'pending' && !$this->isExpired();
    }

    /**
     * Has the owner scanned/confirmed yet?
     */
    public function ownerHasConfirmed(): bool
    {
        return !is_null($this->owner_confirmed_at);
    }

    /**
     * Has the borrower scanned/confirmed yet?
     */
    public function borrowerHasConfirmed(): bool
    {
        return !is_null($this->borrower_confirmed_at);
    }

    /**
     * Have BOTH parties confirmed?
     * This is the trigger condition for transitioning the transaction status.
     */
    public function bothConfirmed(): bool
    {
        return $this->ownerHasConfirmed() && $this->borrowerHasConfirmed();
    }

    /**
     * How many seconds remain before this token expires?
     * Returns 0 if already expired — never negative.
     */
    public function secondsRemaining(): int
    {
        if ($this->isExpired()) return 0;
        return max(0, (int) Carbon::now()->diffInSeconds($this->expires_at, false));
    }

    // ========================================
    // Status Transition Helpers
    // ========================================

    /**
     * Mark this verification as completed
     * Called internally when bothConfirmed() returns true
     */
    public function markAsCompleted(): bool
    {
        return $this->update(['status' => 'completed']);
    }

    /**
     * Mark this verification as expired
     * Called by the scan endpoint or a scheduled cleanup job
     */
    public function markAsExpired(): bool
    {
        return $this->update(['status' => 'expired']);
    }

    /**
     * Record owner confirmation timestamp
     */
    public function confirmOwner(): bool
    {
        return $this->update(['owner_confirmed_at' => Carbon::now()]);
    }

    /**
     * Record borrower confirmation timestamp
     */
    public function confirmBorrower(): bool
    {
        return $this->update(['borrower_confirmed_at' => Carbon::now()]);
    }

    // ========================================
    // Factory / Generation Helpers
    // ========================================

    /**
     * Generate a secure unique token for the QR payload.
     * Called by QrHandoverController::generate() before creating the record.
     * Static so it can be called without an instance.
     */
    public static function generateToken(): string
    {
        // Str::random(64) gives a cryptographically random alphanumeric string
        // Loop ensures uniqueness against the DB (collision extremely unlikely)
        do {
            $token = Str::random(64);
        } while (self::where('token', $token)->exists());

        return $token;
    }

    /**
     * Standard expiry time — now + 15 minutes.
     * Centralised here so it's easy to change in one place.
     */
    public static function expiresAt(): Carbon
    {
        return Carbon::now()->addMinutes(15);
    }

    // ========================================
    // Display Helpers
    // ========================================

    public function getTypeLabel(): string
    {
        return match($this->type) {
            'pickup' => 'Item Pickup',
            'return' => 'Item Return',
            default  => 'Unknown',
        };
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            'pending'   => 'Waiting for Scans',
            'completed' => 'Handover Confirmed',
            'expired'   => 'Token Expired',
            default     => 'Unknown',
        };
    }

    public function getStatusBadgeColor(): string
    {
        return match($this->status) {
            'pending'   => 'warning',
            'completed' => 'success',
            'expired'   => 'danger',
            default     => 'secondary',
        };
    }

    /**
     * Summary of who has confirmed so far —
     * useful for the polling status endpoint response.
     */
    public function getConfirmationSummary(): array
    {
        return [
            'owner_confirmed'    => $this->ownerHasConfirmed(),
            'borrower_confirmed' => $this->borrowerHasConfirmed(),
            'both_confirmed'     => $this->bothConfirmed(),
            'seconds_remaining'  => $this->secondsRemaining(),
            'status'             => $this->status,
        ];
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'owner_id',     // direct FK — the lender/seller
        'borrower_id',
        'type',         // share | lend | sell
        'start_date',
        'due_date',
        'return_date',
        'deposit_amount',
        'final_price',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'start_date'     => 'date',
            'due_date'       => 'date',
            'return_date'    => 'date',
            'deposit_amount' => 'decimal:2',
            'final_price'    => 'decimal:2',
        ];
    }

    // ========================================
    // Relationships
    // ========================================

    /**
     * The item being transacted
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * The owner/lender/seller — direct FK, no join needed
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * The borrower/buyer
     */
    public function borrower()
    {
        return $this->belongsTo(User::class, 'borrower_id');
    }

    /**
     * Ratings given for this transaction
     */
    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    /**
     * Penalties linked to this transaction
     */
    public function penalties()
    {
        return $this->hasMany(Penalty::class);
    }

    /**
     * QR handover verifications for this transaction
     * One for pickup, one for return — max two records per transaction
     */
    public function handoverVerifications()
    {
        return $this->hasMany(HandoverVerification::class);
    }

    /**
     * The active (pending) handover verification if one exists
     */
    public function activeHandoverVerification()
    {
        return $this->hasOne(HandoverVerification::class)
                    ->where('status', 'pending')
                    ->where('expires_at', '>', now());
    }

    /**
     * Get the pickup verification specifically
     */
    public function pickupVerification()
    {
        return $this->hasOne(HandoverVerification::class)
                    ->where('type', 'pickup')
                    ->latest();
    }

    /**
     * Get the return verification specifically
     */
    public function returnVerification()
    {
        return $this->hasOne(HandoverVerification::class)
                    ->where('type', 'return')
                    ->latest();
    }

    // ========================================
    // Scopes
    // ========================================

    /**
     * Updated to include the two new transitional statuses
     * so dashboard counts and queries treat them as "in progress"
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            'pending',
            'awaiting_handover',
            'active',
            'awaiting_return',
        ]);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeLate($query)
    {
        return $query->where('status', 'late');
    }

    public function scopeOverdue($query)
    {
        return $query->where('type', 'lend')
            ->where('status', 'active')
            ->where('due_date', '<', Carbon::today());
    }

    public function scopeByBorrower($query, User $user)
    {
        return $query->where('borrower_id', $user->id);
    }

    public function scopeByOwner($query, User $user)
    {
        return $query->where('owner_id', $user->id);
    }

    /**
     * Scope: Transactions within a specific university
     * Used by UniAdmin to scope their campus data
     */
    public function scopeForUniversity($query, int $universityId)
    {
        return $query->whereHas('item', function ($q) use ($universityId) {
            $q->where('university_id', $universityId);
        });
    }

    // ========================================
    // Type Helpers
    // ========================================

    public function isLending(): bool
    {
        return $this->type === 'lend';
    }

    public function isSelling(): bool
    {
        return $this->type === 'sell';
    }

    public function isSharing(): bool
    {
        return $this->type === 'share';
    }

    // ========================================
    // Status Helpers
    // ========================================

    public function isOverdue(): bool
    {
        return $this->isLending()
            && $this->status === 'active'
            && $this->due_date
            && $this->due_date->lt(Carbon::today());
    }

    public function daysOverdue(): int
    {
        if (!$this->isOverdue()) return 0;
        return Carbon::today()->diffInDays($this->due_date);
    }

    public function markAsActive(): bool
    {
        return $this->update([
            'status'     => 'active',
            'start_date' => Carbon::today(),
        ]);
    }

    /**
     * Mark transaction as completed.
     *
     * Only sets return_date if it hasn't been set yet.
     * This preserves the actual return date recorded when the borrower
     * marked the item as returned — the owner's confirmation call should
     * not overwrite it with today's date.
     */
    public function markAsCompleted(): bool
    {
        $data = ['status' => 'completed'];

        if (is_null($this->return_date)) {
            $data['return_date'] = Carbon::today();
        }

        return $this->update($data);
    }

    public function markAsLate(): bool
    {
        return $this->update(['status' => 'late']);
    }

    public function markAsCancelled(): bool
    {
        return $this->update(['status' => 'cancelled']);
    }

    /**
     * Owner has generated QR — waiting for both parties to scan (pickup stage)
     * Sits between: pending → awaiting_handover → active
     */
    public function markAsAwaitingHandover(): bool
    {
        return $this->update(['status' => 'awaiting_handover']);
    }

    /**
     * Borrower has initiated return — waiting for both parties to scan (return stage)
     * Sits between: active → awaiting_return → completed/late
     */
    public function markAsAwaitingReturn(): bool
    {
        return $this->update(['status' => 'awaiting_return']);
    }

    public function canBeRated(): bool
    {
        return in_array($this->status, ['completed', 'late']);
    }

    // ========================================
    // Calculation Helpers
    // ========================================

    public function calculateDueDate(): Carbon
    {
        if (!$this->isLending()) {
            throw new \Exception('Due date only applies to lending transactions.');
        }
        return Carbon::today()->addDays($this->item->lending_duration_days);
    }

    public function averageRating(): float
    {
        return $this->ratings()->avg('rating') ?? 0.0;
    }

    // ========================================
    // Display Helpers
    // ========================================

    public function getStatusLabel(): string
    {
        return match($this->status) {
            'pending'           => 'Pending',
            'awaiting_handover' => 'Awaiting Pickup Scan',
            'active'            => 'Active',
            'awaiting_return'   => 'Awaiting Return Scan',
            'completed'         => 'Completed',
            'late'              => 'Late',
            'cancelled'         => 'Cancelled',
            default             => 'Unknown',
        };
    }

    public function getStatusBadgeColor(): string
    {
        return match($this->status) {
            'pending'           => 'warning',
            'awaiting_handover' => 'primary',
            'active'            => 'info',
            'awaiting_return'   => 'primary',
            'completed'         => 'success',
            'late'              => 'danger',
            'cancelled'         => 'secondary',
            default             => 'secondary',
        };
    }

    public function getFormattedDepositAttribute(): string
    {
        return $this->deposit_amount ? '₹' . number_format($this->deposit_amount, 2) : 'N/A';
    }

    public function getFormattedPriceAttribute(): string
    {
        return $this->final_price ? '₹' . number_format($this->final_price, 2) : 'N/A';
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Penalty extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'days_late',
        'amount',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'days_late' => 'integer',
            'amount'    => 'decimal:2',
        ];
    }

    // ========================================
    // Relationships
    // ========================================

    /**
     * The transaction that incurred this penalty
     */
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * The borrower who owes this penalty — through transaction
     */
    public function borrower()
    {
        return $this->hasOneThrough(
            User::class,
            Transaction::class,
            'id',          // FK on transactions (transaction.id)
            'id',          // FK on users (user.id)
            'transaction_id', // local key on penalties
            'borrower_id'  // local key on transactions pointing to user
        );
    }

    /**
     * The item owner (lender) — through transaction
     */
    public function owner()
    {
        return $this->hasOneThrough(
            User::class,
            Transaction::class,
            'id',
            'id',
            'transaction_id',
            'owner_id'     // direct owner_id on transaction (our new column)
        );
    }

    /**
     * The item that was returned late — through transaction
     */
    public function item()
    {
        return $this->hasOneThrough(
            Item::class,
            Transaction::class,
            'id',
            'id',
            'transaction_id',
            'item_id'
        );
    }

    // ========================================
    // Scopes
    // ========================================

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeWaived($query)
    {
        return $query->where('status', 'waived');
    }

    public function scopeForBorrower($query, User $user)
    {
        return $query->whereHas('transaction', function ($q) use ($user) {
            $q->where('borrower_id', $user->id);
        });
    }

    public function scopeHighValue($query, float $minAmount = 100)
    {
        return $query->where('amount', '>=', $minAmount);
    }

    public function scopeRecent($query)
    {
        return $query->where('created_at', '>=', Carbon::now()->subDays(30));
    }

    /**
     * Scope: Penalties within a university (for uni admin scoping)
     */
    public function scopeForUniversity($query, int $universityId)
    {
        return $query->whereHas('transaction.item', function ($q) use ($universityId) {
            $q->where('university_id', $universityId);
        });
    }

    // ========================================
    // Status Helpers
    // ========================================

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isWaived(): bool
    {
        return $this->status === 'waived';
    }

    public function markAsPaid(): bool
    {
        return $this->update(['status' => 'paid']);
    }

    public function markAsWaived(): bool
    {
        return $this->update(['status' => 'waived']);
    }

    // ========================================
    // Calculation Helpers
    // ========================================

    /**
     * Calculate penalty amount — ৳50 per day late
     */
    public static function calculateAmount(int $daysLate, float $ratePerDay = 50.00): float
    {
        return $daysLate * $ratePerDay;
    }

    public static function borrowerTotalPending(User $borrower): float
    {
        return self::forBorrower($borrower)->pending()->sum('amount');
    }

    public static function borrowerHasPending(User $borrower): bool
    {
        return self::forBorrower($borrower)->pending()->exists();
    }

    public function isPaymentOverdue(): bool
    {
        if (!$this->isPending()) return false;
        return $this->transaction->due_date
            ? Carbon::parse($this->transaction->due_date)->addDays(7)->lt(Carbon::today())
            : false;
    }

    public function daysSinceIssued(): int
    {
        return Carbon::today()->diffInDays($this->created_at);
    }

    // ========================================
    // Display Helpers
    // ========================================

    public function getFormattedAmountAttribute(): string
    {
        return '৳' . number_format($this->amount, 2);
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            'pending' => 'Pending',
            'paid'    => 'Paid',
            'waived'  => 'Waived',
            default   => 'Unknown',
        };
    }

    public function getStatusBadgeColor(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'paid'    => 'success',
            'waived'  => 'info',
            default   => 'secondary',
        };
    }
}
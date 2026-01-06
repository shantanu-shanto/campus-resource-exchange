<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Penalty extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'transaction_id',
        'days_late',
        'amount',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'days_late' => 'integer',
            'amount' => 'decimal:2',
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
     * The borrower who owes this penalty
     */
    public function borrower()
    {
        return $this->belongsTo(User::class, 'borrower_id')->via('transaction');
    }

    /**
     * The item owner (lender)
     */
    public function lender()
    {
        return $this->belongsTo(User::class, 'user_id')->via('transaction.item');
    }

    /**
     * The item that was returned late
     */
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id')->via('transaction');
    }

    // ========================================
    // Scope Methods
    // ========================================

    /**
     * Scope: Pending penalties (unpaid)
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: Paid penalties
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope: Waived penalties
     */
    public function scopeWaived($query)
    {
        return $query->where('status', 'waived');
    }

    /**
     * Scope: Penalties for specific borrower
     */
    public function scopeForBorrower($query, User $user)
    {
        return $query->whereHas('transaction', function ($q) use ($user) {
            $q->where('borrower_id', $user->id);
        });
    }

    /**
     * Scope: High value penalties
     */
    public function scopeHighValue($query, $minAmount = 100)
    {
        return $query->where('amount', '>=', $minAmount);
    }

    /**
     * Scope: Recent penalties (last 30 days)
     */
    public function scopeRecent($query)
    {
        return $query->where('created_at', '>=', Carbon::now()->subDays(30));
    }

    // ========================================
    // Helper Methods
    // ========================================

    /**
     * Check if penalty is pending payment
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if penalty has been paid
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Check if penalty was waived
     */
    public function isWaived(): bool
    {
        return $this->status === 'waived';
    }

    /**
     * Mark penalty as paid
     */
    public function markAsPaid(): bool
    {
        return $this->update(['status' => 'paid']);
    }

    /**
     * Mark penalty as waived
     */
    public function markAsWaived(): bool
    {
        return $this->update(['status' => 'waived']);
    }

    /**
     * Calculate penalty amount based on days late
     * (e.g., ৳50 per day late)
     */
    public static function calculateAmount(int $daysLate): float
    {
        return $daysLate * 50.00; // ৳50 per day
    }

    /**
     * Get formatted penalty amount
     */
    public function getFormattedAmountAttribute(): string
    {
        return '৳' . number_format($this->amount, 2);
    }

    /**
     * Get status label for UI
     */
    public function getStatusLabel(): string
    {
        return match($this->status) {
            'pending' => 'Pending',
            'paid' => 'Paid',
            'waived' => 'Waived',
            default => 'Unknown'
        };
    }

    /**
     * Get status badge color
     */
    public function getStatusBadgeColor(): string
    {
        return match($this->status) {
            'pending' => 'orange',
            'paid' => 'green',
            'waived' => 'blue',
            default => 'gray'
        };
    }

    /**
     * Check if penalty is overdue for payment (e.g., 7 days after due date)
     */
    public function isPaymentOverdue(): bool
    {
        if (!$this->isPending()) {
            return false;
        }
        return $this->transaction->due_date
            ? Carbon::parse($this->transaction->due_date)->addDays(7)->lt(Carbon::today())
            : false;
    }

    /**
     * Get days since penalty was issued
     */
    public function daysSinceIssued(): int
    {
        return Carbon::today()->diffInDays($this->created_at);
    }

    /**
     * Get borrower total pending penalties
     */
    public static function borrowerTotalPending(User $borrower): float
    {
        return self::forBorrower($borrower)
            ->pending()
            ->sum('amount');
    }

    /**
     * Check if borrower has any pending penalties
     */
    public static function borrowerHasPending(User $borrower): bool
    {
        return self::forBorrower($borrower)->pending()->exists();
    }

    /**
     * Prevent borrower from creating new transactions if they have unpaid penalties
     */
    public static function borrowerCanCreateTransaction(User $borrower): bool
    {
        return !self::borrowerHasPending($borrower);
    }

    /**
     * Get display info for admin dashboard
     */
    public function getAdminDisplayAttribute(): string
    {
        $borrowerName = $this->borrower?->name ?? 'Unknown';
        $itemTitle = $this->item?->title ?? 'Unknown Item';
        $days = $this->days_late;
        
        return "{$this->formatted_amount} ({$days}d late) - {$borrowerName} for '{$itemTitle}'";
    }
}

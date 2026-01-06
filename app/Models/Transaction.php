<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Transaction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'item_id',
        'borrower_id',
        'type',
        'start_date',
        'due_date',
        'return_date',
        'deposit_amount',
        'final_price',
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
            'start_date' => 'date',
            'due_date' => 'date',
            'return_date' => 'date',
            'deposit_amount' => 'decimal:2',
            'final_price' => 'decimal:2',
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
     * The borrower/buyer user
     */
    public function borrower()
    {
        return $this->belongsTo(User::class, 'borrower_id');
    }

    /**
     * The owner (through item)
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id')->via('item');
    }

    /**
     * Ratings given for this transaction
     */
    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    /**
     * Penalties associated with this transaction
     */
    public function penalties()
    {
        return $this->hasMany(Penalty::class);
    }

    // ========================================
    // Scope Methods
    // ========================================

    /**
     * Scope: Active transactions (pending or active)
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'active']);
    }

    /**
     * Scope: Completed transactions
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope: Late transactions
     */
    public function scopeLate($query)
    {
        return $query->where('status', 'late');
    }

    /**
     * Scope: Overdue lending transactions
     */
    public function scopeOverdue($query)
    {
        return $query->where('type', 'lend')
            ->where('status', 'active')
            ->where('due_date', '<', Carbon::today());
    }

    /**
     * Scope: By borrower
     */
    public function scopeByBorrower($query, User $user)
    {
        return $query->where('borrower_id', $user->id);
    }

    // ========================================
    // Helper Methods
    // ========================================

    /**
     * Check if transaction is lending type
     */
    public function isLending(): bool
    {
        return $this->type === 'lend';
    }

    /**
     * Check if transaction is selling type
     */
    public function isSelling(): bool
    {
        return $this->type === 'sell';
    }

    /**
     * Check if transaction is overdue
     */
    public function isOverdue(): bool
    {
        return $this->isLending() && $this->status === 'active' 
            && $this->due_date && $this->due_date->lt(Carbon::today());
    }

    /**
     * Calculate days overdue
     */
    public function daysOverdue(): int
    {
        if (!$this->isOverdue()) {
            return 0;
        }
        return Carbon::today()->diffInDays($this->due_date);
    }

    /**
     * Check if deposit was required
     */
    public function requiresDeposit(): bool
    {
        return $this->isLending() && $this->deposit_amount > 0;
    }

    /**
     * Check if final price was paid
     */
    public function requiresPayment(): bool
    {
        return $this->isSelling() && $this->final_price > 0;
    }

    /**
     * Mark as active
     */
    public function markAsActive(): bool
    {
        return $this->update([
            'status' => 'active',
            'start_date' => Carbon::today()
        ]);
    }

    /**
     * Mark as completed (returned/paid)
     */
    public function markAsCompleted(): bool
    {
        return $this->update([
            'status' => 'completed',
            'return_date' => Carbon::today()
        ]);
    }

    /**
     * Mark as late
     */
    public function markAsLate(): bool
    {
        return $this->update(['status' => 'late']);
    }

    /**
     * Mark as cancelled
     */
    public function markAsCancelled(): bool
    {
        return $this->update(['status' => 'cancelled']);
    }

    /**
     * Auto-calculate due date based on item's lending duration
     */
    public function calculateDueDate(): Carbon
    {
        if (!$this->isLending()) {
            throw new \Exception('Due date calculation only for lending transactions');
        }
        return Carbon::today()->addDays($this->item->lending_duration_days);
    }

    /**
     * Get formatted amounts
     */
    public function getFormattedDepositAttribute(): string
    {
        return $this->deposit_amount ? '৳' . number_format($this->deposit_amount, 2) : 'N/A';
    }

    public function getFormattedPriceAttribute(): string
    {
        return $this->final_price ? '৳' . number_format($this->final_price, 2) : 'N/A';
    }

    /**
     * Get status label for UI
     */
    public function getStatusLabel(): string
    {
        return match($this->status) {
            'pending' => 'Pending',
            'active' => 'Active',
            'completed' => 'Completed',
            'late' => 'Late',
            'cancelled' => 'Cancelled',
            default => 'Unknown'
        };
    }

    /**
     * Get status badge color
     */
    public function getStatusBadgeColor(): string
    {
        return match($this->status) {
            'pending' => 'yellow',
            'active' => 'blue',
            'completed' => 'green',
            'late' => 'red',
            'cancelled' => 'gray',
            default => 'gray'
        };
    }

    /**
     * Check if transaction can be rated
     */
    public function canBeRated(): bool
    {
        return in_array($this->status, ['completed', 'late']);
    }

    /**
     * Get average rating for this transaction
     */
    public function averageRating(): float
    {
        return $this->ratings()->avg('rating') ?? 0.0;
    }
}

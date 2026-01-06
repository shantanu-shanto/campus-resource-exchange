<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'availability_mode',
        'price',
        'lending_duration_days',
        'status',
        'pickup_location',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'lending_duration_days' => 'integer',
        ];
    }

    // ========================================
    // Relationships
    // ========================================

    /**
     * The user who owns this item
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * All transactions related to this item
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Active transaction (current borrower/buyer)
     */
    public function activeTransaction()
    {
        return $this->hasOne(Transaction::class)
            ->whereIn('status', ['active', 'pending']);
    }

    /**
     * Ratings received for this item (through transactions)
     */
    public function ratings()
    {
        return $this->hasManyThrough(Rating::class, Transaction::class);
    }

    // ========================================
    // Scope Methods
    // ========================================

    /**
     * Scope to get only available items
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    /**
     * Scope to get items available for lending
     */
    public function scopeForLending($query)
    {
        return $query->whereIn('availability_mode', ['lend', 'both'])
            ->where('status', 'available');
    }

    /**
     * Scope to get items available for selling
     */
    public function scopeForSelling($query)
    {
        return $query->whereIn('availability_mode', ['sell', 'both'])
            ->where('status', 'available');
    }

    /**
     * Scope to search items by title or description
     */
    public function scopeSearch($query, $term)
    {
        return $query->where(function($q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%");
        });
    }

    // ========================================
    // Helper Methods
    // ========================================

    /**
     * Check if item is available for lending
     */
    public function isAvailableForLending(): bool
    {
        return in_array($this->availability_mode, ['lend', 'both']) 
            && $this->status === 'available';
    }

    /**
     * Check if item is available for selling
     */
    public function isAvailableForSelling(): bool
    {
        return in_array($this->availability_mode, ['sell', 'both']) 
            && $this->status === 'available';
    }

    /**
     * Check if item is currently borrowed
     */
    public function isBorrowed(): bool
    {
        return $this->status === 'borrowed';
    }

    /**
     * Check if item has been sold
     */
    public function isSold(): bool
    {
        return $this->status === 'sold';
    }

    /**
     * Get the current borrower (if borrowed)
     */
    public function currentBorrower()
    {
        $transaction = $this->activeTransaction;
        return $transaction ? $transaction->borrower : null;
    }

    /**
     * Mark item as borrowed
     */
    public function markAsBorrowed(): void
    {
        $this->update(['status' => 'borrowed']);
    }

    /**
     * Mark item as sold
     */
    public function markAsSold(): void
    {
        $this->update(['status' => 'sold']);
    }

    /**
     * Mark item as available
     */
    public function markAsAvailable(): void
    {
        $this->update(['status' => 'available']);
    }

    /**
     * Calculate average rating for this item
     */
    public function averageRating(): float
    {
        return $this->ratings()->avg('rating') ?? 0.0;
    }

    /**
     * Get total number of times this item has been borrowed
     */
    public function totalBorrowCount(): int
    {
        return $this->transactions()
            ->where('type', 'lend')
            ->where('status', 'completed')
            ->count();
    }

    /**
     * Check if price is required based on availability mode
     */
    public function requiresPrice(): bool
    {
        return in_array($this->availability_mode, ['sell', 'both']);
    }

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute(): string
    {
        return $this->price ? '৳' . number_format($this->price, 2) : 'N/A';
    }

    /**
     * Get availability mode label
     */
    public function getAvailabilityModeLabel(): string
    {
        return match($this->availability_mode) {
            'lend' => 'Lending Only',
            'sell' => 'Selling Only',
            'both' => 'Lending & Selling',
            default => 'Unknown'
        };
    }

    /**
     * Get status label
     */
    public function getStatusLabel(): string
    {
        return match($this->status) {
            'available' => 'Available',
            'borrowed' => 'Currently Borrowed',
            'sold' => 'Sold',
            'reserved' => 'Reserved',
            default => 'Unknown'
        };
    }

    /**
     * Get status badge color for UI
     */
    public function getStatusBadgeColor(): string
    {
        return match($this->status) {
            'available' => 'green',
            'borrowed' => 'blue',
            'sold' => 'gray',
            'reserved' => 'yellow',
            default => 'gray'
        };
    }
}

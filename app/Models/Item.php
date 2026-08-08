<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'university_id',
        'title',
        'description',
        'category',
        'availability_mode', // share | lend | sell | both
        'price',
        'lending_duration_days',
        'status',
        'pickup_location',
        'image_path',
    ];

    protected function casts(): array
    {
        return [
            'price'                => 'decimal:2',
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
     * The university this item belongs to
     */
    public function university()
    {
        return $this->belongsTo(University::class);
    }

    /**
     * All transactions for this item
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Current active or pending transaction
     */
    public function activeTransaction()
    {
        return $this->hasOne(Transaction::class)
            ->whereIn('status', ['active', 'pending']);
    }

    /**
     * Ratings received through transactions
     */
    public function ratings()
    {
        return $this->hasManyThrough(
            Rating::class,
            Transaction::class,
            'item_id',        // FK on transactions
            'transaction_id', // FK on ratings
            'id',
            'id'
        );
    }

    /**
     * Conversations started about this item
     */
    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }

    // ========================================
    // Scopes
    // ========================================

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeForUniversity($query, int $universityId)
    {
        return $query->where('university_id', $universityId);
    }

    public function scopeForLending($query)
    {
        return $query->whereIn('availability_mode', ['lend', 'both', 'share'])
            ->where('status', 'available');
    }

    public function scopeForSelling($query)
    {
        return $query->whereIn('availability_mode', ['sell', 'both'])
            ->where('status', 'available');
    }

    public function scopeFree($query)
    {
        return $query->where('availability_mode', 'share')
            ->where('status', 'available');
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%");
        });
    }

    // ========================================
    // Status / Mode Helpers
    // ========================================

    public function isAvailableForLending(): bool
    {
        return in_array($this->availability_mode, ['lend', 'both', 'share'])
            && $this->status === 'available';
    }

    public function isAvailableForSelling(): bool
    {
        return in_array($this->availability_mode, ['sell', 'both'])
            && $this->status === 'available';
    }

    public function isFree(): bool
    {
        return $this->availability_mode === 'share';
    }

    public function isBorrowed(): bool
    {
        return $this->status === 'borrowed';
    }

    public function isSold(): bool
    {
        return $this->status === 'sold';
    }

    public function markAsBorrowed(): void
    {
        $this->update(['status' => 'borrowed']);
    }

    public function markAsSold(): void
    {
        $this->update(['status' => 'sold']);
    }

    public function markAsAvailable(): void
    {
        $this->update(['status' => 'available']);
    }

    // ========================================
    // Stat Helpers
    // ========================================

    public function averageRating(): float
    {
        return $this->ratings()->avg('rating') ?? 0.0;
    }

    public function totalBorrowCount(): int
    {
        return $this->transactions()
            ->whereIn('type', ['lend', 'share'])
            ->where('status', 'completed')
            ->count();
    }

    public function currentBorrower(): ?User
    {
        return $this->activeTransaction?->borrower;
    }

    // ========================================
    // Display Helpers
    // ========================================

    public function getAvailabilityModeLabel(): string
    {
        return match($this->availability_mode) {
            'share' => 'Free / Share',
            'lend'  => 'Lending Only',
            'sell'  => 'Selling Only',
            'both'  => 'Lending & Selling',
            default => 'Unknown',
        };
    }

    public function getAvailabilityModeBadgeColor(): string
    {
        return match($this->availability_mode) {
            'share' => 'success',
            'lend'  => 'info',
            'sell'  => 'danger',
            'both'  => 'primary',
            default => 'secondary',
        };
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            'available' => 'Available',
            'borrowed'  => 'Currently Borrowed',
            'sold'      => 'Sold',
            'reserved'  => 'Reserved',
            default     => 'Unknown',
        };
    }

    public function getStatusBadgeColor(): string
    {
        return match($this->status) {
            'available' => 'success',
            'borrowed'  => 'warning',
            'sold'      => 'secondary',
            'reserved'  => 'info',
            default     => 'secondary',
        };
    }

    public function getFormattedPriceAttribute(): string
    {
        return $this->price ? '₹' . number_format($this->price, 2) : 'Free';
    }

    public function getImageUrlAttribute(): string
    {
        return $this->image_path
            ? asset('storage/' . $this->image_path)
            : asset('images/placeholder.png');
    }
}
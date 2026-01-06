<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'transaction_id',
        'rater_id',
        'rating',
        'comment',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rating' => 'integer',
        ];
    }

    // ========================================
    // Relationships
    // ========================================

    /**
     * The transaction this rating belongs to
     */
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * The user who gave this rating
     */
    public function rater()
    {
        return $this->belongsTo(User::class, 'rater_id');
    }

    /**
     * The borrower of this transaction (for owner ratings)
     */
    public function borrower()
    {
        return $this->belongsTo(User::class, 'borrower_id')->via('transaction');
    }

    /**
     * The owner of the item (for borrower ratings)
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id')->via('transaction.item');
    }

    /**
     * The item being rated (through transaction)
     */
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id')->via('transaction');
    }

    // ========================================
    // Scope Methods
    // ========================================

    /**
     * Scope: Ratings for a specific user (received ratings)
     */
    public function scopeForUser($query, User $user)
    {
        return $query->whereHas('transaction.item', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->orWhereHas('transaction', function ($q) use ($user) {
            $q->where('borrower_id', $user->id);
        });
    }

    /**
     * Scope: Ratings for a specific item
     */
    public function scopeForItem($query, Item $item)
    {
        return $query->whereHas('transaction', function ($q) use ($item) {
            $q->where('item_id', $item->id);
        });
    }

    /**
     * Scope: Ratings with comments
     */
    public function scopeWithComments($query)
    {
        return $query->whereNotNull('comment')->where('comment', '!=', '');
    }

    /**
     * Scope: High ratings (4-5 stars)
     */
    public function scopeHighRatings($query)
    {
        return $query->where('rating', '>=', 4);
    }

    /**
     * Scope: Low ratings (1-2 stars)
     */
    public function scopeLowRatings($query)
    {
        return $query->where('rating', '<=', 2);
    }

    // ========================================
    // Helper Methods
    // ========================================

    /**
     * Check if rating is excellent (5 stars)
     */
    public function isExcellent(): bool
    {
        return $this->rating === 5;
    }

    /**
     * Check if rating is poor (1 star)
     */
    public function isPoor(): bool
    {
        return $this->rating === 1;
    }

    /**
     * Get rating as emoji for UI
     */
    public function getEmojiAttribute(): string
    {
        return match($this->rating) {
            5 => '⭐⭐⭐⭐⭐',
            4 => '⭐⭐⭐⭐',
            3 => '⭐⭐⭐',
            2 => '⭐⭐',
            1 => '⭐',
            default => '⭐'
        };
    }

    /**
     * Get rating label
     */
    public function getRatingLabel(): string
    {
        return match($this->rating) {
            5 => 'Excellent',
            4 => 'Very Good',
            3 => 'Good',
            2 => 'Poor',
            1 => 'Very Poor',
            default => 'Unknown'
        };
    }

    /**
     * Get rating badge color
     */
    public function getRatingBadgeColor(): string
    {
        return match($this->rating) {
            4, 5 => 'green',
            3 => 'blue',
            2 => 'yellow',
            1 => 'red',
            default => 'gray'
        };
    }

    /**
     * Check if this is a borrower rating (rating the owner)
     */
    public function isBorrowerRating(): bool
    {
        return $this->rater_id === $this->transaction->borrower_id;
    }

    /**
     * Check if this is an owner rating (rating the borrower)
     */
    public function isOwnerRating(): bool
    {
        return $this->rater_id === $this->transaction->item->user_id;
    }

    /**
     * Get who was rated (borrower or owner)
     */
    public function ratedUser(): User
    {
        return $this->isBorrowerRating() 
            ? $this->owner() 
            : $this->borrower();
    }

    /**
     * Check if rating can be created for this transaction
     * (Transaction must be completed or late)
     */
    public static function canRateTransaction(Transaction $transaction): bool
    {
        return in_array($transaction->status, ['completed', 'late']);
    }

    /**
     * Prevent duplicate ratings for same transaction/user
     */
    public static function userHasRatedTransaction(User $user, Transaction $transaction): bool
    {
        return self::where('transaction_id', $transaction->id)
            ->where('rater_id', $user->id)
            ->exists();
    }

    /**
     * Get user's average rating as rater
     */
    public static function userAverageRating(User $user): float
    {
        return self::where('rater_id', $user->id)->avg('rating') ?? 0.0;
    }

    /**
     * Get formatted rating display
     */
    public function getDisplayAttribute(): string
    {
        $emoji = $this->emoji;
        $label = $this->rating_label;
        $comment = $this->comment ? '"' . Str::limit($this->comment, 50) . '"' : '';
        
        return "{$emoji} {$label} {$comment}";
    }
}

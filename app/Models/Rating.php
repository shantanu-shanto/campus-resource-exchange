<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Rating extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'rater_id',
        'rating',
        'comment',
    ];

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
     * The borrower of the transaction — through transaction
     */
    public function borrower()
    {
        return $this->hasOneThrough(
            User::class,
            Transaction::class,
            'id',             // FK on transactions
            'id',             // FK on users
            'transaction_id', // local key on ratings
            'borrower_id'     // local key on transactions
        );
    }

    /**
     * The owner/lender of the transaction — through transaction using owner_id
     */
    public function owner()
    {
        return $this->hasOneThrough(
            User::class,
            Transaction::class,
            'id',
            'id',
            'transaction_id',
            'owner_id'        // direct owner_id column on transactions
        );
    }

    /**
     * The item being rated — through transaction
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

    public function scopeForUser($query, User $user)
    {
        return $query->whereHas('transaction', function ($q) use ($user) {
            $q->where('owner_id', $user->id)
              ->orWhere('borrower_id', $user->id);
        });
    }

    public function scopeForItem($query, Item $item)
    {
        return $query->whereHas('transaction', function ($q) use ($item) {
            $q->where('item_id', $item->id);
        });
    }

    public function scopeWithComments($query)
    {
        return $query->whereNotNull('comment')->where('comment', '!=', '');
    }

    public function scopeHighRatings($query)
    {
        return $query->where('rating', '>=', 4);
    }

    public function scopeLowRatings($query)
    {
        return $query->where('rating', '<=', 2);
    }

    // ========================================
    // Type Helpers
    // ========================================

    /**
     * True if the rater is the borrower (rating the owner)
     */
    public function isBorrowerRating(): bool
    {
        return $this->rater_id === $this->transaction->borrower_id;
    }

    /**
     * True if the rater is the owner (rating the borrower)
     */
    public function isOwnerRating(): bool
    {
        return $this->rater_id === $this->transaction->owner_id;
    }

    // ========================================
    // Validation Helpers
    // ========================================

    public static function canRateTransaction(Transaction $transaction): bool
    {
        return in_array($transaction->status, ['completed', 'late']);
    }

    public static function userHasRatedTransaction(User $user, Transaction $transaction): bool
    {
        return self::where('transaction_id', $transaction->id)
            ->where('rater_id', $user->id)
            ->exists();
    }

    // ========================================
    // Display Helpers
    // ========================================

    public function getRatingLabel(): string
    {
        return match($this->rating) {
            5 => 'Excellent',
            4 => 'Very Good',
            3 => 'Good',
            2 => 'Poor',
            1 => 'Very Poor',
            default => 'Unknown',
        };
    }

    public function getRatingBadgeColor(): string
    {
        return match($this->rating) {
            4, 5 => 'success',
            3    => 'info',
            2    => 'warning',
            1    => 'danger',
            default => 'secondary',
        };
    }

    public function getEmojiAttribute(): string
    {
        return str_repeat('⭐', $this->rating);
    }

    public function getPreviewAttribute(): string
    {
        return Str::limit($this->comment ?? '', 60);
    }
}
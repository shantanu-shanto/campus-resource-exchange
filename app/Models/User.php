<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    // ========================================
    // Relationships for Campus Exchange
    // ========================================

    /**
     * Items owned by this user (lending/selling)
     */
    public function items()
    {
        return $this->hasMany(Item::class);
    }

    /**
     * Transactions where this user is the borrower/buyer
     */
    public function transactionsAsBorrower()
    {
        return $this->hasMany(Transaction::class, 'borrower_id');
    }

    /**
     * Transactions where this user is the owner (through their items)
     */
    public function transactionsAsOwner()
    {
        return $this->hasManyThrough(Transaction::class, Item::class);
    }

    /**
     * Ratings given by this user
     */
    public function ratingsGiven()
    {
        return $this->hasMany(Rating::class, 'rater_id');
    }

    /**
     * Ratings received by this user (on their items)
     */
    public function ratingsReceived()
    {
        return $this->hasManyThrough(Rating::class, Item::class, 'user_id', 'transaction_id', 'id', 'id')
            ->join('transactions', 'ratings.transaction_id', '=', 'transactions.id')
            ->where('transactions.borrower_id', '!=', $this->id);
    }

    /**
     * Penalties this user has incurred as borrower
     */
    public function penalties()
    {
        return $this->hasManyThrough(Penalty::class, Transaction::class, 'borrower_id');
    }

    // ========================================
    // Helper Methods
    // ========================================

    /**
     * Check if user is an admin
     */
    public function isAdmin(): bool
    {
        return $this->is_admin;
    }

    /**
     * Check if user can manage a specific item
     */
    public function canManageItem(Item $item): bool
    {
        return $this->isAdmin() || $this->id === $item->user_id;
    }

    /**
     * Calculate average rating received by this user
     */
    public function averageRating(): float
    {
        return $this->ratingsReceived()->avg('rating') ?? 0.0;
    }

    /**
     * Get total unpaid penalties for this user
     */
    public function totalUnpaidPenalties(): float
    {
        return $this->penalties()->where('status', 'pending')->sum('amount');
    }

    /**
     * Check if user has any overdue items
     */
    public function hasOverdueItems(): bool
    {
        return $this->transactionsAsBorrower()
            ->where('status', 'late')
            ->exists();
    }

    /**
     * Get count of active transactions as borrower
     */
    public function activeTransactionsCount(): int
    {
        return $this->transactionsAsBorrower()
            ->where('status', 'active')
            ->count();
    }


    /**
   * Conversations where user is user_id_1
   */
    public function conversationsAsUser1()
    {
        return $this->hasMany(Conversation::class, 'user_id_1');
    }

    /**
     * Conversations where user is user_id_2
     */
    public function conversationsAsUser2()
    {
        return $this->hasMany(Conversation::class, 'user_id_2');
    }

    /**
     * All conversations for user
     */
    public function conversations()
    {
        return Conversation::where('user_id_1', $this->id)
            ->orWhere('user_id_2', $this->id);
    }

    /**
     * Messages sent by user
     */
    public function messagesSent()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Messages received by user
     */
    public function messagesReceived()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    /**
     * Get unread message count
     */
    public function getUnreadMessageCount(): int
    {
        return Message::where('receiver_id', $this->id)
            ->whereNull('read_at')
            ->count();
    }

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',           // super_admin | uni_admin | user
        'status',         // pending | verified | rejected
        'university_id',  // null for super_admin
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // ========================================
    // Relationships
    // ========================================

    /**
     * The university this user belongs to (null for super_admin)
     */
    public function university()
    {
        return $this->belongsTo(University::class);
    }

    /**
     * Items this user owns (listed for lending/selling)
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
     * Transactions where this user is the owner/lender
     */
    public function transactionsAsOwner()
    {
        return $this->hasMany(Transaction::class, 'owner_id');
    }

    /**
     * Ratings this user has given to others
     */
    public function ratingsGiven()
    {
        return $this->hasMany(Rating::class, 'rater_id');
    }

    /**
     * Ratings this user has received — as owner OR as borrower.
     *
     * A user can be rated from two directions:
     *   1. As owner: borrower rates them after a completed transaction
     *   2. As borrower: owner rates them after a completed transaction
     *
     * We collect transaction IDs from both roles, then return all ratings
     * on those transactions that were NOT given by this user themselves.
     *
     * This replaces the broken hasManyThrough which only captured owner-side ratings.
     */
    public function ratingsReceived()
    {
        $asOwner    = Transaction::where('owner_id', $this->id)->pluck('id');
        $asBorrower = Transaction::where('borrower_id', $this->id)->pluck('id');
        $transactionIds = $asOwner->merge($asBorrower)->unique();

        return Rating::whereIn('transaction_id', $transactionIds)
                    ->where('rater_id', '!=', $this->id);
    }

    /**
     * Penalties this user has incurred as borrower
     */
    public function penalties()
    {
        return $this->hasManyThrough(
            Penalty::class,
            Transaction::class,
            'borrower_id',   // FK on transactions
            'transaction_id', // FK on penalties
            'id',
            'id'
        );
    }

    /**
     * Conversations where user is participant 1
     */
    public function conversationsAsUser1()
    {
        return $this->hasMany(Conversation::class, 'user_id_1');
    }

    /**
     * Conversations where user is participant 2
     */
    public function conversationsAsUser2()
    {
        return $this->hasMany(Conversation::class, 'user_id_2');
    }

    /**
     * Messages sent by this user
     */
    public function messagesSent()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Messages received by this user
     */
    public function messagesReceived()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    // ========================================
    // Role Helpers
    // ========================================

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isUniAdmin(): bool
    {
        return $this->role === 'uni_admin';
    }

    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    /**
     * Kept for any legacy references — now checks role instead of boolean
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, ['super_admin', 'uni_admin']);
    }

    // ========================================
    // Status Helpers
    // ========================================

    public function isVerified(): bool
    {
        return $this->status === 'verified';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function verify(): bool
    {
        return $this->update(['status' => 'verified']);
    }

    public function reject(): bool
    {
        return $this->update(['status' => 'rejected']);
    }

    // ========================================
    // Permission Helpers
    // ========================================

    /**
     * Check if user can manage a specific item
     */
    public function canManageItem(Item $item): bool
    {
        return $this->isAdmin() || $this->id === $item->user_id;
    }

    /**
     * Check if user belongs to a given university
     */
    public function belongsToUniversity(int $universityId): bool
    {
        return $this->university_id === $universityId;
    }

    // ========================================
    // Stat / Aggregate Helpers
    // ========================================

    /**
     * Average rating received by this user (both as owner and as borrower)
     */
    public function averageRating(): float
    {
        return $this->ratingsReceived()->avg('rating') ?? 0.0;
    }

    /**
     * Total unpaid penalties for this user
     */
    public function totalUnpaidPenalties(): float
    {
        return $this->penalties()->where('penalties.status', 'pending')->sum('amount');
    }

    /**
     * Check if user has any pending penalties (blocks new transactions)
     */
    public function hasPendingPenalties(): bool
    {
        return $this->penalties()->where('penalties.status', 'pending')->exists();
    }

    /**
     * Check if user has any currently late/overdue items
     */
    public function hasOverdueItems(): bool
    {
        return $this->transactionsAsBorrower()
            ->where('status', 'late')
            ->exists();
    }

    /**
     * Count of active borrow transactions
     */
    public function activeTransactionsCount(): int
    {
        return $this->transactionsAsBorrower()
            ->where('status', 'active')
            ->count();
    }

    /**
     * Unread message count
     * NOTE: method is named unreadMessageCount() — do NOT call getUnreadMessageCount()
     */
    public function unreadMessageCount(): int
    {
        return Message::where('receiver_id', $this->id)
            ->whereNull('read_at')
            ->count();
    }

    /**
     * All conversations this user is part of (both sides).
     * Returns a query builder — not a relationship, cannot be eager loaded with with().
     */
    public function allConversations()
    {
        return Conversation::where('user_id_1', $this->id)
            ->orWhere('user_id_2', $this->id);
    }

    // ========================================
    // Display Helpers
    // ========================================

    public function getRoleLabel(): string
    {
        return match($this->role) {
            'super_admin' => 'Super Administrator',
            'uni_admin'   => 'University Admin',
            'user'        => 'Student / Teacher',
            default       => 'Unknown',
        };
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            'verified' => 'Verified',
            'pending'  => 'Pending',
            'rejected' => 'Rejected',
            default    => 'Unknown',
        };
    }

    public function getStatusBadgeColor(): string
    {
        return match($this->status) {
            'verified' => 'success',
            'pending'  => 'warning',
            'rejected' => 'danger',
            default    => 'secondary',
        };
    }

    // ----------------------------------------
    // SUPPORT TICKETS
    // ----------------------------------------

    public function supportTickets()
    {
        return $this->hasMany(\App\Models\SupportTicket::class);
    }

    public function ticketReplies()
    {
        return $this->hasMany(\App\Models\TicketReply::class);
    }

}
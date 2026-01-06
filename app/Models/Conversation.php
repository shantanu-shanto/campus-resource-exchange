<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Conversation extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id_1',
        'user_id_2',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    // ========================================
    // Relationships
    // ========================================

    /**
     * First user in conversation
     */
    public function user1()
    {
        return $this->belongsTo(User::class, 'user_id_1');
    }

    /**
     * Second user in conversation
     */
    public function user2()
    {
        return $this->belongsTo(User::class, 'user_id_2');
    }

    /**
     * All messages in conversation
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Last message in conversation
     */
    public function lastMessage()
    {
        return $this->hasOne(Message::class)
            ->whereNull('deleted_at')
            ->latest();
    }

    // ========================================
    // Scopes
    // ========================================

    /**
     * Scope: Conversations for a specific user
     */
    public function scopeForUser($query, User $user)
    {
        return $query->where('user_id_1', $user->id)
            ->orWhere('user_id_2', $user->id);
    }

    /**
     * Scope: Conversations with unread messages
     */
    public function scopeWithUnreadMessages($query, User $user)
    {
        return $query->whereHas('messages', function($q) use ($user) {
            $q->where('receiver_id', $user->id)
              ->whereNull('read_at');
        });
    }

    // ========================================
    // Helper Methods
    // ========================================

    /**
     * Get the other user in conversation
     */
    public function getOtherUser(User $user): User
    {
        return $this->user_id_1 === $user->id ? $this->user2 : $this->user1;
    }

    /**
     * Get unread message count for a user
     */
    public function getUnreadCount(User $user): int
    {
        return $this->messages()
            ->where('receiver_id', $user->id)
            ->whereNull('read_at')
            ->count();
    }

    /**
     * Mark all messages as read for a user
     */
    public function markAllAsRead(User $user): void
    {
        $this->messages()
            ->where('receiver_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Get message count
     */
    public function getMessageCount(): int
    {
        return $this->messages()->count();
    }

    /**
     * Check if user can access this conversation
     */
    public function belongsToUser(User $user): bool
    {
        return $this->user_id_1 === $user->id || $this->user_id_2 === $user->id;
    }
}

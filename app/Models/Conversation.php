<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Conversation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id_1',
        'user_id_2',
        'item_id',   // context: which item this conversation is about
    ];

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

    public function user1()
    {
        return $this->belongsTo(User::class, 'user_id_1');
    }

    public function user2()
    {
        return $this->belongsTo(User::class, 'user_id_2');
    }

    /**
     * The item this conversation is about (nullable)
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function lastMessage()
    {
        return $this->hasOne(Message::class)
            ->whereNull('deleted_at')
            ->latest();
    }

    // ========================================
    // Scopes
    // ========================================

    public function scopeForUser($query, User $user)
    {
        return $query->where('user_id_1', $user->id)
            ->orWhere('user_id_2', $user->id);
    }

    public function scopeWithUnreadMessages($query, User $user)
    {
        return $query->whereHas('messages', function ($q) use ($user) {
            $q->where('receiver_id', $user->id)
              ->whereNull('read_at');
        });
    }

    // ========================================
    // Helpers
    // ========================================

    public function getOtherUser(User $user): User
    {
        return $this->user_id_1 === $user->id ? $this->user2 : $this->user1;
    }

    public function getUnreadCount(User $user): int
    {
        return $this->messages()
            ->where('receiver_id', $user->id)
            ->whereNull('read_at')
            ->count();
    }

    public function markAllAsRead(User $user): void
    {
        $this->messages()
            ->where('receiver_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function belongsToUser(User $user): bool
    {
        return $this->user_id_1 === $user->id || $this->user_id_2 === $user->id;
    }

    /**
     * Find existing conversation between two users about a specific item.
     * Used to prevent duplicate conversations.
     */
    public static function findBetween(int $userId1, int $userId2, ?int $itemId = null): ?self
    {
        return self::where(function ($q) use ($userId1, $userId2) {
                $q->where('user_id_1', $userId1)->where('user_id_2', $userId2);
            })
            ->orWhere(function ($q) use ($userId1, $userId2) {
                $q->where('user_id_1', $userId2)->where('user_id_2', $userId1);
            })
            ->when($itemId, fn($q) => $q->where('item_id', $itemId))
            ->first();
    }
}
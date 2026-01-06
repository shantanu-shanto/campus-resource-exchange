<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'conversation_id',
        'sender_id',
        'receiver_id',
        'message',
        'read_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    // ========================================
    // Relationships
    // ========================================

    /**
     * Conversation this message belongs to
     */
    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * User who sent this message
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * User who received this message
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    // ========================================
    // Scopes
    // ========================================

    /**
     * Scope: Unread messages
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope: Read messages
     */
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Scope: Messages from specific user
     */
    public function scopeFromUser($query, User $user)
    {
        return $query->where('sender_id', $user->id);
    }

    /**
     * Scope: Messages to specific user
     */
    public function scopeToUser($query, User $user)
    {
        return $query->where('receiver_id', $user->id);
    }

    /**
     * Scope: Recent messages
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // ========================================
    // Helper Methods
    // ========================================

    /**
     * Check if message is read
     */
    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    /**
     * Check if message is unread
     */
    public function isUnread(): bool
    {
        return $this->read_at === null;
    }

    /**
     * Mark message as read
     */
    public function markAsRead(): bool
    {
        return $this->update(['read_at' => now()]);
    }

    /**
     * Get formatted message for display
     */
    public function getFormattedMessageAttribute(): string
    {
        return nl2br(htmlspecialchars($this->message));
    }

    /**
     * Get time difference for display
     */
    public function getTimeDisplayAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Get time for API
     */
    public function getTimeApiAttribute(): string
    {
        return $this->created_at->format('Y-m-d H:i:s');
    }

    /**
     * Truncate message for preview
     */
    public function getPreviewAttribute(): string
    {
        return \Illuminate\Support\Str::limit($this->message, 50);
    }
}

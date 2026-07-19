<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TicketReply extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ticket_id',
        'user_id',
        'message',
        'sender_role',
    ];

    // ========================================
    // Relationships
    // ========================================

    public function ticket()
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    /**
     * The user who wrote this reply (student or uni admin).
     * Named "author" to match what the Blade views expect.
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ========================================
    // Helpers
    // ========================================

    public function isFromAdmin(): bool
    {
        return $this->sender_role === 'uni_admin';
    }

    public function isFromUser(): bool
    {
        return $this->sender_role === 'user';
    }
}

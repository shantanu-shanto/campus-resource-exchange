<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupportTicket extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'university_id',
        'transaction_id',
        'item_id',
        'subject',
        'description',
        'category',
        'priority',
        'status',
        'resolved_by',
        'resolved_at',
        'closed_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'closed_at'   => 'datetime',
    ];

    // ========================================
    // Relationships
    // ========================================

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function university()
    {
        return $this->belongsTo(University::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function replies()
    {
        return $this->hasMany(TicketReply::class, 'ticket_id')->oldest();
    }

    public function latestReply()
    {
        return $this->hasOne(TicketReply::class, 'ticket_id')->latest();
    }

    // ========================================
    // Scopes
    // ========================================

    public function scopeForUniversity($query, $universityId)
    {
        return $query->where('university_id', $universityId);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['open', 'in_progress']);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    // ========================================
    // Status checks
    // ========================================

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isResolved(): bool
    {
        return $this->status === 'resolved';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['open', 'in_progress']);
    }

    // ========================================
    // Status transitions
    // ========================================

    public function markAsInProgress(): void
    {
        $this->update(['status' => 'in_progress']);
    }

    public function markAsResolved(User $resolver): void
    {
        $this->update([
            'status'      => 'resolved',
            'resolved_by' => $resolver->id,
            'resolved_at' => now(),
        ]);
    }

    public function markAsClosed(): void
    {
        $this->update([
            'status'    => 'closed',
            'closed_at' => now(),
        ]);
    }

    public function reopen(): void
    {
        $this->update([
            'status'      => 'open',
            'resolved_by' => null,
            'resolved_at' => null,
            'closed_at'   => null,
        ]);
    }

    // ========================================
    // Display helpers (used by Blade views)
    // ========================================

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'open'        => 'Open',
            'in_progress' => 'In Progress',
            'resolved'    => 'Resolved',
            'closed'      => 'Closed',
            default       => ucfirst($this->status),
        };
    }

    public function getStatusBadgeColor(): string
    {
        return match ($this->status) {
            'open'        => 'danger',
            'in_progress' => 'warning',
            'resolved'    => 'success',
            'closed'      => 'secondary',
            default       => 'secondary',
        };
    }

    public function getPriorityLabel(): string
    {
        return ucfirst($this->priority);
    }

    public function getPriorityBadgeColor(): string
    {
        return match ($this->priority) {
            'high'   => 'danger',
            'medium' => 'warning',
            'low'    => 'secondary',
            default  => 'secondary',
        };
    }

    public function getCategoryLabel(): string
    {
        return match ($this->category) {
            'transaction_issue' => 'Transaction Issue',
            'item_condition'    => 'Item Condition',
            'penalty_dispute'   => 'Penalty Dispute',
            'user_behaviour'    => 'User Behaviour',
            'account_issue'     => 'Account Issue',
            'other'             => 'Other',
            default             => ucfirst($this->category),
        };
    }
}

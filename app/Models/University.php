<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class University extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'domain',
        'state',
        'city',
        'country',
        'description',
        'status',
        'rejection_reason',
        'applicant_name',
        'applicant_email',
        'applicant_phone',
        'admin_email',
        'admin_password_hash',
        'approved_at',
        'rejected_at',
    ];

    protected $hidden = [
        'admin_password_hash',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'created_at'  => 'datetime',
            'updated_at'  => 'datetime',
        ];
    }

    // ========================================
    // Relationships
    // ========================================

    /**
     * All users belonging to this university (students, teachers, uni admin)
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * The university admin user
     */
    public function admin()
    {
        return $this->hasOne(User::class)->where('role', 'uni_admin');
    }

    /**
     * All verified students/teachers of this university
     */
    public function verifiedUsers()
    {
        return $this->hasMany(User::class)
            ->where('role', 'user')
            ->where('status', 'verified');
    }

    /**
     * All pending users awaiting verification
     */
    public function pendingUsers()
    {
        return $this->hasMany(User::class)
            ->where('role', 'user')
            ->where('status', 'pending');
    }

    /**
     * All items listed within this university
     */
    public function items()
    {
        return $this->hasMany(Item::class);
    }

    // ========================================
    // Scopes
    // ========================================

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeByState($query, string $state)
    {
        return $query->where('state', $state);
    }

    // ========================================
    // Status Helpers
    // ========================================

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function approve(): bool
    {
        return $this->update([
            'status'      => 'approved',
            'approved_at' => now(),
            'rejected_at' => null,
            'rejection_reason' => null,
        ]);
    }

    public function reject(string $reason = ''): bool
    {
        return $this->update([
            'status'           => 'rejected',
            'rejected_at'      => now(),
            'rejection_reason' => $reason,
            'approved_at'      => null,
        ]);
    }

    // ========================================
    // Credential Management
    // ========================================

    /**
     * Issue admin credentials after approval.
     * Stores hashed password, returns plain text once for emailing.
     */
    public function issueCredentials(string $adminEmail, string $plainPassword): string
    {
        $this->update([
            'admin_email'         => $adminEmail,
            'admin_password_hash' => Hash::make($plainPassword),
        ]);

        return $plainPassword;
    }

    /**
     * Validate submitted admin credentials (used during uni admin login)
     */
    public function validateAdminCredentials(string $password): bool
    {
        return Hash::check($password, $this->admin_password_hash);
    }

    // ========================================
    // Domain Helpers
    // ========================================

    /**
     * Check if a given email belongs to this university's domain
     */
    public function emailMatchesDomain(string $email): bool
    {
        $emailDomain = substr(strrchr($email, '@'), 1);
        return str_ends_with($emailDomain, $this->domain);
    }

    /**
     * Get all unique states from approved universities
     * Used by the student register page state filter
     */
    public static function approvedStates(): array
    {
        return self::approved()
            ->distinct()
            ->orderBy('state')
            ->pluck('state')
            ->toArray();
    }

    // ========================================
    // Stats
    // ========================================

    public function totalUsers(): int
    {
        return $this->users()->where('role', 'user')->count();
    }

    public function totalVerifiedUsers(): int
    {
        return $this->verifiedUsers()->count();
    }

    public function totalPendingUsers(): int
    {
        return $this->pendingUsers()->count();
    }

    public function totalItems(): int
    {
        return $this->items()->count();
    }

    public function totalActiveItems(): int
    {
        return $this->items()->where('status', 'available')->count();
    }

    // ========================================
    // Display Helpers
    // ========================================

    public function getStatusLabel(): string
    {
        return match($this->status) {
            'approved' => 'Approved',
            'pending'  => 'Pending Review',
            'rejected' => 'Rejected',
            default    => 'Unknown',
        };
    }

    public function getStatusBadgeColor(): string
    {
        return match($this->status) {
            'approved' => 'success',
            'pending'  => 'warning',
            'rejected' => 'danger',
            default    => 'secondary',
        };
    }

    public function getFullLocationAttribute(): string
    {
        return "{$this->city}, {$this->state}, {$this->country}";
    }

    // ----------------------------------------
    // SUPPORT TICKETS
    // ----------------------------------------

    public function supportTickets()
    {
        return $this->hasMany(\App\Models\SupportTicket::class);
    }

}
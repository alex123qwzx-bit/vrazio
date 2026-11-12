<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'google_id',
        'avatar',
        'phone',
        'date_of_birth',
        'role',
        'is_approved',
        'approved_at',
        'approved_by',
        'admin_pin',
        'password_confirmation_token',
        'password_confirmation_expires_at',
        'password',
        'email_verified_at',
        'phone_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
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
            'date_of_birth' => 'date',
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'is_approved' => 'boolean',
            'approved_at' => 'datetime',
            'password_confirmation_expires_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Get all orders belonging to this user.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get all VRAZIO PRIVÉ CLUB memberships for this user.
     */
    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class)->latest();
    }

    /**
     * Get currently active PRIVÉ CLUB membership if any.
     */
    public function activeMembership(): ?Membership
    {
        return $this->memberships()
            ->active()
            ->first();
    }

    /**
     * Check if user is an active PRIVÉ CLUB member.
     */
    public function isPriveMember(): bool
    {
        return $this->activeMembership() !== null;
    }

    /**
     * Get active PRIVÉ CLUB membership object or null.
     */
    public function priveMember(): ?Membership
    {
        return $this->activeMembership();
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Scope a query to only include admin users (including super_admin).
     */
    public function scopeAdmins(Builder $query): Builder
    {
        return $query->whereIn('role', ['admin', 'super_admin']);
    }

    /**
     * Scope a query to only include super admin users.
     */
    public function scopeSuperAdmins(Builder $query): Builder
    {
        return $query->where('role', 'super_admin');
    }

    /**
     * Scope a query to only include sub-managers.
     */
    public function scopeSubManagers(Builder $query): Builder
    {
        return $query->where('role', 'sub_manager');
    }

    /**
     * Scope a query to only include patron users.
     */
    public function scopePatrons(Builder $query): Builder
    {
        return $query->where('role', 'patron');
    }

    /**
     * Scope a query to all staff accounts.
     */
    public function scopeStaff(Builder $query): Builder
    {
        return $query->whereIn('role', ['super_admin', 'admin', 'sub_manager']);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Determine if the user is a super administrator.
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    /**
     * Determine if the user is an admin or super admin.
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'super_admin'], true);
    }

    /**
     * Determine if the user is a sub-manager.
     */
    public function isSubManager(): bool
    {
        return $this->role === 'sub_manager';
    }

    /**
     * Check if the account has been approved by the Super Administrator.
     */
    public function isApproved(): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($this->role === 'admin') {
            return $this->is_approved !== false;
        }

        return (bool) $this->is_approved;
    }

    /**
     * Determine whether the user can access the Filament admin panel.
     * Enforces super admin authority and sub-manager approval.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // Super admins have unrestricted panel access
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Standard admins have operational access unless revoked
        if ($this->role === 'admin') {
            return $this->is_approved !== false;
        }

        // Sub-managers MUST be explicitly approved by Super Administrator
        if ($this->isSubManager() && (bool) $this->is_approved) {
            return true;
        }

        return false;
    }
}

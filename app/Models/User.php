<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'invited_by',
        'invited_at',
        'last_login_at',
        'status',
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
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'invited_at' => 'datetime',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Filament Access Control
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // Allow access if active and has a role
        return $this->status === 'active' && $this->role_id !== null;
    }

    /**
     * Get the user's role
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get bookings created by this user
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'created_by');
    }

    /**
     * Get invitations sent by this user
     */
    public function sentInvitations(): HasMany
    {
        return $this->hasMany(StaffInvitation::class, 'invited_by');
    }

    /**
     * Get activity logs for this user
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Helper to check role slug
     */
    public function hasRole(string|array $slugs): bool
    {
        if (!$this->role) {
            return false;
        }

        if (is_array($slugs)) {
            return in_array($this->role->slug, $slugs);
        }

        return $this->role->slug === $slugs;
    }

    /**
     * Helper to check permission via role
     */
    public function canPerform(string $permission): bool
    {
        return $this->role && $this->role->hasPermission($permission);
    }
}

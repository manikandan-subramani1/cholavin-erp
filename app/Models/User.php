<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'mobile',
        'password',
        'role_id',
        'is_active',
        'last_login_at',
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
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function shops()
    {
        return $this->belongsToMany(Shop::class)->withPivot(['is_default', 'is_active', 'created_by']);
    }

    public function godowns()
    {
        return $this->belongsToMany(Godown::class)->withPivot(['is_default', 'is_active', 'created_by']);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class)->withPivot('allowed')->withTimestamps();
    }

    public function loginHistories()
    {
        return $this->hasMany(LoginHistory::class);
    }

    public function isSuperAdmin(): bool
    {
        return (bool) $this->role?->is_super_admin;
    }

    public function hasPermission(string $code): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $override = $this->relationLoaded('permissions')
            ? $this->permissions->firstWhere('code', $code)
            : $this->permissions()->where('code', $code)->first();

        if ($override) {
            return (bool) $override->pivot->allowed;
        }

        if (! $this->relationLoaded('role')) {
            $this->load('role.permissions');
        } elseif ($this->role && ! $this->role->relationLoaded('permissions')) {
            $this->role->load('permissions');
        }

        return $this->role?->permissions->contains('code', $code) ?? false;
    }

    public function effectivePermissionCodes()
    {
        $this->loadMissing(['role.permissions', 'permissions']);
        $codes = $this->role?->permissions->pluck('code')->flip() ?? collect();

        foreach ($this->permissions as $permission) {
            if ($permission->pivot->allowed) {
                $codes->put($permission->code, true);
            } else {
                $codes->forget($permission->code);
            }
        }

        return $codes->keys()->values();
    }

    public function canAccessShop(int $shopId): bool
    {
        return $this->isSuperAdmin() || $this->shops()->wherePivot('is_active', true)->whereKey($shopId)->exists();
    }

    public function canAccessGodown(int $godownId): bool
    {
        return $this->isSuperAdmin() || $this->godowns()->wherePivot('is_active', true)->whereKey($godownId)->exists();
    }
}

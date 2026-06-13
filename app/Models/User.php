<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'first_name', 'last_name', 'email', 'phone', 'password', 'role', 'role_id', 'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    /** Admin = punya role custom apa pun, atau kolom role legacy 'admin'. */
    public function isAdmin(): bool
    {
        return $this->getRawOriginal('role') === 'admin' || ! is_null($this->role_id);
    }

    public function isSuperAdmin(): bool
    {
        return optional($this->roleModel())->slug === 'super-admin'
            || $this->getRawOriginal('role') === 'admin';
    }

    /**
     * Selalu kembalikan objek Role (dari relasi) atau null — menghindari
     * ambiguitas antara kolom legacy `role` (string) dan relasi `role()`.
     */
    public function roleModel(): ?Role
    {
        if (! $this->role_id) {
            return null;
        }
        return $this->relationLoaded('role')
            ? $this->getRelationValue('role')
            : $this->role()->with('permissions')->first();
    }

    /** Cek apakah user punya permission tertentu (super admin selalu true). */
    public function hasPermission(string $slug): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }
        $role = $this->roleModel();
        return $role ? $role->hasPermission($slug) : false;
    }

    public function getNameAttribute(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }
}

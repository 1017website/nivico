<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Role extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'slug', 'description', 'is_locked'];

    protected $casts = ['is_locked' => 'boolean'];

    protected static function booted(): void
    {
        static::saving(function (Role $r) {
            if (empty($r->slug)) {
                $r->slug = Str::slug($r->name);
            }
        });
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function hasPermission(string $slug): bool
    {
        return $this->permissions->contains('slug', $slug);
    }
}

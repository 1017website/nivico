<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'icon', 'sort_order', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    protected static function booted(): void
    {
        static::saving(function (Category $c) {
            if (empty($c->slug)) {
                $c->slug = Str::slug($c->name);
            }
        });
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }
}

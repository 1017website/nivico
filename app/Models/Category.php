<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory, Auditable, SoftDeletes;

    protected $fillable = ['name', 'slug', 'icon', 'sort_order', 'is_active',
        'created_by', 'updated_by', 'deleted_by',
    ];

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

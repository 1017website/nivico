<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id', 'name', 'slug', 'sku', 'price', 'old_price', 'badge',
        'description', 'image', 'stock', 'weight', 'rating', 'rating_count', 'sold',
        'is_flash_sale', 'is_active',
    ];

    protected $casts = [
        'is_flash_sale' => 'boolean',
        'is_active' => 'boolean',
        'rating' => 'float',
    ];

    protected static function booted(): void
    {
        static::saving(function (Product $p) {
            if (empty($p->slug)) {
                $p->slug = Str::slug($p->name).'-'.Str::lower(Str::random(4));
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }

    // persentase diskon
    public function getDiscountPercentAttribute(): ?int
    {
        if (! $this->old_price || $this->old_price <= $this->price) {
            return null;
        }
        return (int) round((1 - $this->price / $this->old_price) * 100);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}

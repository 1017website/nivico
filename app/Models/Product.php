<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, Auditable, SoftDeletes;

    protected $fillable = [
        'category_id', 'name', 'slug', 'sku', 'price', 'old_price', 'badge',
        'description', 'image', 'stock', 'weight', 'rating', 'rating_count', 'sold',
        'is_flash_sale', 'is_active', 'has_variants',
        'created_by', 'updated_by', 'deleted_by',
    ];

    protected $casts = [
        'is_flash_sale' => 'boolean',
        'is_active' => 'boolean',
        'has_variants' => 'boolean',
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

    public function variants()
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order');
    }

    public function activeVariants()
    {
        return $this->variants()->where('is_active', true);
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }

    /** Harga terendah: dari varian aktif bila bervarian, jika tidak dari kolom price. */
    public function getMinPriceAttribute(): int
    {
        if ($this->has_variants) {
            $min = $this->variants->where('is_active', true)->min('price');
            return (int) ($min ?? $this->price);
        }
        return (int) $this->price;
    }

    /** Harga tertinggi varian aktif (untuk tampilan rentang harga). */
    public function getMaxPriceAttribute(): int
    {
        if ($this->has_variants) {
            $max = $this->variants->where('is_active', true)->max('price');
            return (int) ($max ?? $this->price);
        }
        return (int) $this->price;
    }

    /** Total stok: jumlah stok varian aktif bila bervarian, jika tidak kolom stock. */
    public function getTotalStockAttribute(): int
    {
        if ($this->has_variants) {
            return (int) $this->variants->where('is_active', true)->sum('stock');
        }
        return (int) $this->stock;
    }

    /** Apakah harga varian beragam (untuk menampilkan "mulai dari" / rentang). */
    public function hasPriceRange(): bool
    {
        return $this->has_variants && $this->min_price !== $this->max_price;
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

<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Services\ProductDiscountService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use Auditable, HasFactory, SoftDeletes;

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

    /** Urutkan produk dengan stok tersedia di atas dan stok kosong di bawah. */
    public function scopeAvailableFirst($q)
    {
        return $q->orderByRaw('
            CASE
                WHEN products.has_variants = 1 THEN
                    CASE
                        WHEN COALESCE((
                            SELECT SUM(pv.stock)
                            FROM product_variants pv
                            WHERE pv.product_id = products.id
                              AND pv.is_active = 1
                              AND pv.deleted_at IS NULL
                        ), 0) > 0 THEN 0
                        ELSE 1
                    END
                WHEN COALESCE(products.stock, 0) > 0 THEN 0
                ELSE 1
            END ASC
        ');
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

    /** Apakah diskon produk dari admin sedang berlaku pada produk ini. */
    public function hasActiveDiscount(): bool
    {
        return app(ProductDiscountService::class)->appliesTo($this);
    }

    /** Terapkan diskon admin pada harga dasar tertentu milik produk ini. */
    public function discountedPrice(int $basePrice): int
    {
        return app(ProductDiscountService::class)->priceFor($this, $basePrice);
    }

    public function getEffectivePriceAttribute(): int
    {
        return $this->discountedPrice((int) $this->price);
    }

    public function getEffectiveMinPriceAttribute(): int
    {
        return $this->discountedPrice($this->min_price);
    }

    public function getEffectiveMaxPriceAttribute(): int
    {
        return $this->discountedPrice($this->max_price);
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
        if ($this->hasActiveDiscount()) {
            return app(ProductDiscountService::class)->percentage();
        }

        if (! $this->old_price || $this->old_price <= $this->price) {
            return null;
        }

        return (int) round((1 - $this->price / $this->old_price) * 100);
    }

    /** Deskripsi yang selalu siap ditampilkan, termasuk untuk data katalog lama. */
    public function getDisplayDescriptionAttribute(): string
    {
        $description = trim((string) $this->description);

        if ($description !== '') {
            return $description;
        }

        return "{$this->name} adalah produk elektronik pilihan NIVICO untuk kebutuhan rumah, usaha, dan aktivitas sehari-hari. Hubungi kami apabila Anda memerlukan detail spesifikasi tambahan.";
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}

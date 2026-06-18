<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use HasFactory, Auditable, SoftDeletes;

    protected $fillable = [
        'product_id', 'name', 'sku', 'price', 'old_price', 'stock',
        'weight', 'image', 'sold', 'sort_order', 'is_active',
        'created_by', 'updated_by', 'deleted_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }

    /** Persentase diskon bila old_price lebih tinggi dari price. */
    public function getDiscountPercentAttribute(): ?int
    {
        if (! $this->old_price || $this->old_price <= $this->price) {
            return null;
        }
        return (int) round((1 - $this->price / $this->old_price) * 100);
    }

    /** Berat efektif varian: pakai berat varian, fallback ke berat produk. */
    public function effectiveWeight(): int
    {
        return (int) ($this->weight ?: optional($this->product)->weight ?: config('rajaongkir.default_weight'));
    }

    /** Label audit lebih jelas: sertakan nama produk induk. */
    public function auditLabel(): string
    {
        $parent = optional($this->product)->name;
        return 'Varian '.($parent ? $parent.' - ' : '').$this->name;
    }
}

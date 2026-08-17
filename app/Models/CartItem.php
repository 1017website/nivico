<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = ['cart_id', 'product_id', 'product_variant_id', 'qty'];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    /** Harga efektif: dari varian bila ada, jika tidak dari produk. */
    public function effectivePrice(): int
    {
        $basePrice = (int) ($this->variant->price ?? optional($this->product)->price ?? 0);

        return $this->product ? $this->product->discountedPrice($basePrice) : $basePrice;
    }

    /** Harga sebelum diskon produk untuk kebutuhan tampilan harga coret. */
    public function basePrice(): int
    {
        return (int) ($this->variant->price ?? optional($this->product)->price ?? 0);
    }

    /** Stok efektif untuk item ini. */
    public function effectiveStock(): int
    {
        if ($this->product_variant_id) {
            return (int) ($this->variant->stock ?? 0);
        }

        return (int) (optional($this->product)->stock ?? 0);
    }

    /** Berat efektif (gram). */
    public function effectiveWeight(): int
    {
        $w = $this->variant?->weight ?: optional($this->product)->weight;

        return (int) ($w ?: config('rajaongkir.default_weight'));
    }

    /** Dimensi kemasan efektif (cm), memakai varian lalu fallback ke produk. */
    public function effectiveDimensions(): array
    {
        return [
            'length' => (int) ($this->variant?->length ?: optional($this->product)->length ?: 0),
            'width' => (int) ($this->variant?->width ?: optional($this->product)->width ?: 0),
            'height' => (int) ($this->variant?->height ?: optional($this->product)->height ?: 0),
        ];
    }

    /**
     * Berat yang ditagihkan kurir: nilai terbesar antara berat aktual dan
     * berat volumetrik. Bila dimensi belum lengkap, gunakan berat aktual.
     */
    public function shippingWeight(): int
    {
        $actualWeight = $this->effectiveWeight();
        $dimensions = $this->effectiveDimensions();

        if (in_array(0, $dimensions, true)) {
            return $actualWeight;
        }

        $divisor = max(1, (int) config('rajaongkir.dimensional_divisor', 6000));
        $volumetricWeight = (int) ceil(
            ($dimensions['length'] * $dimensions['width'] * $dimensions['height'] * 1000) / $divisor
        );

        return max($actualWeight, $volumetricWeight);
    }

    /** Label tampilan: nama produk + varian. */
    public function displayName(): string
    {
        $name = optional($this->product)->name ?? '';

        return $this->variant ? $name.' — '.$this->variant->name : $name;
    }
}

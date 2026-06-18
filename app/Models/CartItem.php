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

    /** Label tampilan: nama produk + varian. */
    public function displayName(): string
    {
        $name = optional($this->product)->name ?? '';
        return $this->variant ? $name.' — '.$this->variant->name : $name;
    }
}

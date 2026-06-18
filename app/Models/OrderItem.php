<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use Auditable, SoftDeletes;

    protected $fillable = [
        'order_id', 'product_id', 'product_variant_id', 'product_name', 'variation_name',
        'sku', 'image', 'price', 'qty', 'subtotal',
        'created_by', 'updated_by', 'deleted_by',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}

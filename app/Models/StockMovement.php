<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = [
        'product_id', 'type', 'qty_change', 'stock_before', 'stock_after',
        'reason', 'reference', 'user_id', 'user_name',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'adjustment' => 'Penyesuaian',
            'opname'     => 'Stock Opname',
            'sale'       => 'Penjualan',
            'restock'    => 'Pengembalian',
            'initial'    => 'Stok Awal',
            default      => ucfirst($this->type),
        };
    }
}

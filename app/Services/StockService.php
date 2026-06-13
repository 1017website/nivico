<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Sesuaikan stok produk dengan delta (+/-), catat ke stock_movements.
     * Mengembalikan StockMovement yang dibuat.
     */
    public function adjust(int $productId, int $delta, string $type = 'adjustment', ?string $reason = null, ?string $reference = null): StockMovement
    {
        return DB::transaction(function () use ($productId, $delta, $type, $reason, $reference) {
            $product = Product::lockForUpdate()->findOrFail($productId);

            $before = (int) $product->stock;
            $after  = max(0, $before + $delta);
            $applied = $after - $before; // delta sebenarnya setelah clamp ke 0

            $product->update(['stock' => $after]);

            return StockMovement::create([
                'product_id'   => $product->id,
                'type'         => $type,
                'qty_change'   => $applied,
                'stock_before' => $before,
                'stock_after'  => $after,
                'reason'       => $reason,
                'reference'    => $reference,
                'user_id'      => Auth::id(),
                'user_name'    => optional(Auth::user())->name,
            ]);
        });
    }

    /**
     * Set stok ke nilai absolut (dipakai stock opname).
     */
    public function setStock(int $productId, int $physicalCount, string $reference, ?string $reason = null): StockMovement
    {
        return DB::transaction(function () use ($productId, $physicalCount, $reference, $reason) {
            $product = Product::lockForUpdate()->findOrFail($productId);

            $before = (int) $product->stock;
            $after  = max(0, $physicalCount);
            $delta  = $after - $before;

            $product->update(['stock' => $after]);

            return StockMovement::create([
                'product_id'   => $product->id,
                'type'         => 'opname',
                'qty_change'   => $delta,
                'stock_before' => $before,
                'stock_after'  => $after,
                'reason'       => $reason ?? 'Stock opname',
                'reference'    => $reference,
                'user_id'      => Auth::id(),
                'user_name'    => optional(Auth::user())->name,
            ]);
        });
    }
}

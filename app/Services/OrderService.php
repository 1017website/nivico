<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Membuat order dari keranjang dengan stock booking anti-race-condition
 * (lockForUpdate) — pola yang sama seperti project sebelumnya.
 */
class OrderService
{
    public function __construct(protected CartService $cartService) {}

    /** Total berat keranjang dalam gram (untuk RajaOngkir). */
    public function cartWeight($cart): int
    {
        $w = (int) $cart->items->sum(fn ($i) => $i->effectiveWeight() * $i->qty);
        return max(1, $w);
    }


    public function createFromCart(array $data): Order
    {
        $summary = $this->cartService->summary($data['shipping_cost'] ?? null);
        $cart = $summary['cart'];

        if ($cart->items->isEmpty()) {
            throw ValidationException::withMessages(['cart' => 'Keranjang kosong.']);
        }

        return DB::transaction(function () use ($cart, $summary, $data) {
            $productIds = $cart->items->pluck('product_id')->unique()->all();
            $variantIds = $cart->items->pluck('product_variant_id')->filter()->unique()->all();

            // kunci baris produk agar stok tidak balapan
            $locked = Product::whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // kunci baris varian yang dipakai
            $lockedVariants = collect();
            if (! empty($variantIds)) {
                $lockedVariants = \App\Models\ProductVariant::whereIn('id', $variantIds)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');
            }

            // validasi stok (varian bila ada, jika tidak produk)
            foreach ($cart->items as $item) {
                $p = $locked[$item->product_id] ?? null;
                if (! $p || ! $p->is_active) {
                    throw ValidationException::withMessages(['stock' => 'Produk tidak tersedia.']);
                }

                if ($item->product_variant_id) {
                    $v = $lockedVariants[$item->product_variant_id] ?? null;
                    if (! $v || ! $v->is_active) {
                        throw ValidationException::withMessages(['stock' => "Varian {$p->name} tidak tersedia."]);
                    }
                    if ($v->stock < $item->qty) {
                        throw ValidationException::withMessages(['stock' => "Stok {$p->name} ({$v->name}) tidak mencukupi (sisa {$v->stock})."]);
                    }
                } else {
                    if ($p->stock < $item->qty) {
                        throw ValidationException::withMessages(['stock' => "Stok {$p->name} tidak mencukupi (sisa {$p->stock})."]);
                    }
                }
            }

            $order = Order::create([
                'order_number'    => $this->generateNumber(),
                'user_id'         => $data['user_id'] ?? null,
                'recipient_name'  => $data['recipient_name'],
                'phone'           => $data['phone'],
                'email'           => $data['email'] ?? null,
                'address'         => $data['address'],
                'province'        => $data['province'] ?? null,
                'city'            => $data['city'] ?? null,
                'district'        => $data['district'] ?? null,
                'postal_code'     => $data['postal_code'] ?? null,
                'destination_id'  => $data['destination_id'] ?? null,
                'note'            => $data['note'] ?? null,
                'shipping_method' => $data['shipping_method'],
                'shipping_courier'=> $data['shipping_courier'] ?? null,
                'shipping_service'=> $data['shipping_service'] ?? null,
                'shipping_etd'    => $data['shipping_etd'] ?? null,
                'shipping_weight' => $data['shipping_weight'] ?? $this->cartWeight($cart),
                'shipping_cost'   => $summary['freeShip'] ? 0 : $summary['shipping'],
                'payment_method'  => $data['payment_method'],
                'payment_gateway' => $data['payment_gateway'] ?? 'manual_transfer',
                'payment_status'  => 'unpaid',
                'bank_account_id' => $data['bank_account_id'] ?? null,
                'subtotal'        => $summary['subtotal'],
                'discount'        => $summary['discount'],
                'promo_id'        => $cart->promo_id,
                'total'           => $summary['total'],
                'status'          => 'pending',
                'expires_at'      => now()->addHours(24),
            ]);

            foreach ($cart->items as $item) {
                $p = $locked[$item->product_id];
                $v = $item->product_variant_id ? ($lockedVariants[$item->product_variant_id] ?? null) : null;

                // Harga/SKU/nama dari varian bila ada, jika tidak dari produk
                $unitPrice = (int) ($v?->price ?? $p->price);
                $sku       = $v?->sku ?: $p->sku;
                $varName   = $v?->name;

                $order->items()->create([
                    'product_id'         => $p->id,
                    'product_variant_id' => $v?->id,
                    'product_name'       => $p->name,
                    'variation_name'     => $varName,
                    'sku'                => $sku,
                    'image'              => $v?->image ?: $p->image,
                    'price'              => $unitPrice,
                    'qty'                => $item->qty,
                    'subtotal'           => $unitPrice * $item->qty,
                ]);

                // potong stok & tambah terjual
                if ($v) {
                    $before = (int) $v->stock;
                    $v->decrement('stock', $item->qty);
                    $v->increment('sold', $item->qty);
                    // jaga konsistensi: sold produk induk ikut naik
                    $p->increment('sold', $item->qty);
                } else {
                    $before = (int) $p->stock;
                    $p->decrement('stock', $item->qty);
                    $p->increment('sold', $item->qty);
                }

                \App\Models\StockMovement::create([
                    'product_id'   => $p->id,
                    'type'         => 'sale',
                    'qty_change'   => -1 * (int) $item->qty,
                    'stock_before' => $before,
                    'stock_after'  => max(0, $before - (int) $item->qty),
                    'reason'       => $v ? 'Penjualan (varian: '.$v->name.')' : 'Penjualan',
                    'reference'    => $order->order_number,
                    'user_id'      => $order->user_id,
                ]);
            }

            // kosongkan keranjang
            $cart->items()->delete();
            $cart->update(['promo_id' => null]);

            return $order;
        });
    }

    protected function generateNumber(): string
    {
        do {
            $num = config('shop.order_prefix').'-'.now()->format('Y').'-'.str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (Order::where('order_number', $num)->exists());

        return $num;
    }

    /**
     * Lepas order pending yang kedaluwarsa & kembalikan stok.
     */
    public function releaseExpired(): int
    {
        $expired = Order::where('status', 'pending')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            // jangan batalkan order yang sudah dibayar / sudah upload bukti / menunggu verifikasi
            ->where('payment_status', 'unpaid')
            ->whereNull('payment_proof')
            ->with('items')
            ->get();

        foreach ($expired as $order) {
            DB::transaction(function () use ($order) {
                foreach ($order->items as $item) {
                    if ($item->product_variant_id) {
                        \App\Models\ProductVariant::where('id', $item->product_variant_id)->update([
                            'stock' => DB::raw("stock + {$item->qty}"),
                            'sold'  => DB::raw("GREATEST(sold - {$item->qty}, 0)"),
                        ]);
                        // sold produk induk ikut dikoreksi
                        if ($item->product_id) {
                            Product::where('id', $item->product_id)->update([
                                'sold' => DB::raw("GREATEST(sold - {$item->qty}, 0)"),
                            ]);
                        }
                    } elseif ($item->product_id) {
                        Product::where('id', $item->product_id)->update([
                            'stock' => DB::raw("stock + {$item->qty}"),
                            'sold'  => DB::raw("GREATEST(sold - {$item->qty}, 0)"),
                        ]);
                    }
                }
                $order->update(['status' => 'cancelled']);
            });
        }

        return $expired->count();
    }
}

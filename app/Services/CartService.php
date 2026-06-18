<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Promo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

/**
 * Mengelola keranjang. Mendukung guest (via session_id) maupun user login.
 * Saat user login, keranjang guest digabung otomatis.
 *
 * Mendukung produk bervarian: tiap kombinasi (product_id, product_variant_id)
 * adalah baris keranjang terpisah. Harga & stok dibaca dari varian bila ada.
 */
class CartService
{
    public function current(): Cart
    {
        if (Auth::check()) {
            $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);
            $this->mergeGuestCart($cart);
            return $cart->load('items.product', 'items.variant', 'promo');
        }

        $sid = Session::getId();
        return Cart::with('items.product', 'items.variant', 'promo')
            ->firstOrCreate(['session_id' => $sid]);
    }

    protected function mergeGuestCart(Cart $userCart): void
    {
        $sid = Session::getId();
        $guest = Cart::where('session_id', $sid)->whereNull('user_id')->first();
        if (! $guest || $guest->id === $userCart->id) {
            return;
        }
        foreach ($guest->items as $gi) {
            $item = $userCart->items()->firstOrNew([
                'product_id'         => $gi->product_id,
                'product_variant_id' => $gi->product_variant_id,
            ]);
            $merged = ($item->qty ?? 0) + $gi->qty;
            // jangan melebihi stok tersedia (varian bila ada, jika tidak produk)
            $stock = $gi->product_variant_id
                ? optional($gi->variant)->stock
                : optional($gi->product)->stock;
            $item->qty = is_null($stock) ? $merged : max(1, min($merged, $stock));
            $item->save();
        }
        $guest->delete();
    }

    public function add(int $productId, int $qty = 1, ?int $variantId = null): array
    {
        $product = Product::active()->findOrFail($productId);

        // Produk bervarian wajib menyertakan varian yang valid & milik produk ini
        if ($product->has_variants) {
            if (! $variantId) {
                throw ValidationException::withMessages(['variant' => 'Silakan pilih varian terlebih dahulu.']);
            }
            $variant = ProductVariant::where('id', $variantId)
                ->where('product_id', $product->id)
                ->where('is_active', true)
                ->first();
            if (! $variant) {
                throw ValidationException::withMessages(['variant' => 'Varian tidak tersedia.']);
            }
            $stock = (int) $variant->stock;
        } else {
            // Produk single: abaikan variantId bila terkirim
            $variantId = null;
            $stock = (int) $product->stock;
        }

        $cart = $this->current();

        $item = $cart->items()->firstOrNew([
            'product_id'         => $product->id,
            'product_variant_id' => $variantId,
        ]);
        $requested = ($item->qty ?? 0) + max(1, $qty);
        $capped = $requested > $stock;
        $item->qty = max(1, min($requested, $stock));
        $item->save();

        return ['capped' => $capped, 'qty' => $item->qty, 'stock' => $stock];
    }

    public function updateQty(int $itemId, int $qty): void
    {
        $cart = $this->current();
        $item = $cart->items()->with(['product', 'variant'])->findOrFail($itemId);
        if ($qty <= 0) {
            $item->delete();
        } else {
            $stock = $item->effectiveStock();
            $item->update(['qty' => $stock <= 0 ? 1 : min($qty, max(1, $stock))]);
        }
    }

    public function remove(int $itemId): void
    {
        $this->current()->items()->where('id', $itemId)->delete();
    }

    public function clear(): void
    {
        $cart = $this->current();
        $cart->items()->delete();
        $cart->update(['promo_id' => null]);
    }

    public function applyPromo(string $code): array
    {
        $cart = $this->current();
        $promo = Promo::active()->whereRaw('UPPER(code) = ?', [strtoupper(trim($code))])->first();

        if (! $promo) {
            return ['ok' => false, 'message' => 'Kode promo tidak valid'];
        }

        $subtotal = $cart->subtotal();
        if ($subtotal < $promo->min_purchase) {
            return ['ok' => false, 'message' => 'Min. belanja Rp'.number_format($promo->min_purchase, 0, ',', '.')];
        }

        $cart->update(['promo_id' => $promo->id]);
        return ['ok' => true, 'message' => 'Promo '.$promo->code.' diterapkan', 'promo' => $promo];
    }

    public function removePromo(): void
    {
        $this->current()->update(['promo_id' => null]);
    }

    /**
     * Ringkasan biaya keranjang.
     */
    public function summary(?int $shippingCost = null): array
    {
        $cart = $this->current();
        $subtotal = $cart->subtotal();
        $shipping = $shippingCost ?? ($cart->items->isNotEmpty() ? config('shop.default_ongkir') : 0);

        $discount = 0;
        $freeShip = false;
        if ($cart->promo) {
            $calc = $cart->promo->calculate($subtotal, $shipping);
            $discount = $calc['discount'];
            $freeShip = $calc['free_shipping'];
        }

        $total = max(0, $subtotal + $shipping - $discount);

        return compact('subtotal', 'shipping', 'discount', 'freeShip', 'total') + [
            'cart' => $cart,
            'qty' => $cart->totalQty(),
        ];
    }
}

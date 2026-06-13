<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Product;
use App\Models\Promo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/**
 * Mengelola keranjang. Mendukung guest (via session_id) maupun user login.
 * Saat user login, keranjang guest digabung otomatis.
 */
class CartService
{
    public function current(): Cart
    {
        if (Auth::check()) {
            $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);
            $this->mergeGuestCart($cart);
            return $cart->load('items.product', 'promo');
        }

        $sid = Session::getId();
        return Cart::with('items.product', 'promo')
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
            $item = $userCart->items()->firstOrNew(['product_id' => $gi->product_id]);
            $merged = ($item->qty ?? 0) + $gi->qty;
            // jangan melebihi stok tersedia
            $stock = optional($gi->product)->stock;
            $item->qty = is_null($stock) ? $merged : max(1, min($merged, $stock));
            $item->save();
        }
        $guest->delete();
    }

    public function add(int $productId, int $qty = 1): array
    {
        $product = Product::active()->findOrFail($productId);
        $cart = $this->current();

        $item = $cart->items()->firstOrNew(['product_id' => $product->id]);
        $requested = ($item->qty ?? 0) + max(1, $qty);
        $capped = $requested > $product->stock;
        $item->qty = max(1, min($requested, $product->stock));
        $item->save();

        return ['capped' => $capped, 'qty' => $item->qty, 'stock' => $product->stock];
    }

    public function updateQty(int $itemId, int $qty): void
    {
        $cart = $this->current();
        $item = $cart->items()->with('product')->findOrFail($itemId);
        if ($qty <= 0) {
            $item->delete();
        } else {
            $stock = optional($item->product)->stock;
            $item->update(['qty' => is_null($stock) ? $qty : min($qty, max(1, $stock))]);
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

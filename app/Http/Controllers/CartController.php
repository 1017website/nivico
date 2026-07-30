<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(protected CartService $cart) {}

    public function index()
    {
        $summary = $this->cart->summary();
        return view('pages.cart', $summary);
    }

    public function add(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'qty'        => 'nullable|integer|min:1',
            'variant_id' => 'nullable|integer|exists:product_variants,id',
        ]);

        try {
            $result = $this->cart->add(
                $data['product_id'],
                $data['qty'] ?? 1,
                $data['variant_id'] ?? null
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            // mis. "Silakan pilih varian" / "Varian tidak tersedia"
            return back()->with('error', $e->validator->errors()->first());
        }

        if ($request->input('redirect') === 'checkout') {
            return redirect()->route('checkout');
        }

        if ($result['capped']) {
            return back()->with('toast', '⚠ Jumlah disesuaikan dengan sisa stok ('.$result['qty'].')');
        }

        return back()->with('toast', '✓ Produk ditambahkan ke keranjang');
    }

    public function update(Request $request, int $item)
    {
        $request->validate(['qty' => 'required|integer|min:0']);
        $this->cart->updateQty($item, (int) $request->qty);
        return back();
    }

    public function remove(int $item)
    {
        $this->cart->remove($item);
        return back()->with('toast', 'Item dihapus dari keranjang');
    }

    public function clear()
    {
        $this->cart->clear();
        return back()->with('toast', 'Keranjang dikosongkan');
    }

    public function applyPromo(Request $request)
    {
        $request->validate(['code' => 'required|string']);
        $res = $this->cart->applyPromo($request->code);

        if ($request->expectsJson()) {
            $promo = $res['promo'] ?? null;

            return response()->json([
                'ok' => $res['ok'],
                'message' => $res['message'],
                'promo' => $promo ? [
                    'code' => $promo->code,
                    'title' => $promo->title,
                    'type' => $promo->type,
                    'value' => (int) $promo->value,
                    'max_discount' => $promo->max_discount ? (int) $promo->max_discount : null,
                    'min_purchase' => (int) $promo->min_purchase,
                ] : null,
            ], $res['ok'] ? 200 : 422);
        }

        return back()->with($res['ok'] ? 'toast' : 'error', $res['message']);
    }

    public function removePromo(Request $request)
    {
        $this->cart->removePromo();

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Promo dihapus',
            ]);
        }

        return back();
    }
}

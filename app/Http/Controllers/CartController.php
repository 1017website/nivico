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
        ]);

        $this->cart->add($data['product_id'], $data['qty'] ?? 1);

        if ($request->input('redirect') === 'checkout') {
            return redirect()->route('checkout');
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
        return back()->with($res['ok'] ? 'toast' : 'error', $res['message']);
    }

    public function removePromo()
    {
        $this->cart->removePromo();
        return back();
    }
}

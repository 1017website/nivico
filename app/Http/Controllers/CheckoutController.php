<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Order;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\RajaOngkirService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function __construct(
        protected CartService $cart,
        protected OrderService $orders,
        protected RajaOngkirService $rajaongkir,
    ) {}

    public function index()
    {
        $summary = $this->cart->summary(0); // ongkir dihitung dinamis di langkah berikutnya
        if ($summary['cart']->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Keranjang kosong.');
        }

        $weight       = $this->orders->cartWeight($summary['cart']);
        $useApi       = $this->rajaongkir->isConfigured();
        $fallbackShip = $this->rajaongkir->fallbackOptions();
        $banks        = BankAccount::active()->orderBy('sort_order')->get();
        $midtransOn   = app(\App\Services\MidtransService::class)->isConfigured();

        return view('pages.checkout', $summary + compact('weight', 'useApi', 'fallbackShip', 'banks', 'midtransOn'));
    }

    /** AJAX: cari kota/kecamatan tujuan. */
    public function searchDestination(Request $request)
    {
        $request->validate(['q' => 'required|string|min:3']);
        return response()->json($this->rajaongkir->searchDestination($request->q));
    }

    /** AJAX: hitung ongkir berdasarkan tujuan + berat keranjang. */
    public function calculateShipping(Request $request)
    {
        $request->validate(['destination_id' => 'required|string']);
        $summary = $this->cart->summary(0);
        $weight  = $this->orders->cartWeight($summary['cart']);

        $options = $this->rajaongkir->calculateCost($request->destination_id, $weight);
        if (empty($options)) {
            $options = $this->rajaongkir->fallbackOptions();
        }

        return response()->json(['weight' => $weight, 'options' => $options]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'recipient_name'   => 'required|string|max:120',
            'phone'            => 'required|string|max:30',
            'address'          => 'required|string',
            'province'         => 'nullable|string|max:80',
            'city'             => 'nullable|string|max:80',
            'district'         => 'nullable|string|max:80',
            'postal_code'      => 'nullable|string|max:10',
            'destination_id'   => 'nullable|string|max:40',
            'note'             => 'nullable|string|max:500',

            // shipping dikirim sebagai "courier|service|cost|etd|description"
            'shipping_option'  => 'required|string',

            // payment
            'payment_gateway'  => 'required|in:manual_transfer,midtrans',
            'bank_account_id'  => 'required_if:payment_gateway,manual_transfer|nullable|exists:bank_accounts,id',
        ]);

        $parts   = explode('|', $data['shipping_option']);
        $courier = $parts[0] ?? '';
        $service = $parts[1] ?? '';
        $cost    = (int) ($parts[2] ?? config('shop.default_ongkir'));
        $etd     = $parts[3] ?? '';

        $data['shipping_method']  = trim(strtoupper($courier).' '.$service);
        $data['shipping_courier'] = $courier;
        $data['shipping_service'] = $service;
        $data['shipping_etd']     = $etd;
        $data['shipping_cost']    = $cost;
        $data['payment_method']   = $data['payment_gateway'] === 'midtrans' ? 'midtrans' : 'transfer';
        $data['user_id']          = auth()->id();

        try {
            $order = $this->orders->createFromCart($data);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return redirect()->route('payment.show', $order->order_number);
    }

    /** Halaman sukses (juga finish redirect Midtrans). */
    public function success(string $orderNumber)
    {
        $order = Order::with('items')->where('order_number', $orderNumber)->firstOrFail();
        return view('pages.success', compact('order'));
    }
}

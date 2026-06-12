<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    public function __construct(protected MidtransService $midtrans) {}

    /** Halaman pembayaran setelah checkout. */
    public function show(string $orderNumber)
    {
        $order = Order::with('items', 'bankAccount')->where('order_number', $orderNumber)->firstOrFail();

        // batasi akses: pemilik order atau guest yang baru membuat (cek via session optional)
        if ($order->user_id && auth()->id() !== $order->user_id && ! optional(auth()->user())->isAdmin()) {
            abort(403);
        }

        $snapToken = null;
        if ($order->payment_gateway === 'midtrans' && ! $order->isPaid()) {
            $snapToken = $order->snap_token ?: $this->midtrans->createSnapToken($order->fresh('items'));
        }

        return view('pages.payment', compact('order', 'snapToken'));
    }

    /** Upload bukti transfer manual. */
    public function uploadProof(Request $request, string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();
        $request->validate([
            'proof' => 'required|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        // hapus bukti lama bila ada
        if ($order->payment_proof) {
            $old = str_replace('/storage/', '', $order->payment_proof);
            Storage::disk('public')->delete($old);
        }

        $path = $request->file('proof')->store('payment-proofs', 'public');
        $order->update([
            'payment_proof'  => Storage::url($path),
            'payment_status' => 'pending', // menunggu verifikasi admin
        ]);

        return back()->with('toast', '✓ Bukti transfer terkirim. Menunggu verifikasi admin.');
    }

    /** Webhook notifikasi Midtrans (server-to-server). */
    public function midtransNotify(Request $request)
    {
        $payload = $request->all();

        if (! $this->midtrans->verifySignature($payload)) {
            Log::warning('Midtrans signature tidak valid', ['order' => $payload['order_id'] ?? null]);
            return response()->json(['message' => 'invalid signature'], 403);
        }

        // order_id Midtrans = "{order_number}-{timestamp}"
        $orderNumber = explode('-', $payload['order_id'] ?? '')[0] ?? null;
        // order_number kita berformat NVC-YYYY-NNNNN, jadi gabung 3 segmen pertama
        $segments = explode('-', $payload['order_id'] ?? '');
        if (count($segments) >= 3) {
            $orderNumber = $segments[0].'-'.$segments[1].'-'.$segments[2];
        }

        $order = Order::where('order_number', $orderNumber)->first();
        if (! $order) {
            return response()->json(['message' => 'order not found'], 404);
        }

        $status = $this->midtrans->mapStatus($payload);
        $update = [
            'payment_status'          => $status,
            'midtrans_transaction_id' => $payload['transaction_id'] ?? $order->midtrans_transaction_id,
            'midtrans_payment_type'   => $payload['payment_type'] ?? $order->midtrans_payment_type,
        ];

        if ($status === 'paid') {
            $update['paid_at'] = now();
            $update['status']  = 'paid';
        } elseif (in_array($status, ['expired', 'failed'])) {
            // kembalikan stok bila batal/expired
            app(\App\Services\OrderService::class);
            $update['status'] = 'cancelled';
        }

        $order->update($update);

        // kembalikan stok jika dibatalkan
        if (in_array($status, ['expired', 'failed'])) {
            $this->restock($order);
        }

        return response()->json(['message' => 'ok']);
    }

    protected function restock(Order $order): void
    {
        foreach ($order->items as $item) {
            if ($item->product_id) {
                \App\Models\Product::where('id', $item->product_id)->update([
                    'stock' => \Illuminate\Support\Facades\DB::raw("stock + {$item->qty}"),
                    'sold'  => \Illuminate\Support\Facades\DB::raw("GREATEST(sold - {$item->qty}, 0)"),
                ]);
            }
        }
    }
}

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
    public function show(Request $request, string $orderNumber)
    {
        $order = Order::with('items', 'bankAccount')->where('order_number', $orderNumber)->firstOrFail();

        $this->authorizeAccess($request, $order);

        $snapToken = null;
        if ($order->payment_gateway === 'midtrans' && ! $order->isPaid()) {
            $snapToken = $order->snap_token ?: $this->midtrans->createSnapToken($order->fresh('items'));
        }

        return view('pages.payment', compact('order', 'snapToken'));
    }

    /**
     * Izinkan akses bila: admin, pemilik order (login), atau guest yang
     * membuat order tsb (tercatat di session 'my_orders').
     */
    protected function authorizeAccess(Request $request, Order $order): void
    {
        $user = auth()->user();

        if ($user && $user->isAdmin()) {
            return;
        }
        if ($order->user_id && $user && $user->id === $order->user_id) {
            return;
        }
        $owned = (array) $request->session()->get('my_orders', []);
        if (in_array($order->order_number, $owned, true)) {
            return;
        }

        abort(403, 'Anda tidak memiliki akses ke pesanan ini.');
    }

    /** Upload bukti transfer manual. */
    public function uploadProof(Request $request, string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();
        $this->authorizeAccess($request, $order);
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

        $midtransOrderId = $payload['order_id'] ?? '';

        // Utamakan lookup via kolom midtrans_order_id (disimpan saat buat Snap token).
        $order = Order::where('midtrans_order_id', $midtransOrderId)->first();

        // Fallback: lepas suffix "-{timestamp}" dari belakang (lebih tahan terhadap
        // perubahan prefix order_number yang bisa mengandung tanda '-').
        if (! $order && $midtransOrderId !== '') {
            $orderNumber = preg_replace('/-\d+$/', '', $midtransOrderId);
            $order = Order::where('order_number', $orderNumber)->first();
        }

        if (! $order) {
            return response()->json(['message' => 'order not found'], 404);
        }

        $status = $this->midtrans->mapStatus($payload);

        // Idempoten: bila status pembayaran tidak berubah, abaikan (notifikasi dobel).
        if ($order->payment_status === $status) {
            return response()->json(['message' => 'ok (no change)']);
        }

        // Jangan proses mundur dari kondisi final 'paid'.
        if ($order->payment_status === 'paid' && $status !== 'refunded') {
            return response()->json(['message' => 'ok (already paid)']);
        }

        $wasCancelled = $order->status === 'cancelled';

        $update = [
            'payment_status'          => $status,
            'midtrans_transaction_id' => $payload['transaction_id'] ?? $order->midtrans_transaction_id,
            'midtrans_payment_type'   => $payload['payment_type'] ?? $order->midtrans_payment_type,
        ];

        if ($status === 'paid') {
            $update['paid_at'] = now();
            $update['status']  = 'paid';
        } elseif (in_array($status, ['expired', 'failed'])) {
            $update['status'] = 'cancelled';
        }

        $order->update($update);

        // Kirim invoice "lunas" ke pembeli.
        if ($status === 'paid') {
            $this->sendInvoice($order->fresh('items'), 'paid');
        }

        // Kembalikan stok hanya bila baru dibatalkan (hindari double restock).
        if (in_array($status, ['expired', 'failed']) && ! $wasCancelled) {
            $this->restock($order);
        }

        return response()->json(['message' => 'ok']);
    }

    /** Kirim email invoice; gagal-aman. */
    protected function sendInvoice(Order $order, string $kind): void
    {
        $to = $order->email ?: optional($order->user)->email;
        if (! $to) {
            return;
        }
        try {
            \Illuminate\Support\Facades\Mail::to($to)->send(new \App\Mail\OrderInvoiceMail($order, $kind));
        } catch (\Throwable $e) {
            Log::error('Gagal kirim invoice', ['order' => $order->order_number, 'msg' => $e->getMessage()]);
        }
    }

    protected function restock(Order $order): void
    {        foreach ($order->items as $item) {
            if ($item->product_id) {
                \App\Models\Product::where('id', $item->product_id)->update([
                    'stock' => \Illuminate\Support\Facades\DB::raw("stock + {$item->qty}"),
                    'sold'  => \Illuminate\Support\Facades\DB::raw("GREATEST(sold - {$item->qty}, 0)"),
                ]);
            }
        }
    }
}

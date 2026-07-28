<?php

namespace App\Http\Controllers;

use App\Mail\OrderInvoiceMail;
use App\Models\Order;
use App\Models\Product;
use App\Services\DuitkuService;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    public function __construct(
        protected MidtransService $midtrans,
        protected DuitkuService $duitku,
    ) {}

    /** Halaman pembayaran setelah checkout. */
    public function show(Request $request, string $orderNumber)
    {
        $order = Order::with('items', 'bankAccount')->where('order_number', $orderNumber)->firstOrFail();

        $this->authorizeAccess($request, $order);

        $snapToken = null;
        $duitkuMethods = [];
        if ($order->payment_gateway === 'midtrans' && ! $order->isPaid()) {
            $snapToken = $order->snap_token ?: $this->midtrans->createSnapToken($order->fresh('items'));
        } elseif ($order->payment_gateway === 'duitku' && ! $order->isPaid()) {
            $duitkuMethods = $this->duitku->getPaymentMethods((int) $order->total);
        }

        return view('pages.payment', compact('order', 'snapToken', 'duitkuMethods'));
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
            'payment_proof' => Storage::url($path),
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
            'payment_status' => $status,
            'midtrans_transaction_id' => $payload['transaction_id'] ?? $order->midtrans_transaction_id,
            'midtrans_payment_type' => $payload['payment_type'] ?? $order->midtrans_payment_type,
        ];

        if ($status === 'paid') {
            $update['paid_at'] = now();
            $update['status'] = 'paid';
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

    /** Buat invoice sesuai channel yang dipilih, lalu buka halaman pembayaran penuh. */
    public function duitkuPay(Request $request, string $orderNumber)
    {
        $order = Order::with('items', 'user')->where('order_number', $orderNumber)->firstOrFail();
        $this->authorizeAccess($request, $order);

        if ($order->payment_gateway !== 'duitku' || $order->isPaid()) {
            return back()->with('error', 'Pesanan ini tidak dapat dibayar melalui Duitku.');
        }

        $data = $request->validate([
            'payment_method' => 'required|string|max:10|regex:/^[A-Za-z0-9]+$/',
        ]);
        $paymentMethod = strtoupper($data['payment_method']);
        $available = collect($this->duitku->getPaymentMethods((int) $order->total))
            ->contains(fn ($method) => strtoupper($method['paymentMethod']) === $paymentMethod);

        if (! $available) {
            return back()->with('error', 'Metode pembayaran tersebut sedang tidak tersedia.');
        }

        $invoice = $this->duitku->createInvoice($order, $paymentMethod);

        if (! $invoice) {
            return back()->with('error', 'Duitku belum dapat membuat pembayaran. Silakan pilih metode lain atau coba kembali.');
        }

        $separator = str_contains($invoice['payment_url'], '?') ? '&' : '?';

        return redirect()->away($invoice['payment_url'].$separator.'lang=id');
    }

    /** Callback pembayaran Duitku (server-to-server, bukan hasil JavaScript). */
    public function duitkuCallback(Request $request)
    {
        $payload = $request->all();

        if (! $this->duitku->verifyCallback($payload)) {
            Log::warning('Duitku callback signature tidak valid', [
                'order' => $payload['merchantOrderId'] ?? null,
            ]);

            return response('Invalid signature', 403);
        }

        $merchantOrderId = (string) ($payload['merchantOrderId'] ?? '');
        $order = Order::with('items')
            ->where('duitku_merchant_order_id', $merchantOrderId)
            ->orWhere('order_number', $merchantOrderId)
            ->first();

        // Invoice lama tetap dikenali bila pelanggan sempat mengganti channel.
        if (! $order && $merchantOrderId !== '') {
            $orderNumber = preg_replace('/-[A-Z0-9]+-\d{6}$/i', '', $merchantOrderId);
            $order = Order::with('items')->where('order_number', $orderNumber)->first();
        }

        if (! $order) {
            return response('Order not found', 404);
        }

        if ($order->payment_gateway !== 'duitku') {
            return response('Invalid payment gateway', 422);
        }

        if ((int) ($payload['amount'] ?? 0) !== (int) $order->total) {
            Log::warning('Duitku callback nominal tidak cocok', [
                'order' => $order->order_number,
                'expected' => $order->total,
                'received' => $payload['amount'] ?? null,
            ]);

            return response('Invalid amount', 422);
        }

        $resultCode = (string) ($payload['resultCode'] ?? '');
        $newStatus = match ($resultCode) {
            '00' => 'paid',
            '01', '02' => 'failed',
            default => 'pending',
        };

        if ($order->payment_status === 'paid' && $newStatus !== 'paid') {
            return response('OK', 200);
        }

        $wasCancelled = $order->status === 'cancelled';
        $update = [
            'payment_status' => $newStatus,
            'duitku_reference' => $payload['reference'] ?? $order->duitku_reference,
            'duitku_payment_method' => $payload['paymentCode'] ?? $order->duitku_payment_method,
            'duitku_publisher_order_id' => $payload['publisherOrderId'] ?? $order->duitku_publisher_order_id,
        ];

        if ($newStatus === 'paid') {
            $update['paid_at'] = now();
            $update['status'] = 'paid';
        } elseif ($newStatus === 'failed') {
            $update['status'] = 'cancelled';
        }

        $changed = $order->payment_status !== $newStatus;
        $order->update($update);

        if ($changed && $newStatus === 'paid') {
            $this->sendInvoice($order->fresh('items'), 'paid');
        }

        if ($changed && $newStatus === 'failed' && ! $wasCancelled) {
            $this->restock($order);
        }

        return response('OK', 200);
    }

    /**
     * Return URL hanya memberi informasi kepada pelanggan. Status pembayaran
     * tetap hanya diubah melalui callback server Duitku.
     */
    public function duitkuReturn(Request $request, string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();
        $this->authorizeAccess($request, $order);

        $message = match ((string) $request->query('resultCode')) {
            '00' => 'Pembayaran diterima. Status pesanan akan diperbarui otomatis.',
            '01' => 'Pembayaran masih menunggu konfirmasi.',
            '02' => 'Pembayaran dibatalkan. Anda dapat mencoba kembali.',
            default => 'Anda kembali dari halaman pembayaran Duitku.',
        };

        return redirect()->route('payment.show', $order->order_number)->with('toast', $message);
    }

    /** Kirim email invoice; gagal-aman. */
    protected function sendInvoice(Order $order, string $kind): void
    {
        $to = $order->email ?: optional($order->user)->email;
        if (! $to) {
            return;
        }
        try {
            Mail::to($to)->send(new OrderInvoiceMail($order, $kind));
        } catch (\Throwable $e) {
            Log::error('Gagal kirim invoice', ['order' => $order->order_number, 'msg' => $e->getMessage()]);
        }
    }

    protected function restock(Order $order): void
    {
        foreach ($order->items as $item) {
            if ($item->product_id) {
                Product::where('id', $item->product_id)->update([
                    'stock' => DB::raw("stock + {$item->qty}"),
                    'sold' => DB::raw("GREATEST(sold - {$item->qty}, 0)"),
                ]);
            }
        }
    }
}

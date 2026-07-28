<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Services\DuitkuService;
use App\Services\IntegrationLogger;
use App\Services\InvoiceMailService;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        $logger = app(IntegrationLogger::class);
        $trace = $logger->create('duitku', 'payment_callback', 'received', [
            'order_number' => $payload['merchantOrderId'] ?? null,
            'reference' => $payload['reference'] ?? null,
            'ip_address' => $request->ip(),
            'message' => 'Callback Duitku diterima dan sedang diverifikasi.',
            'context' => $this->safeDuitkuPayload($payload),
        ]);

        try {
            if (! $this->duitku->verifyCallback($payload)) {
                Log::warning('Duitku callback signature tidak valid', [
                    'order' => $payload['merchantOrderId'] ?? null,
                ]);
                $logger->finish($trace, 'rejected', [
                    'http_status' => 403,
                    'message' => 'Callback ditolak: signature tidak valid.',
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
                $logger->finish($trace, 'rejected', [
                    'http_status' => 404,
                    'message' => 'Callback valid, tetapi pesanan tidak ditemukan.',
                ]);

                return response('Order not found', 404);
            }

            $traceIdentity = [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'reference' => $payload['reference'] ?? $order->duitku_reference,
            ];

            if ($order->payment_gateway !== 'duitku') {
                $logger->finish($trace, 'rejected', array_merge($traceIdentity, [
                    'http_status' => 422,
                    'message' => 'Callback ditolak: gateway pesanan bukan Duitku.',
                ]));

                return response('Invalid payment gateway', 422);
            }

            if ((int) ($payload['amount'] ?? 0) !== (int) $order->total) {
                Log::warning('Duitku callback nominal tidak cocok', [
                    'order' => $order->order_number,
                    'expected' => $order->total,
                    'received' => $payload['amount'] ?? null,
                ]);
                $logger->finish($trace, 'rejected', array_merge($traceIdentity, [
                    'http_status' => 422,
                    'message' => 'Callback ditolak: nominal pembayaran tidak cocok.',
                    'context' => [
                        'expected_amount' => (int) $order->total,
                        'received_amount' => (int) ($payload['amount'] ?? 0),
                    ],
                ]));

                return response('Invalid amount', 422);
            }

            $resultCode = (string) ($payload['resultCode'] ?? '');
            $newStatus = match ($resultCode) {
                '00' => 'paid',
                '01', '02' => 'failed',
                default => 'pending',
            };

            if ($order->payment_status === 'paid' && $newStatus !== 'paid') {
                $logger->finish($trace, 'ignored', array_merge($traceIdentity, [
                    'http_status' => 200,
                    'message' => 'Callback diabaikan agar status lunas tidak mundur.',
                    'context' => [
                        'previous_payment_status' => $order->payment_status,
                        'callback_payment_status' => $newStatus,
                    ],
                ]));

                return response('OK', 200);
            }

            $wasCancelled = $order->status === 'cancelled';
            $previousStatus = $order->payment_status;
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

            $changed = $previousStatus !== $newStatus;
            $order->update($update);

            if ($changed && $newStatus === 'paid') {
                $this->sendInvoice($order->fresh('items'), 'paid', 'duitku_callback');
            }

            if ($changed && $newStatus === 'failed' && ! $wasCancelled) {
                $this->restock($order);
            }

            $logger->finish($trace, 'processed', array_merge($traceIdentity, [
                'http_status' => 200,
                'message' => $changed
                    ? "Callback berhasil diproses: {$previousStatus} → {$newStatus}."
                    : "Callback valid tanpa perubahan status ({$newStatus}).",
                'context' => [
                    'previous_payment_status' => $previousStatus,
                    'new_payment_status' => $newStatus,
                    'status_changed' => $changed,
                ],
            ]));

            return response('OK', 200);
        } catch (\Throwable $e) {
            $logger->finish($trace, 'failed', [
                'http_status' => 500,
                'message' => $e->getMessage(),
                'context' => ['exception' => $e::class],
            ]);
            Log::error('Duitku callback gagal diproses', [
                'order' => $payload['merchantOrderId'] ?? null,
                'message' => $e->getMessage(),
            ]);

            return response('Internal server error', 500);
        }
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
    protected function sendInvoice(Order $order, string $kind, string $source = 'payment_callback'): void
    {
        app(InvoiceMailService::class)->send($order, $kind, $source);
    }

    protected function safeDuitkuPayload(array $payload): array
    {
        return collect($payload)->only([
            'merchantOrderId', 'amount', 'resultCode', 'paymentCode',
            'reference', 'publisherOrderId', 'productDetail',
        ])->all() + [
            'signature_present' => ! empty($payload['signature']),
        ];
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

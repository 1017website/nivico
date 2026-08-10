<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Integrasi Midtrans Snap (tanpa SDK resmi — pakai HTTP langsung agar
 * tidak menambah dependency; bila ingin pakai SDK, ganti method getSnapToken).
 *
 * Server Key dipakai sebagai Basic Auth (username = serverKey, password kosong).
 */
class MidtransService
{
    protected string $serverKey;

    protected string $clientKey;

    protected string $snapApiUrl;

    public function __construct()
    {
        $this->serverKey = (string) config('midtrans.server_key');
        $this->clientKey = (string) config('midtrans.client_key');
        $this->snapApiUrl = (string) config('midtrans.snap_api_url');
    }

    public function isConfigured(): bool
    {
        if (trim((string) config('midtrans.merchant_id')) === ''
            || trim($this->serverKey) === ''
            || trim($this->clientKey) === '') {
            return false;
        }

        // Mencegah kredensial Sandbox dipakai tanpa sengaja ke endpoint produksi.
        if (config('midtrans.is_production')) {
            return ! str_starts_with($this->serverKey, 'SB-')
                && ! str_starts_with($this->clientKey, 'SB-');
        }

        return str_starts_with($this->serverKey, 'SB-')
            && str_starts_with($this->clientKey, 'SB-');
    }

    /**
     * Buat Snap token untuk sebuah order. Mengembalikan token string atau null.
     */
    public function createSnapToken(Order $order): ?string
    {
        if (! $this->isConfigured()) {
            return null;
        }

        // Midtrans menolak order_id yang pernah digunakan. Suffix acak membuat
        // retry tetap unik, termasuk bila terjadi pada detik yang sama.
        $suffix = now()->format('ymdHis').'-'.Str::upper(Str::random(6));
        $midtransOrderId = Str::limit($order->order_number, 30, '').'-'.$suffix;

        $items = $order->items->map(fn ($it) => [
            'id' => (string) ($it->sku ?: $it->product_id ?: 'ITEM'),
            'price' => (int) $it->price,
            'quantity' => (int) $it->qty,
            'name' => mb_substr($it->product_name, 0, 50),
        ])->values()->all();

        if ($order->shipping_cost > 0) {
            $items[] = ['id' => 'SHIP', 'price' => (int) $order->shipping_cost, 'quantity' => 1, 'name' => 'Ongkos Kirim'];
        }
        if ($order->discount > 0) {
            $items[] = ['id' => 'DISC', 'price' => -1 * (int) $order->discount, 'quantity' => 1, 'name' => 'Diskon'];
        }
        if ($order->service_fee > 0) {
            $items[] = ['id' => 'FEE', 'price' => (int) $order->service_fee, 'quantity' => 1, 'name' => 'Biaya Layanan'];
        }

        // Snap mensyaratkan jumlah item tepat sama dengan gross_amount.
        $itemTotal = collect($items)->sum(fn (array $item) => $item['price'] * $item['quantity']);
        $adjustment = (int) $order->total - $itemTotal;
        if ($adjustment !== 0) {
            $items[] = ['id' => 'ADJ', 'price' => $adjustment, 'quantity' => 1, 'name' => 'Penyesuaian Total'];
        }

        $payload = [
            'transaction_details' => [
                'order_id' => $midtransOrderId,
                'gross_amount' => (int) $order->total,
            ],
            'item_details' => $items,
            'customer_details' => [
                'first_name' => $order->recipient_name,
                'phone' => $order->phone,
                'email' => $order->email ?: optional($order->user)->email,
                'shipping_address' => [
                    'first_name' => $order->recipient_name,
                    'phone' => $order->phone,
                    'address' => $order->address,
                    'city' => $order->city,
                    'postal_code' => $order->postal_code,
                ],
            ],
            'callbacks' => [
                'finish' => route('midtrans.finish'),
            ],
        ];

        try {
            $res = Http::withBasicAuth($this->serverKey, '')
                ->acceptJson()
                ->asJson()
                ->connectTimeout(10)
                ->timeout(25)
                ->post($this->snapApiUrl, $payload);

            if (! $res->successful()) {
                Log::warning('Midtrans snap gagal', ['status' => $res->status(), 'body' => $res->body()]);

                return null;
            }

            $token = (string) $res->json('token');
            if ($token === '') {
                Log::warning('Midtrans tidak mengembalikan Snap token', ['order' => $order->order_number]);

                return null;
            }

            $order->update([
                'snap_token' => $token,
                'midtrans_order_id' => $midtransOrderId,
                'payment_status' => 'pending',
            ]);

            return $token;
        } catch (\Throwable $e) {
            Log::error('Midtrans snap exception', ['msg' => $e->getMessage()]);

            return null;
        }
    }

    /** Batalkan transaksi aktif sebelum menerbitkan pilihan pembayaran baru. */
    public function cancelTransaction(string $midtransOrderId): bool
    {
        if (! $this->isConfigured() || trim($midtransOrderId) === '') {
            return false;
        }

        try {
            $response = Http::withBasicAuth($this->serverKey, '')
                ->acceptJson()
                ->connectTimeout(10)
                ->timeout(20)
                ->post(rtrim((string) config('midtrans.api_base'), '/').'/v2/'.rawurlencode($midtransOrderId).'/cancel');

            if ($response->successful()) {
                return true;
            }

            Log::warning('Midtrans cancel gagal', [
                'order' => $midtransOrderId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Midtrans cancel exception', [
                'order' => $midtransOrderId,
                'message' => $e->getMessage(),
            ]);
        }

        return false;
    }

    /**
     * Verifikasi signature notifikasi Midtrans.
     * signature_key = sha512(order_id + status_code + gross_amount + serverKey)
     */
    public function verifySignature(array $payload): bool
    {
        if ($this->serverKey === ''
            || empty($payload['order_id'])
            || empty($payload['status_code'])
            || ! isset($payload['gross_amount'], $payload['signature_key'])) {
            return false;
        }

        $expected = hash('sha512',
            ($payload['order_id'] ?? '').
            ($payload['status_code'] ?? '').
            ($payload['gross_amount'] ?? '').
            $this->serverKey
        );

        return hash_equals($expected, $payload['signature_key'] ?? '');
    }

    /**
     * Terjemahkan status transaksi Midtrans -> payment_status internal.
     */
    public function mapStatus(array $payload): ?string
    {
        $trx = $payload['transaction_status'] ?? '';
        $fraud = $payload['fraud_status'] ?? 'accept';

        return match ($trx) {
            'capture' => match ($fraud) {
                'accept' => 'paid',
                'challenge' => 'pending',
                default => 'failed',
            },
            'settlement' => 'paid',
            'pending' => 'pending',
            'deny', 'cancel' => 'failed',
            'expire' => 'expired',
            'refund', 'partial_refund' => 'refunded',
            default => null,
        };
    }

    public function amountMatches(Order $order, array $payload): bool
    {
        if (! isset($payload['gross_amount']) || ! is_numeric($payload['gross_amount'])) {
            return false;
        }

        return (int) round((float) $payload['gross_amount']) === (int) $order->total;
    }

    public function merchantMatches(array $payload): bool
    {
        $merchantId = trim((string) config('midtrans.merchant_id'));

        return $merchantId === ''
            || empty($payload['merchant_id'])
            || hash_equals($merchantId, (string) $payload['merchant_id']);
    }
}

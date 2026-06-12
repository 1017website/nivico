<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Integrasi Midtrans Snap (tanpa SDK resmi — pakai HTTP langsung agar
 * tidak menambah dependency; bila ingin pakai SDK, ganti method getSnapToken).
 *
 * Server Key dipakai sebagai Basic Auth (username = serverKey, password kosong).
 */
class MidtransService
{
    protected string $serverKey;
    protected string $apiBase;

    public function __construct()
    {
        $this->serverKey = (string) config('midtrans.server_key');
        $this->apiBase   = rtrim(config('midtrans.api_base'), '/');
    }

    public function isConfigured(): bool
    {
        return $this->serverKey !== '' && config('midtrans.client_key') !== '';
    }

    /**
     * Buat Snap token untuk sebuah order. Mengembalikan token string atau null.
     */
    public function createSnapToken(Order $order): ?string
    {
        if (! $this->isConfigured()) {
            return null;
        }

        // gunakan order_id unik per attempt agar tidak bentrok jika retry
        $midtransOrderId = $order->order_number.'-'.now()->timestamp;

        $items = $order->items->map(fn ($it) => [
            'id'       => (string) ($it->sku ?: $it->product_id),
            'price'    => (int) $it->price,
            'quantity' => (int) $it->qty,
            'name'     => mb_substr($it->product_name, 0, 50),
        ])->values()->all();

        // ongkir sebagai item
        if ($order->shipping_cost > 0) {
            $items[] = ['id' => 'SHIP', 'price' => (int) $order->shipping_cost, 'quantity' => 1, 'name' => 'Ongkos Kirim'];
        }
        // diskon sebagai item negatif
        if ($order->discount > 0) {
            $items[] = ['id' => 'DISC', 'price' => -1 * (int) $order->discount, 'quantity' => 1, 'name' => 'Diskon'];
        }

        $payload = [
            'transaction_details' => [
                'order_id'     => $midtransOrderId,
                'gross_amount' => (int) $order->total,
            ],
            'item_details' => $items,
            'customer_details' => [
                'first_name' => $order->recipient_name,
                'phone'      => $order->phone,
                'email'      => optional($order->user)->email ?? 'noreply@nivico.id',
                'shipping_address' => [
                    'first_name' => $order->recipient_name,
                    'phone'      => $order->phone,
                    'address'    => $order->address,
                    'city'       => $order->city,
                    'postal_code'=> $order->postal_code,
                ],
            ],
            'callbacks' => [
                'finish' => route('order.show', $order->order_number),
            ],
        ];

        try {
            $res = Http::withBasicAuth($this->serverKey, '')
                ->acceptJson()->asJson()->timeout(20)
                ->post('https://app'.(config('midtrans.is_production') ? '' : '.sandbox').'.midtrans.com/snap/v1/transactions', $payload);

            if (! $res->successful()) {
                Log::warning('Midtrans snap gagal', ['status' => $res->status(), 'body' => $res->body()]);
                return null;
            }

            $token = $res->json('token');
            $order->update([
                'snap_token'        => $token,
                'midtrans_order_id' => $midtransOrderId,
                'payment_status'    => 'pending',
            ]);

            return $token;
        } catch (\Throwable $e) {
            Log::error('Midtrans snap exception', ['msg' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Verifikasi signature notifikasi Midtrans.
     * signature_key = sha512(order_id + status_code + gross_amount + serverKey)
     */
    public function verifySignature(array $payload): bool
    {
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
    public function mapStatus(array $payload): string
    {
        $trx   = $payload['transaction_status'] ?? '';
        $fraud = $payload['fraud_status'] ?? 'accept';

        return match ($trx) {
            'capture'    => $fraud === 'challenge' ? 'pending' : 'paid',
            'settlement' => 'paid',
            'pending'    => 'pending',
            'deny'       => 'failed',
            'cancel', 'expire' => 'expired',
            'refund', 'partial_refund' => 'refunded',
            default      => 'pending',
        };
    }
}

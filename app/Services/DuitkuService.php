<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DuitkuService
{
    protected string $merchantCode;

    protected string $apiKey;

    public function __construct()
    {
        $this->merchantCode = (string) config('duitku.merchant_code');
        $this->apiKey = (string) config('duitku.api_key');
    }

    public function isConfigured(): bool
    {
        return $this->merchantCode !== '' && $this->apiKey !== '';
    }

    /**
     * Mengambil channel pembayaran yang aktif pada proyek merchant Duitku.
     *
     * @return array<int, array{paymentMethod:string,paymentName:string,paymentImage:string,totalFee:string}>
     */
    public function getPaymentMethods(int $amount): array
    {
        if (! $this->isConfigured() || $amount < 1) {
            return [];
        }

        $datetime = now('Asia/Jakarta')->format('Y-m-d H:i:s');
        $signature = hash_hmac(
            'sha256',
            $this->merchantCode.$amount.$datetime,
            $this->apiKey
        );

        try {
            $response = Http::acceptJson()
                ->asJson()
                ->timeout(20)
                ->post((string) config('duitku.payment_method_url'), [
                    'merchantcode' => $this->merchantCode,
                    'amount' => $amount,
                    'datetime' => $datetime,
                    'signature' => $signature,
                ]);

            if (! $response->successful() || $response->json('responseCode') !== '00') {
                Log::warning('Duitku get payment method gagal', [
                    'status' => $response->status(),
                    'message' => $response->json('responseMessage') ?? $response->body(),
                ]);

                return [];
            }

            return collect($response->json('paymentFee', []))
                ->filter(fn ($method) => ! empty($method['paymentMethod']) && ! empty($method['paymentName']))
                ->map(fn ($method) => [
                    'paymentMethod' => (string) $method['paymentMethod'],
                    'paymentName' => (string) $method['paymentName'],
                    'paymentImage' => (string) ($method['paymentImage'] ?? ''),
                    'totalFee' => (string) ($method['totalFee'] ?? '0'),
                ])
                ->values()
                ->all();
        } catch (\Throwable $e) {
            Log::error('Duitku get payment method exception', [
                'message' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Membuat invoice POP Duitku. Semua nominal item harus sama dengan total.
     *
     * @return array{reference:string,payment_url:string}|null
     */
    public function createInvoice(Order $order, string $paymentMethod): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        $paymentMethod = strtoupper(trim($paymentMethod));

        if ($paymentMethod === '') {
            return null;
        }

        if ($order->duitku_reference
            && $order->duitku_payment_url
            && $order->duitku_payment_method === $paymentMethod) {
            return [
                'reference' => $order->duitku_reference,
                'payment_url' => $order->duitku_payment_url,
            ];
        }

        $merchantOrderId = mb_substr(
            $order->order_number.'-'.$paymentMethod.'-'.now('Asia/Jakarta')->format('His'),
            0,
            50
        );
        $timestamp = (string) round(microtime(true) * 1000);
        $signature = hash_hmac(
            'sha256',
            $this->merchantCode.$timestamp,
            $this->apiKey
        );

        $items = $order->items->map(fn ($item) => [
            // POP mensyaratkan jumlah seluruh field price sama dengan paymentAmount.
            'name' => mb_substr($item->product_name.' × '.$item->qty, 0, 50),
            'price' => (int) $item->subtotal,
            'quantity' => 1,
        ])->values()->all();

        if ($order->shipping_cost > 0) {
            $items[] = [
                'name' => 'Ongkos Kirim',
                'price' => (int) $order->shipping_cost,
                'quantity' => 1,
            ];
        }

        if ($order->discount > 0) {
            $items[] = [
                'name' => 'Diskon',
                'price' => -1 * (int) $order->discount,
                'quantity' => 1,
            ];
        }

        $nameParts = preg_split('/\s+/', trim($order->recipient_name), 2) ?: [];
        $firstName = mb_substr($nameParts[0] ?? 'Pelanggan', 0, 50);
        $lastName = mb_substr($nameParts[1] ?? '', 0, 50);
        $email = $order->email ?: optional($order->user)->email;
        $address = [
            'firstName' => $firstName,
            'lastName' => $lastName,
            'address' => mb_substr((string) $order->address, 0, 50),
            'city' => mb_substr((string) $order->city, 0, 50),
            'postalCode' => mb_substr((string) $order->postal_code, 0, 50),
            'phone' => mb_substr((string) $order->phone, 0, 50),
            'countryCode' => 'ID',
        ];

        $payload = [
            'paymentAmount' => (int) $order->total,
            'merchantOrderId' => $merchantOrderId,
            'productDetails' => mb_substr(
                'Pembayaran produk elektronik NIVICO: '.$order->items->pluck('product_name')->join(', '),
                0,
                255
            ),
            'additionalParam' => '',
            'merchantUserInfo' => $email ?: '',
            'paymentMethod' => $paymentMethod,
            'customerVaName' => mb_substr($order->recipient_name, 0, 20),
            'email' => $email,
            'phoneNumber' => mb_substr((string) $order->phone, 0, 50),
            'itemDetails' => $items,
            'customerDetail' => [
                'firstName' => $firstName,
                'lastName' => $lastName,
                'email' => $email,
                'phoneNumber' => mb_substr((string) $order->phone, 0, 50),
                'merchantCustomerId' => $order->user_id ? (string) $order->user_id : '',
                'billingAddress' => $address,
                'shippingAddress' => $address,
            ],
            'callbackUrl' => route('duitku.callback'),
            'returnUrl' => route('duitku.return', $order->order_number),
            'expiryPeriod' => (int) config('duitku.expiry_period', 60),
        ];

        try {
            $response = Http::acceptJson()
                ->asJson()
                ->timeout(20)
                ->withHeaders([
                    'x-duitku-merchantcode' => $this->merchantCode,
                    'x-duitku-timestamp' => $timestamp,
                    'x-duitku-signature' => $signature,
                ])
                ->post((string) config('duitku.create_invoice_url'), $payload);

            if (! $response->successful() || $response->json('statusCode') !== '00') {
                Log::warning('Duitku create invoice gagal', [
                    'order' => $order->order_number,
                    'status' => $response->status(),
                    'message' => $response->json('statusMessage') ?? $response->body(),
                ]);

                return null;
            }

            $reference = (string) $response->json('reference');
            $paymentUrl = (string) $response->json('paymentUrl');

            if ($reference === '' || $paymentUrl === '') {
                return null;
            }

            $order->update([
                'duitku_reference' => $reference,
                'duitku_merchant_order_id' => $merchantOrderId,
                'duitku_payment_url' => $paymentUrl,
                'duitku_payment_method' => $paymentMethod,
                'payment_status' => 'pending',
            ]);

            return [
                'reference' => $reference,
                'payment_url' => $paymentUrl,
            ];
        } catch (\Throwable $e) {
            Log::error('Duitku create invoice exception', [
                'order' => $order->order_number,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function verifyCallback(array $payload): bool
    {
        $merchantCode = (string) ($payload['merchantCode'] ?? '');
        $amount = (string) ($payload['amount'] ?? '');
        $merchantOrderId = (string) ($payload['merchantOrderId'] ?? '');
        $signature = (string) ($payload['signature'] ?? '');

        if (! $this->isConfigured()
            || $merchantCode === ''
            || $amount === ''
            || $merchantOrderId === ''
            || $signature === ''
            || ! hash_equals($this->merchantCode, $merchantCode)) {
            return false;
        }

        $expected = hash_hmac(
            'sha256',
            $merchantCode.$amount.$merchantOrderId,
            $this->apiKey
        );

        return hash_equals($expected, $signature);
    }
}

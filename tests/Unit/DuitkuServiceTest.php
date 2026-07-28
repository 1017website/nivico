<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\OrderItem;
use App\Services\DuitkuService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DuitkuServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'duitku.merchant_code' => 'D12345',
            'duitku.api_key' => 'sandbox-secret-key',
        ]);
    }

    public function test_it_detects_a_configured_merchant(): void
    {
        $this->assertTrue(app(DuitkuService::class)->isConfigured());
    }

    public function test_it_accepts_a_valid_callback_signature(): void
    {
        $payload = [
            'merchantCode' => 'D12345',
            'amount' => '150000',
            'merchantOrderId' => 'NVC-20260728-001',
        ];
        $payload['signature'] = hash_hmac(
            'sha256',
            $payload['merchantCode'].$payload['amount'].$payload['merchantOrderId'],
            'sandbox-secret-key'
        );

        $this->assertTrue(app(DuitkuService::class)->verifyCallback($payload));
    }

    public function test_it_rejects_a_tampered_callback(): void
    {
        $payload = [
            'merchantCode' => 'D12345',
            'amount' => '150001',
            'merchantOrderId' => 'NVC-20260728-001',
            'signature' => hash_hmac(
                'sha256',
                'D12345150000NVC-20260728-001',
                'sandbox-secret-key'
            ),
        ];

        $this->assertFalse(app(DuitkuService::class)->verifyCallback($payload));
    }

    public function test_it_fetches_the_active_payment_methods(): void
    {
        config([
            'duitku.payment_method_url' => 'https://sandbox.duitku.com/webapi/api/merchant/paymentmethod/getpaymentmethod',
        ]);

        Http::fake([
            'https://sandbox.duitku.com/*' => Http::response([
                'paymentFee' => [
                    [
                        'paymentMethod' => 'VA',
                        'paymentName' => 'MAYBANK VA',
                        'paymentImage' => 'https://images.duitku.com/VA.png',
                        'totalFee' => '0',
                    ],
                    [
                        'paymentMethod' => 'NQ',
                        'paymentName' => 'NOBU QRIS',
                        'paymentImage' => 'https://images.duitku.com/NQ.png',
                        'totalFee' => '1000',
                    ],
                ],
                'responseCode' => '00',
                'responseMessage' => 'SUCCESS',
            ]),
        ]);

        $methods = app(DuitkuService::class)->getPaymentMethods(45000);

        $this->assertCount(2, $methods);
        $this->assertSame('MAYBANK VA', $methods[0]['paymentName']);
        $this->assertSame('NQ', $methods[1]['paymentMethod']);

        Http::assertSent(function (Request $request) {
            $data = $request->data();
            $expected = hash_hmac(
                'sha256',
                'D12345'.$data['amount'].$data['datetime'],
                'sandbox-secret-key'
            );

            return $request->url() === 'https://sandbox.duitku.com/webapi/api/merchant/paymentmethod/getpaymentmethod'
                && $data['merchantcode'] === 'D12345'
                && $data['amount'] === 45000
                && $data['signature'] === $expected;
        });
    }

    public function test_it_creates_a_sandbox_invoice_with_matching_item_total(): void
    {
        config([
            'duitku.create_invoice_url' => 'https://api-sandbox.duitku.com/api/merchant/createInvoice',
            'duitku.expiry_period' => 60,
        ]);

        Http::fake([
            'https://api-sandbox.duitku.com/*' => Http::response([
                'merchantCode' => 'D12345',
                'reference' => 'D12345-REF-001',
                'paymentUrl' => 'https://app-sandbox.duitku.com/redirect_checkout?reference=D12345-REF-001',
                'statusCode' => '00',
                'statusMessage' => 'SUCCESS',
            ]),
        ]);

        $order = new class(['order_number' => 'NVC-20260728-001', 'recipient_name' => 'Budi Pelanggan', 'phone' => '628123456789', 'email' => 'customer@nivico.id', 'address' => 'Jl. Contoh No. 1', 'city' => 'Surabaya', 'postal_code' => '60123', 'shipping_cost' => 10000, 'discount' => 5000, 'total' => 45000]) extends Order
        {
            public array $updatedAttributes = [];

            public function update(array $attributes = [], array $options = []): bool
            {
                $this->updatedAttributes = $attributes;
                $this->forceFill($attributes);

                return true;
            }
        };

        $order->setRelation('items', collect([
            new OrderItem([
                'product_name' => 'Kabel USB Type-C',
                'price' => 20000,
                'qty' => 2,
                'subtotal' => 40000,
            ]),
        ]));

        $invoice = app(DuitkuService::class)->createInvoice($order, 'NQ');

        $this->assertSame('D12345-REF-001', $invoice['reference']);
        $this->assertSame('pending', $order->updatedAttributes['payment_status']);
        $this->assertSame('NQ', $order->updatedAttributes['duitku_payment_method']);
        $this->assertStringStartsWith(
            'NVC-20260728-001-NQ-',
            $order->updatedAttributes['duitku_merchant_order_id']
        );

        Http::assertSent(function (Request $request) {
            $timestamp = (string) $request->header('x-duitku-timestamp')[0];
            $expectedSignature = hash_hmac(
                'sha256',
                'D12345'.$timestamp,
                'sandbox-secret-key'
            );
            $itemTotal = collect($request->data()['itemDetails'])->sum('price');

            return $request->url() === 'https://api-sandbox.duitku.com/api/merchant/createInvoice'
                && $request->header('x-duitku-merchantcode')[0] === 'D12345'
                && $request->header('x-duitku-signature')[0] === $expectedSignature
                && $request['paymentAmount'] === 45000
                && $request['paymentMethod'] === 'NQ'
                && str_starts_with($request['merchantOrderId'], 'NVC-20260728-001-NQ-')
                && $itemTotal === 45000
                && $request['callbackUrl'] === route('duitku.callback');
        });
    }
}

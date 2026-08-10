<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\OrderItem;
use App\Services\MidtransService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MidtransServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'midtrans.merchant_id' => 'G123456789',
            'midtrans.client_key' => 'Mid-client-production-key',
            'midtrans.server_key' => 'Mid-server-production-key',
            'midtrans.is_production' => true,
            'midtrans.snap_api_url' => 'https://app.midtrans.com/snap/v1/transactions',
        ]);
    }

    public function test_it_accepts_production_credentials(): void
    {
        $this->assertTrue(app(MidtransService::class)->isConfigured());
    }

    public function test_it_rejects_sandbox_credentials_in_production_mode(): void
    {
        config([
            'midtrans.client_key' => 'SB-Mid-client-key',
            'midtrans.server_key' => 'SB-Mid-server-key',
        ]);

        $this->assertFalse(app(MidtransService::class)->isConfigured());
    }

    public function test_it_requires_the_merchant_id(): void
    {
        config(['midtrans.merchant_id' => '']);

        $this->assertFalse(app(MidtransService::class)->isConfigured());
    }

    public function test_it_verifies_the_official_notification_signature_format(): void
    {
        $payload = [
            'order_id' => 'NVC-20260730-001-260730120000-ABC123',
            'status_code' => '200',
            'gross_amount' => '125000.00',
        ];
        $payload['signature_key'] = hash(
            'sha512',
            $payload['order_id'].$payload['status_code'].$payload['gross_amount'].'Mid-server-production-key'
        );

        $service = app(MidtransService::class);

        $this->assertTrue($service->verifySignature($payload));
        $payload['gross_amount'] = '125001.00';
        $this->assertFalse($service->verifySignature($payload));
    }

    public function test_it_maps_supported_transaction_statuses_without_guessing_unknown_values(): void
    {
        $service = app(MidtransService::class);

        $this->assertSame('paid', $service->mapStatus(['transaction_status' => 'settlement']));
        $this->assertSame('pending', $service->mapStatus([
            'transaction_status' => 'capture',
            'fraud_status' => 'challenge',
        ]));
        $this->assertSame('expired', $service->mapStatus(['transaction_status' => 'expire']));
        $this->assertSame('failed', $service->mapStatus(['transaction_status' => 'cancel']));
        $this->assertNull($service->mapStatus(['transaction_status' => 'future_status']));
    }

    public function test_it_validates_notification_amount_and_merchant(): void
    {
        $order = new Order(['total' => 125000]);
        $service = app(MidtransService::class);

        $this->assertTrue($service->amountMatches($order, ['gross_amount' => '125000.00']));
        $this->assertFalse($service->amountMatches($order, ['gross_amount' => '125001.00']));
        $this->assertTrue($service->merchantMatches(['merchant_id' => 'G123456789']));
        $this->assertFalse($service->merchantMatches(['merchant_id' => 'G000000000']));
    }

    public function test_it_creates_a_production_snap_transaction_with_matching_item_total(): void
    {
        Http::fake([
            'https://app.midtrans.com/*' => Http::response([
                'token' => 'production-snap-token',
                'redirect_url' => 'https://app.midtrans.com/snap/v4/redirection/token',
            ]),
        ]);

        $order = new class(['order_number' => 'NVC-20260730-001', 'recipient_name' => 'Budi Pelanggan', 'phone' => '628123456789', 'email' => 'customer@nivico.id', 'address' => 'Jl. Contoh No. 1', 'city' => 'Surabaya', 'postal_code' => '60123', 'shipping_cost' => 10000, 'discount' => 5000, 'total' => 45000]) extends Order
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
                'sku' => 'USB-C-001',
                'price' => 20000,
                'qty' => 2,
                'subtotal' => 40000,
            ]),
        ]));

        $token = app(MidtransService::class)->createSnapToken($order);

        $this->assertSame('production-snap-token', $token);
        $this->assertSame('pending', $order->updatedAttributes['payment_status']);
        $this->assertStringStartsWith('NVC-20260730-001-', $order->updatedAttributes['midtrans_order_id']);

        Http::assertSent(function (Request $request) {
            $data = $request->data();
            $itemTotal = collect($data['item_details'])
                ->sum(fn (array $item) => $item['price'] * $item['quantity']);

            return $request->url() === 'https://app.midtrans.com/snap/v1/transactions'
                && $data['transaction_details']['gross_amount'] === 45000
                && $itemTotal === 45000
                && $data['customer_details']['email'] === 'customer@nivico.id'
                && str_ends_with($data['callbacks']['finish'], '/midtrans/finish');
        });
    }

    public function test_it_cancels_an_active_transaction_before_reissuing_payment_options(): void
    {
        config(['midtrans.api_base' => 'https://api.midtrans.com']);
        Http::fake([
            'https://api.midtrans.com/v2/*/cancel' => Http::response([
                'status_code' => '200',
                'transaction_status' => 'cancel',
            ]),
        ]);

        $result = app(MidtransService::class)->cancelTransaction('NVC-20260730-001-260730120000-ABC123');

        $this->assertTrue($result);
        Http::assertSent(fn (Request $request) => $request->method() === 'POST'
            && $request->url() === 'https://api.midtrans.com/v2/NVC-20260730-001-260730120000-ABC123/cancel'
        );
    }
}

<?php

namespace Tests\Unit;

use App\Http\Controllers\CartController;
use App\Models\Promo;
use App\Services\CartService;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;

class PromoApplicationTest extends TestCase
{
    public function test_percent_discount_respects_its_cap_and_never_exceeds_subtotal(): void
    {
        $promo = new Promo([
            'type' => 'percent',
            'value' => 150,
            'max_discount' => null,
            'min_purchase' => 0,
            'is_active' => true,
        ]);

        $this->assertSame([
            'discount' => 100000,
            'free_shipping' => false,
        ], $promo->calculate(100000, 15000));

        $promo->value = 30;
        $promo->max_discount = 20000;

        $this->assertSame(20000, $promo->calculate(100000, 15000)['discount']);
    }

    public function test_free_shipping_discount_uses_the_selected_shipping_cost(): void
    {
        $promo = new Promo([
            'type' => 'free_shipping',
            'value' => 0,
            'min_purchase' => 50000,
            'is_active' => true,
        ]);

        $this->assertSame([
            'discount' => 18000,
            'free_shipping' => true,
        ], $promo->calculate(75000, 18000));
    }

    public function test_checkout_can_apply_a_promo_as_json_without_reloading_the_form(): void
    {
        $promo = new Promo([
            'code' => 'NIVICO10',
            'title' => 'Diskon 10%',
            'type' => 'percent',
            'value' => 10,
            'max_discount' => 25000,
            'min_purchase' => 50000,
        ]);

        $cart = Mockery::mock(CartService::class);
        $cart->shouldReceive('applyPromo')
            ->once()
            ->with('NIVICO10')
            ->andReturn([
                'ok' => true,
                'message' => 'Promo NIVICO10 diterapkan',
                'promo' => $promo,
            ]);

        $request = Request::create('/keranjang/promo', 'POST', ['code' => 'NIVICO10']);
        $request->headers->set('Accept', 'application/json');

        $response = (new CartController($cart))->applyPromo($request);
        $payload = $response->getData(true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue($payload['ok']);
        $this->assertSame('NIVICO10', $payload['promo']['code']);
        $this->assertSame(25000, $payload['promo']['max_discount']);
    }
}

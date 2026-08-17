<?php

namespace Tests\Unit;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\OrderService;
use Tests\TestCase;

class VariantShippingWeightTest extends TestCase
{
    public function test_selected_variant_supplies_its_own_weight_and_dimensions(): void
    {
        config(['rajaongkir.dimensional_divisor' => 6000]);

        $product = new Product([
            'weight' => 1000,
            'length' => 20,
            'width' => 20,
            'height' => 20,
        ]);
        $variant = new ProductVariant([
            'weight' => 2500,
            'length' => 60,
            'width' => 40,
            'height' => 30,
        ]);

        $item = new CartItem;
        $item->setRelation('product', $product);
        $item->setRelation('variant', $variant);

        $this->assertSame(2500, $item->effectiveWeight());
        $this->assertSame([
            'length' => 60,
            'width' => 40,
            'height' => 30,
        ], $item->effectiveDimensions());
        $this->assertSame(12000, $item->shippingWeight());
    }

    public function test_variant_dimensions_fall_back_to_product_dimensions(): void
    {
        config(['rajaongkir.dimensional_divisor' => 6000]);

        $product = new Product([
            'weight' => 2000,
            'length' => 30,
            'width' => 20,
            'height' => 10,
        ]);
        $variant = new ProductVariant(['weight' => 1500]);

        $item = new CartItem;
        $item->setRelation('product', $product);
        $item->setRelation('variant', $variant);

        $this->assertSame(1500, $item->effectiveWeight());
        $this->assertSame(1500, $item->shippingWeight());
    }

    public function test_incomplete_dimensions_do_not_create_a_volumetric_weight(): void
    {
        $product = new Product([
            'weight' => 1800,
            'length' => 60,
            'width' => 40,
        ]);

        $item = new CartItem;
        $item->setRelation('product', $product);
        $item->setRelation('variant', null);

        $this->assertSame(1800, $item->shippingWeight());
    }

    public function test_cart_weight_uses_each_selected_variant_and_quantity(): void
    {
        config(['rajaongkir.dimensional_divisor' => 6000]);

        $product = new Product(['weight' => 1000]);
        $variant38 = new ProductVariant([
            'weight' => 1200,
            'length' => 30,
            'width' => 20,
            'height' => 15,
        ]);
        $variant48 = new ProductVariant([
            'weight' => 1800,
            'length' => 40,
            'width' => 25,
            'height' => 18,
        ]);

        $item38 = new CartItem(['qty' => 2]);
        $item38->setRelation('product', $product);
        $item38->setRelation('variant', $variant38);

        $item48 = new CartItem(['qty' => 1]);
        $item48->setRelation('product', $product);
        $item48->setRelation('variant', $variant48);

        $cart = (object) ['items' => collect([$item38, $item48])];

        $this->assertSame(6000, app(OrderService::class)->cartWeight($cart));
    }
}

<?php

namespace Tests\Unit;

use App\Models\Product;
use PHPUnit\Framework\TestCase;

class ProductDescriptionTest extends TestCase
{
    public function test_it_uses_the_saved_product_description(): void
    {
        $product = new Product([
            'name' => 'Kabel USB',
            'description' => 'Kabel USB fast charging 65W dengan lapisan nylon.',
        ]);

        $this->assertSame(
            'Kabel USB fast charging 65W dengan lapisan nylon.',
            $product->display_description
        );
    }

    public function test_it_provides_a_fallback_for_legacy_products(): void
    {
        $product = new Product([
            'name' => 'Kabel USB',
            'description' => null,
        ]);

        $this->assertStringContainsString('Kabel USB', $product->display_description);
        $this->assertStringContainsString('produk elektronik', $product->display_description);
    }
}

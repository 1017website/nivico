<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminProductVariantShippingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_save_different_weight_and_dimensions_for_each_variant(): void
    {
        $admin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'Varian',
            'email' => 'admin-varian-test@example.com',
            'password' => 'test-password',
            'role' => 'admin',
            'role_id' => null,
            'is_active' => true,
        ]);
        $category = Category::create([
            'name' => 'Lampu',
            'slug' => 'lampu',
            'is_active' => true,
        ]);

        $payload = [
            'category_id' => $category->id,
            'name' => 'Lampu LED Dua Daya',
            'sku' => 'LAMPU-DUA-DAYA',
            'weight' => 1000,
            'length' => 20,
            'width' => 20,
            'height' => 10,
            'description' => 'Lampu LED dengan pilihan daya dan ukuran kemasan yang berbeda.',
            'is_active' => 1,
            'has_variants' => 1,
            'variants' => [
                [
                    'name' => '38 Watt',
                    'sku' => 'LAMPU-38W',
                    'price' => 85000,
                    'stock' => 12,
                    'weight' => 1200,
                    'length' => 30,
                    'width' => 20,
                    'height' => 15,
                ],
                [
                    'name' => '48 Watt',
                    'sku' => 'LAMPU-48W',
                    'price' => 105000,
                    'stock' => 8,
                    'weight' => 1800,
                    'length' => 40,
                    'width' => 25,
                    'height' => 18,
                ],
            ],
        ];

        $response = $this->actingAs($admin)->post(route('admin.products.store'), $payload);

        $response->assertRedirect(route('admin.products.index'));
        $response->assertSessionHasNoErrors();

        $product = Product::where('sku', 'LAMPU-DUA-DAYA')->with('variants')->firstOrFail();
        $variant38 = $product->variants->firstWhere('sku', 'LAMPU-38W');
        $variant48 = $product->variants->firstWhere('sku', 'LAMPU-48W');

        $this->assertSame(1200, (int) $variant38->weight);
        $this->assertSame([30, 20, 15], [
            (int) $variant38->length,
            (int) $variant38->width,
            (int) $variant38->height,
        ]);
        $this->assertSame(1800, (int) $variant48->weight);
        $this->assertSame([40, 25, 18], [
            (int) $variant48->length,
            (int) $variant48->width,
            (int) $variant48->height,
        ]);
    }
}

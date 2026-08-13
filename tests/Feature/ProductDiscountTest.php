<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductDiscountTest extends TestCase
{
    use RefreshDatabase;

    public function test_selected_product_discount_changes_display_and_cart_prices(): void
    {
        $this->configureDiscount('selected', 20);

        $selected = new Product([
            'price' => 100000,
            'is_flash_sale' => true,
            'has_variants' => false,
        ]);
        $regular = new Product([
            'price' => 100000,
            'is_flash_sale' => false,
            'has_variants' => false,
        ]);

        $item = new CartItem(['qty' => 2]);
        $item->setRelation('product', $selected);
        $item->setRelation('variant', null);

        $this->assertTrue($selected->hasActiveDiscount());
        $this->assertSame(80000, $selected->effective_price);
        $this->assertSame(20, $selected->discount_percent);
        $this->assertSame(80000, $item->effectivePrice());
        $this->assertSame(100000, $item->basePrice());
        $this->assertFalse($regular->hasActiveDiscount());
        $this->assertSame(100000, $regular->effective_price);
    }

    public function test_all_product_discount_also_applies_to_variant_price(): void
    {
        $this->configureDiscount('all', 15);

        $product = new Product([
            'price' => 50000,
            'is_flash_sale' => false,
            'has_variants' => true,
        ]);
        $variant = new ProductVariant(['price' => 120000]);
        $item = new CartItem(['qty' => 1]);
        $item->setRelation('product', $product);
        $item->setRelation('variant', $variant);

        $this->assertTrue($product->hasActiveDiscount());
        $this->assertSame(102000, $item->effectivePrice());
        $this->assertSame(120000, $item->basePrice());
    }

    public function test_disabled_product_discount_keeps_catalog_price(): void
    {
        SiteSetting::put('flashsale.discount_enabled', '0', 'boolean', 'flashsale');
        SiteSetting::put('flashsale.discount_scope', 'all', 'text', 'flashsale');
        SiteSetting::put('flashsale.discount_percent', '25', 'number', 'flashsale');

        $product = new Product([
            'price' => 100000,
            'is_flash_sale' => true,
            'has_variants' => false,
        ]);

        $this->assertFalse($product->hasActiveDiscount());
        $this->assertSame(100000, $product->effective_price);
    }

    public function test_admin_can_save_discount_scope_and_percentage(): void
    {
        $admin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'Diskon',
            'email' => 'admin-diskon-test@example.com',
            'password' => 'test-password',
            'role' => 'admin',
            'role_id' => null,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.flashsale.settings'), [
            'discount_enabled' => '1',
            'discount_scope' => 'all',
            'discount_percent' => '30',
            'enabled' => '0',
            'title' => 'Flash Sale',
            'label' => 'Berakhir dalam:',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $this->assertTrue((bool) SiteSetting::get('flashsale.discount_enabled'));
        $this->assertSame('all', SiteSetting::get('flashsale.discount_scope'));
        $this->assertSame(30, SiteSetting::get('flashsale.discount_percent'));
    }

    private function configureDiscount(string $scope, int $percentage): void
    {
        SiteSetting::put('flashsale.discount_enabled', '1', 'boolean', 'flashsale');
        SiteSetting::put('flashsale.discount_scope', $scope, 'text', 'flashsale');
        SiteSetting::put('flashsale.discount_percent', (string) $percentage, 'number', 'flashsale');
    }
}

<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminProductMediaTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_save_weight_and_up_to_ten_product_images(): void
    {
        Storage::fake('public');

        $admin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'Produk',
            'email' => 'admin-produk-test@example.com',
            'password' => 'test-password',
            'role' => 'admin',
            'role_id' => null,
            'is_active' => true,
        ]);
        $category = Category::create([
            'name' => 'Elektronik',
            'slug' => 'elektronik',
            'is_active' => true,
        ]);
        $images = collect(range(1, 10))->mapWithKeys(fn ($number) => [
            $number - 1 => UploadedFile::fake()->image("produk-{$number}.jpg", 320, 320),
        ])->all();

        $response = $this->actingAs($admin)->post(route('admin.products.store'), [
            'category_id' => $category->id,
            'name' => 'Produk Galeri Lengkap',
            'sku' => 'GALERI-10',
            'price' => 125000,
            'stock' => 20,
            'weight' => 1750,
            'description' => 'Produk elektronik dengan galeri lengkap untuk kebutuhan pengujian.',
            'rating' => 4.8,
            'rating_count' => 0,
            'is_active' => 1,
            'image_files' => $images,
        ]);

        $product = Product::where('sku', 'GALERI-10')->firstOrFail();

        $response->assertRedirect(route('admin.products.index'));
        $this->assertSame(1750, (int) $product->weight);
        $this->assertNotNull($product->image);
        $this->assertCount(9, $product->images);

        $tooManyResponse = $this->actingAs($admin)->put(route('admin.products.update', $product), [
            'category_id' => $category->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'price' => $product->price,
            'stock' => $product->stock,
            'weight' => $product->weight,
            'description' => $product->description,
            'rating' => $product->rating,
            'rating_count' => $product->rating_count,
            'is_active' => 1,
            'image' => $product->image,
            'image_files' => [UploadedFile::fake()->image('gambar-ke-11.jpg', 320, 320)],
        ]);

        $tooManyResponse->assertSessionHasErrors('image_files');
        $this->assertSame(10, 1 + $product->fresh()->images()->count());
    }
}

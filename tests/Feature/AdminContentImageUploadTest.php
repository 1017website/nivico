<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminContentImageUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_upload_an_image_for_a_hero_slide(): void
    {
        Storage::fake('public');
        $admin = $this->admin();
        SiteSetting::put('hero.slides', [[
            'title1' => 'Slide lama',
            'image' => 'https://example.com/old.jpg',
        ]], 'json', 'hero', 'Slide Hero');

        $this->actingAs($admin)->get(route('admin.content.index', ['tab' => 'hero']))
            ->assertOk()
            ->assertSee('json_file[hero__slides][0][image]', false);

        $response = $this->actingAs($admin)->put(route('admin.content.update', 'hero'), [
            'json' => [
                'hero.slides' => [[
                    'title1' => 'Slide baru',
                    'image' => 'https://example.com/old.jpg',
                ]],
            ],
            'json_file' => [
                'hero__slides' => [[
                    'image' => UploadedFile::fake()->image('hero-baru.jpg', 1200, 600),
                ]],
            ],
        ]);

        $response->assertRedirect()->assertSessionHasNoErrors();
        $slide = SiteSetting::get('hero.slides')[0];

        $this->assertSame('Slide baru', $slide['title1']);
        $this->assertStringStartsWith('/storage/content/', $slide['image']);
        Storage::disk('public')->assertExists(str_replace('/storage/', '', $slide['image']));
        $this->get(route('home'))->assertOk()->assertSee($slide['image'], false);
    }

    public function test_replacing_an_uploaded_repeater_image_removes_the_old_file(): void
    {
        Storage::fake('public');
        $admin = $this->admin();
        Storage::disk('public')->put('content/hero-lama.jpg', 'old-image');
        SiteSetting::put('hero.slides', [[
            'title1' => 'Hero',
            'image' => '/storage/content/hero-lama.jpg',
        ]], 'json', 'hero', 'Slide Hero');

        $this->actingAs($admin)->put(route('admin.content.update', 'hero'), [
            'json' => [
                'hero.slides' => [[
                    'title1' => 'Hero',
                    'image' => '/storage/content/hero-lama.jpg',
                ]],
            ],
            'json_file' => [
                'hero__slides' => [[
                    'image' => UploadedFile::fake()->image('pengganti.png', 1200, 600),
                ]],
            ],
        ])->assertSessionHasNoErrors();

        Storage::disk('public')->assertMissing('content/hero-lama.jpg');
        $newImage = SiteSetting::get('hero.slides')[0]['image'];
        Storage::disk('public')->assertExists(str_replace('/storage/', '', $newImage));
    }

    public function test_content_image_upload_rejects_non_image_files(): void
    {
        Storage::fake('public');
        $admin = $this->admin();
        SiteSetting::put('hero.slides', [], 'json', 'hero', 'Slide Hero');

        $response = $this->actingAs($admin)->from(route('admin.content.index', ['tab' => 'hero']))
            ->put(route('admin.content.update', 'hero'), [
                'json' => ['hero.slides' => [['title1' => 'Hero']]],
                'json_file' => [
                    'hero__slides' => [[
                        'image' => UploadedFile::fake()->create('bukan-gambar.txt', 10, 'text/plain'),
                    ]],
                ],
            ]);

        $response->assertRedirect(route('admin.content.index', ['tab' => 'hero']))
            ->assertSessionHasErrors('images.0');
        $this->assertSame([], SiteSetting::get('hero.slides'));
    }

    private function admin(): User
    {
        return User::create([
            'first_name' => 'Admin',
            'last_name' => 'Konten',
            'email' => 'admin-content-image@example.com',
            'password' => 'test-password',
            'role' => 'admin',
            'role_id' => null,
            'is_active' => true,
        ]);
    }
}

<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAboutContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_about_content_and_frontend_displays_it(): void
    {
        $admin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'Konten',
            'email' => 'admin-konten-tentang@example.com',
            'password' => 'test-password',
            'role' => 'admin',
            'role_id' => null,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->put(route('admin.content.update', 'tentang'), [
            'val' => [
                'about.hero_title' => 'Mengenal Toko Kami',
                'about.hero_subtitle' => 'Deskripsi baru halaman tentang.',
                'about.story_title' => 'Perjalanan Kami',
                'about.story_body' => "Cerita baris pertama.\n\nCerita baris kedua.",
                'about.vision_mission_title' => 'Arah Perusahaan',
                'about.vision_label' => 'Visi Kami:',
                'about.vision_body' => 'Menjadi toko elektronik pilihan keluarga Indonesia.',
                'about.mission_label' => 'Misi Kami:',
            ],
            'json' => [
                'about.stats' => [
                    ['value' => '900+', 'label' => 'Produk Aktif'],
                    ['value' => '24/7', 'label' => 'Dukungan'],
                ],
                'about.missions' => [
                    ['text' => 'Memberikan produk berkualitas'],
                    ['text' => 'Melayani pelanggan dengan cepat'],
                ],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $this->assertSame('Mengenal Toko Kami', SiteSetting::get('about.hero_title'));
        $this->assertSame([
            ['value' => '900+', 'label' => 'Produk Aktif'],
            ['value' => '24/7', 'label' => 'Dukungan'],
        ], SiteSetting::get('about.stats'));

        $this->get(route('about'))
            ->assertOk()
            ->assertSee('Mengenal Toko Kami')
            ->assertSee('900+')
            ->assertSee('Perjalanan Kami')
            ->assertSee('Menjadi toko elektronik pilihan keluarga Indonesia.')
            ->assertSee('Melayani pelanggan dengan cepat')
            ->assertDontSee('Budi Santoso');
    }
}

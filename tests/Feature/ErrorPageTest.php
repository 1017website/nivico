<?php

namespace Tests\Feature;

use Tests\TestCase;

class ErrorPageTest extends TestCase
{
    public function test_unknown_url_uses_the_branded_not_found_page(): void
    {
        config(['app.debug' => false]);

        $response = $this->get('/halaman-yang-tidak-tersedia');

        $response->assertNotFound()
            ->assertSee('NIVICO Electronic Mart')
            ->assertSee('Halaman yang Anda cari tidak ditemukan')
            ->assertDontSee('Laravel');
    }

    public function test_all_supported_error_pages_render_without_framework_branding(): void
    {
        foreach ([400, 401, 403, 404, 408, 419, 422, 429, 500, 502, 503, 504] as $status) {
            $html = view("errors.{$status}")->render();

            $this->assertStringContainsString('NIVICO Electronic Mart', $html);
            $this->assertStringContainsString((string) $status, $html);
            $this->assertStringNotContainsString('Laravel', $html);
        }
    }

    public function test_favicon_assets_exist_and_are_valid_images(): void
    {
        foreach ([
            'favicon.png',
            'favicon-32x32.png',
            'favicon-16x16.png',
            'apple-touch-icon.png',
        ] as $filename) {
            $path = public_path($filename);

            $this->assertFileExists($path);
            $this->assertNotFalse(getimagesize($path));
        }

        $this->assertFileExists(public_path('favicon.ico'));
        $this->assertGreaterThan(0, filesize(public_path('favicon.ico')));
    }
}

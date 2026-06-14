<?php

use App\Models\SiteSetting;

if (! function_exists('setting')) {
    /**
     * Ambil nilai site setting. Contoh:
     *   setting('brand.name', 'NIVICO')
     *   setting('hero.slides', [])
     */
    function setting(string $key, $default = null)
    {
        return SiteSetting::get($key, $default);
    }
}

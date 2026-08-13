<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $settings = [
            ['flashsale.discount_enabled', 'boolean', 'Aktifkan Diskon Produk', '0'],
            ['flashsale.discount_scope', 'text', 'Cakupan Diskon', 'selected'],
            ['flashsale.discount_percent', 'number', 'Persentase Diskon', '10'],
        ];

        foreach ($settings as [$key, $type, $label, $value]) {
            DB::table('site_settings')->updateOrInsert(
                ['key' => $key],
                [
                    'type' => $type,
                    'group' => 'flashsale',
                    'label' => $label,
                    'value' => $value,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        Cache::forget('site_settings_all');
    }

    public function down(): void
    {
        DB::table('site_settings')->whereIn('key', [
            'flashsale.discount_enabled',
            'flashsale.discount_scope',
            'flashsale.discount_percent',
        ])->delete();

        Cache::forget('site_settings_all');
    }
};

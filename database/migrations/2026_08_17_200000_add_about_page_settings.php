<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $settings = [
            ['about.hero_title', 'text', 'Judul Banner', 'Tentang NIVICO Electronic Mart'],
            ['about.hero_subtitle', 'textarea', 'Deskripsi Banner', 'Kami adalah toko elektronik terpercaya yang telah melayani ribuan pelanggan di seluruh Indonesia sejak 2015 dengan produk berkualitas dan harga terjangkau.'],
            ['about.stats', 'json', 'Statistik', json_encode([
                ['value' => '10K+', 'label' => 'Produk Tersedia'],
                ['value' => '50K+', 'label' => 'Pelanggan Puas'],
                ['value' => '9+', 'label' => 'Tahun Pengalaman'],
                ['value' => '34', 'label' => 'Provinsi Terjangkau'],
            ], JSON_UNESCAPED_UNICODE)],
            ['about.story_title', 'text', 'Judul Cerita', 'Cerita Kami'],
            ['about.story_body', 'textarea', 'Isi Cerita', "NIVICO Electronic Mart didirikan pada tahun 2015 di Surabaya, Jawa Timur. Berawal dari toko kecil yang menjual kabel dan aksesoris elektronik, kini kami telah berkembang menjadi salah satu toko elektronik online terpercaya di Indonesia.\n\nNama NIVICO terinspirasi dari semangat kami untuk memberikan nilai (value) terbaik kepada pelanggan melalui produk berkualitas dengan harga kompetitif. Kami berkomitmen untuk selalu menghadirkan produk original dan bersertifikat SNI."],
            ['about.vision_mission_title', 'text', 'Judul Visi & Misi', 'Visi & Misi'],
            ['about.vision_label', 'text', 'Label Visi', 'Visi:'],
            ['about.vision_body', 'textarea', 'Isi Visi', 'Menjadi marketplace elektronik nomor satu di Indonesia yang dipercaya oleh jutaan pelanggan dan mitra bisnis.'],
            ['about.mission_label', 'text', 'Label Misi', 'Misi:'],
            ['about.missions', 'json', 'Daftar Misi', json_encode([
                ['text' => 'Menyediakan produk elektronik berkualitas dengan harga terjangkau'],
                ['text' => 'Memberikan pelayanan pelanggan terbaik yang responsif dan profesional'],
                ['text' => 'Membangun ekosistem perdagangan elektronik yang transparan dan terpercaya'],
                ['text' => 'Terus berinovasi untuk memberikan pengalaman belanja terbaik'],
            ], JSON_UNESCAPED_UNICODE)],
        ];

        foreach ($settings as [$key, $type, $label, $value]) {
            DB::table('site_settings')->updateOrInsert(
                ['key' => $key],
                [
                    'type' => $type,
                    'group' => 'tentang',
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
            'about.hero_title',
            'about.hero_subtitle',
            'about.stats',
            'about.story_title',
            'about.story_body',
            'about.vision_mission_title',
            'about.vision_label',
            'about.vision_body',
            'about.mission_label',
            'about.missions',
        ])->delete();

        Cache::forget('site_settings_all');
    }
};

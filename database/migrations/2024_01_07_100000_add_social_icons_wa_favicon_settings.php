<?php

use App\Models\SiteSetting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Tambah setting baru:
     * - social.*_icon : upload ikon sosmed sendiri (opsional; kosong = ikon default)
     * - wa.card_title / wa.card_subtitle : teks kartu "Chat WhatsApp" di halaman kontak
     * - brand.favicon : favicon situs
     */
    public function up(): void
    {
        $rows = [
            // ── Ikon sosmed custom (image) ──
            ['key' => 'social.instagram_icon', 'type' => 'image', 'group' => 'sosmed', 'label' => 'Ikon Instagram (opsional)', 'value' => ''],
            ['key' => 'social.tokopedia_icon', 'type' => 'image', 'group' => 'sosmed', 'label' => 'Ikon Tokopedia (opsional)', 'value' => ''],
            ['key' => 'social.shopee_icon',    'type' => 'image', 'group' => 'sosmed', 'label' => 'Ikon Shopee (opsional)',    'value' => ''],
            ['key' => 'social.whatsapp_icon',  'type' => 'image', 'group' => 'sosmed', 'label' => 'Ikon WhatsApp (opsional)',  'value' => ''],
            ['key' => 'social.facebook_icon',  'type' => 'image', 'group' => 'sosmed', 'label' => 'Ikon Facebook (opsional)',  'value' => ''],
            ['key' => 'social.tiktok_icon',    'type' => 'image', 'group' => 'sosmed', 'label' => 'Ikon TikTok (opsional)',    'value' => ''],

            // ── Teks kartu WhatsApp (halaman kontak) ──
            ['key' => 'wa.card_title',    'type' => 'text', 'group' => 'kontak', 'label' => 'Judul Kartu WhatsApp',    'value' => 'Chat WhatsApp Sekarang'],
            ['key' => 'wa.card_subtitle', 'type' => 'text', 'group' => 'kontak', 'label' => 'Subjudul Kartu WhatsApp', 'value' => 'Respon cepat, siap membantu Anda!'],

            // ── Favicon ──
            ['key' => 'brand.favicon', 'type' => 'image', 'group' => 'brand', 'label' => 'Favicon (ikon tab browser)', 'value' => ''],
        ];

        foreach ($rows as $r) {
            SiteSetting::firstOrCreate(['key' => $r['key']], $r);
        }
    }

    public function down(): void
    {
        SiteSetting::whereIn('key', [
            'social.instagram_icon', 'social.tokopedia_icon', 'social.shopee_icon',
            'social.whatsapp_icon', 'social.facebook_icon', 'social.tiktok_icon',
            'wa.card_title', 'wa.card_subtitle', 'brand.favicon',
        ])->delete();
    }
};

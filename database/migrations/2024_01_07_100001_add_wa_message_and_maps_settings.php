<?php

use App\Models\SiteSetting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * - wa.default_message : teks pesan otomatis saat klik WhatsApp.
     * - maps.embed         : URL embed Google Maps (src dari "Sematkan peta") ATAU link share biasa.
     * Sekaligus normalisasi: ubah label social.whatsapp menjadi "Nomor WhatsApp" agar admin
     * mengisi NOMOR saja, bukan URL.
     */
    public function up(): void
    {
        SiteSetting::firstOrCreate(
            ['key' => 'wa.default_message'],
            ['type' => 'textarea', 'group' => 'kontak', 'label' => 'Pesan Default WhatsApp', 'value' => 'Halo, saya ingin bertanya tentang produk NIVICO.']
        );

        SiteSetting::firstOrCreate(
            ['key' => 'maps.embed'],
            ['type' => 'textarea', 'group' => 'kontak', 'label' => 'Google Maps (URL embed / link share)', 'value' => '']
        );

        // Perjelas bahwa kolom WhatsApp diisi NOMOR, bukan URL.
        $wa = SiteSetting::where('key', 'social.whatsapp')->first();
        if ($wa) {
            $wa->update(['label' => 'Nomor WhatsApp (mis. 0812xxxx)']);
        }
    }

    public function down(): void
    {
        SiteSetting::whereIn('key', ['wa.default_message', 'maps.embed'])->delete();
        $wa = SiteSetting::where('key', 'social.whatsapp')->first();
        if ($wa) {
            $wa->update(['label' => 'WhatsApp URL']);
        }
    }
};

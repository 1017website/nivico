<?php

use App\Models\SiteSetting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $settings = [
            ['wholesale.enabled', 'boolean', 'Aktifkan Popup Grosir', '1'],
            ['wholesale.title', 'text', 'Judul Popup Grosir', 'Butuh Harga Grosir?'],
            ['wholesale.subtitle', 'textarea', 'Subjudul Popup Grosir', 'Dapatkan penawaran khusus untuk pembelian dalam jumlah besar.'],
            ['wholesale.button_text', 'text', 'Teks Tombol Grosir', 'Hubungi via WhatsApp'],
            ['wholesale.message', 'textarea', 'Pesan WhatsApp Grosir', 'Halo NIVICO, saya ingin bertanya mengenai harga dan pemesanan grosir.'],
        ];

        foreach ($settings as [$key, $type, $label, $value]) {
            SiteSetting::firstOrCreate(
                ['key' => $key],
                ['type' => $type, 'group' => 'popup', 'label' => $label, 'value' => $value]
            );
        }
    }

    public function down(): void
    {
        SiteSetting::whereIn('key', [
            'wholesale.enabled',
            'wholesale.title',
            'wholesale.subtitle',
            'wholesale.button_text',
            'wholesale.message',
        ])->delete();
    }
};

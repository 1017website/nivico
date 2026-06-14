<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            // ── HERO SLIDER (JSON array slide) ──
            ['hero.slides', 'json', 'hero', 'Slide Hero', json_encode([
                [
                    'title1' => 'NIVICO',
                    'title2' => 'Electronic Mart',
                    'desc'   => 'Pusat kebutuhan elektronik, aksesoris, tools, kabel, microphone, adaptor dan perlengkapan rumah tangga dengan harga terbaik.',
                    'image'  => 'https://images.unsplash.com/photo-1550009158-9ebf69173e03?w=900&q=80&auto=format&fit=crop',
                    'cta_text' => '', 'cta_link' => '',
                ],
                [
                    'title1' => 'Flash Sale',
                    'title2' => 'Diskon 50%',
                    'desc'   => 'Penawaran terbatas untuk produk elektronik pilihan!',
                    'image'  => 'https://images.unsplash.com/photo-1607082349566-187342175e2f?w=900&q=80&auto=format&fit=crop',
                    'cta_text' => 'Lihat Promo →', 'cta_link' => '/promo',
                ],
                [
                    'title1' => 'Koleksi',
                    'title2' => 'Terbaru 2024',
                    'desc'   => 'Produk elektronik terbaru dengan kualitas premium dan harga terjangkau.',
                    'image'  => 'https://images.unsplash.com/photo-1498049794561-7780e7231661?w=900&q=80&auto=format&fit=crop',
                    'cta_text' => 'Lihat Produk →', 'cta_link' => '/produk',
                ],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)],

            // ── HERO PERKS (4 keunggulan) ──
            ['hero.perks', 'json', 'hero', 'Keunggulan (Perks)', json_encode([
                ['t1' => 'Produk', 't2' => 'Berkualitas'],
                ['t1' => 'Pengiriman', 't2' => 'Cepat'],
                ['t1' => 'Pembayaran', 't2' => 'Aman'],
                ['t1' => 'Layanan', 't2' => 'Terbaik'],
            ], JSON_UNESCAPED_UNICODE)],

            // ── PROMO BANNER (PENAWARAN SPESIAL) ──
            ['banner.promos', 'json', 'banner', 'Banner Penawaran Spesial', json_encode([
                [
                    'tag' => '🔥 Promo Spesial', 'title' => 'Belanja Min. Rp200rb<br>Diskon 20%',
                    'btn' => 'Klaim Sekarang →', 'link' => '/promo',
                    'image' => 'https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?w=800&q=80&auto=format&fit=crop',
                ],
                [
                    'tag' => '⚡ Flash Deal', 'title' => 'Gratis Ongkir<br>Seluruh Indonesia',
                    'btn' => 'Belanja Sekarang →', 'link' => '/promo',
                    'image' => 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?w=800&q=80&auto=format&fit=crop',
                ],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)],

            // ── LABEL SECTION ──
            ['section.new_title',    'text', 'label', 'Judul: Produk Terbaru',    'PRODUK TERBARU'],
            ['section.flash_title',  'text', 'label', 'Judul: Flash Sale',        '⚡ Flash Sale'],
            ['section.promo_title',  'text', 'label', 'Judul: Penawaran Spesial', 'PENAWARAN SPESIAL'],
            ['section.best_title',   'text', 'label', 'Judul: Best Seller',       'BEST SELLER'],

            // ── COUNTDOWN FLASH SALE ──
            ['flashsale.enabled', 'boolean', 'flashsale', 'Aktifkan Countdown', '1'],
            ['flashsale.ends_at', 'text',    'flashsale', 'Waktu Berakhir (YYYY-MM-DD HH:MM)', now()->addDay()->format('Y-m-d H:i')],
            ['flashsale.label',   'text',    'flashsale', 'Label Countdown', 'Berakhir dalam:'],

            // ── POPUP PROMO ──
            ['popup.enabled',  'boolean', 'popup', 'Aktifkan Popup', '1'],
            ['popup.tag',      'text',    'popup', 'Tag Atas',        'Selamat Datang!'],
            ['popup.title',    'text',    'popup', 'Judul Popup',     'Diskon Spesial<br>Untuk Kamu'],
            ['popup.subtitle', 'textarea','popup', 'Subjudul',        'Gunakan kode di bawah ini dan hemat langsung di pembelian pertamamu!'],
            ['popup.promo_code','text',   'popup', 'Kode Promo (kosong = ambil otomatis)', ''],
            ['popup.btn_text', 'text',    'popup', 'Teks Tombol',     '🛒 Lihat Semua Promo'],

            // ── BRAND / LOGO ──
            ['brand.name',    'text',  'brand', 'Nama Toko',  'NIVICO'],
            ['brand.tagline', 'text',  'brand', 'Tagline',    'Electronic Mart'],
            ['brand.logo',    'image', 'brand', 'Logo (kosong = teks)', ''],

            // ── KONTAK & SOSMED (footer) ──
            ['contact.address', 'textarea', 'kontak', 'Alamat',  'Surabaya, Jawa Timur, Indonesia'],
            ['contact.phone',   'text',     'kontak', 'Telepon', '+62 895-3393-46702'],
            ['contact.email',   'text',     'kontak', 'Email',   'cs@nivico.id'],
            ['contact.hours',   'text',     'kontak', 'Jam Operasional', 'Senin–Sabtu, 08.00–17.00 WIB'],

            ['social.instagram', 'text', 'sosmed', 'Instagram URL', ''],
            ['social.whatsapp',  'text', 'sosmed', 'WhatsApp URL',  ''],
            ['social.facebook',  'text', 'sosmed', 'Facebook URL',  ''],
            ['social.tiktok',    'text', 'sosmed', 'TikTok URL',    ''],
            ['social.tokopedia', 'text', 'sosmed', 'Tokopedia URL', ''],
            ['social.shopee',    'text', 'sosmed', 'Shopee URL',    ''],

            // ── FOOTER ──
            ['footer.about',     'textarea', 'footer', 'Tentang (footer)', 'NIVICO Electronic Mart — pusat kebutuhan elektronik terpercaya dengan harga terbaik.'],
            ['footer.copyright', 'text',     'footer', 'Copyright', '© '.date('Y').' NIVICO Electronic Mart. All rights reserved.'],
        ];

        foreach ($defaults as [$key, $type, $group, $label, $value]) {
            SiteSetting::firstOrCreate(
                ['key' => $key],
                ['type' => $type, 'group' => $group, 'label' => $label, 'value' => $value]
            );
        }
    }
}

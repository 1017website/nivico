<?php

namespace Database\Seeders;

use App\Models\SeoSetting;
use Illuminate\Database\Seeder;

class SeoSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['page_key' => 'global', 'title' => 'NIVICO Electronic Mart — Belanja Elektronik Terpercaya', 'meta_description' => 'Pusat kebutuhan elektronik, kabel, microphone, adaptor, baterai, tools, audio, lampu LED & perlengkapan rumah tangga dengan harga terbaik.', 'meta_keywords' => 'elektronik, kabel, adaptor, baterai, microphone, tools, audio, lampu led'],
            ['page_key' => 'home', 'title' => 'NIVICO Electronic Mart — Belanja Elektronik Online', 'meta_description' => 'Belanja produk elektronik original bergaransi dengan harga kompetitif. Gratis ongkir & banyak promo menarik.'],
            ['page_key' => 'products', 'title' => 'Katalog Produk — NIVICO Electronic Mart', 'meta_description' => 'Jelajahi ribuan produk elektronik: kabel, adaptor, microphone, tools, audio, dan lainnya.'],
            ['page_key' => 'promo', 'title' => 'Promo & Penawaran — NIVICO Electronic Mart', 'meta_description' => 'Diskon, voucher, gratis ongkir, dan cashback untuk belanja elektronik kebutuhanmu.'],
            ['page_key' => 'about', 'title' => 'Tentang Kami — NIVICO Electronic Mart', 'meta_description' => 'Mengenal NIVICO Electronic Mart, toko elektronik terpercaya sejak 2015.'],
            ['page_key' => 'contact', 'title' => 'Kontak — NIVICO Electronic Mart', 'meta_description' => 'Hubungi NIVICO Electronic Mart melalui telepon, email, atau WhatsApp.'],
        ];
        foreach ($rows as $r) {
            SeoSetting::updateOrCreate(['page_key' => $r['page_key']], $r);
        }
    }
}

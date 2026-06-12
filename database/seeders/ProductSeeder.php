<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $cats = Category::pluck('id', 'name');

        $products = [
            ['name' => 'Dynamic Microphone Pro 318', 'price' => 55250, 'old' => null, 'badge' => 'NEW', 'cat' => 'Microphone', 'rat' => 4.8, 'ratN' => 120, 'img' => 'https://images.unsplash.com/photo-1598300042247-d088f8ab3a91?w=400&q=80', 'desc' => 'Microphone dinamis profesional dengan respons frekuensi 50Hz–16kHz. Cocok untuk karaoke, presentasi, rekaman vokal, dan pertunjukan live.', 'sku' => 'MIC-318-DYN', 'flash' => false],
            ['name' => 'Power Adaptor 12V 2A Universal', 'price' => 120000, 'old' => 150000, 'badge' => 'NEW', 'cat' => 'Adaptor & Charger', 'rat' => 4.9, 'ratN' => 89, 'img' => 'https://images.unsplash.com/photo-1588508065123-287b28e013da?w=400&q=80', 'desc' => 'Adaptor switching universal 12V 2A, input 100–240V AC. Proteksi arus lebih, tegangan lebih, dan hubung singkat.', 'sku' => 'ADP-12V2A', 'flash' => true],
            ['name' => 'Baterai Alkaline AA Pack 4 Pcs', 'price' => 27500, 'old' => null, 'badge' => 'NEW', 'cat' => 'Baterai', 'rat' => 4.9, 'ratN' => 210, 'img' => 'https://images.unsplash.com/photo-1609091839311-d5365f9ff1c5?w=400&q=80', 'desc' => 'Baterai alkaline AA berkualitas tinggi, tahan lama hingga 5 tahun penyimpanan. Tegangan 1.5V per baterai.', 'sku' => 'BAT-AA4-ALK', 'flash' => false],
            ['name' => 'Kabel Listrik NYM 3x1.5 10 Meter', 'price' => 98000, 'old' => null, 'badge' => null, 'cat' => 'Kabel & Kawat', 'rat' => 4.8, 'ratN' => 64, 'img' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=400&q=80', 'desc' => 'Kabel listrik NYM 3 inti 1.5mm² panjang 10 meter. Material tembaga berkualitas tinggi dengan isolasi PVC. Standar SNI.', 'sku' => 'KBL-NYM315', 'flash' => false],
            ['name' => 'Klem Kabel Round Cable Clip', 'price' => 7500, 'old' => null, 'badge' => null, 'cat' => 'Tools', 'rat' => 4.7, 'ratN' => 78, 'img' => 'https://images.unsplash.com/photo-1518770660439-4636190af475?w=400&q=80', 'desc' => 'Klem kabel bulat untuk menata kabel di dinding. Material plastik ABS kuat. Isi 20 pcs per pack.', 'sku' => 'KLM-RND-20', 'flash' => false],
            ['name' => 'Kabel Power IEC C13 1.5 Meter', 'price' => 22000, 'old' => null, 'badge' => null, 'cat' => 'Kabel & Kawat', 'rat' => 4.8, 'ratN' => 96, 'img' => 'https://images.unsplash.com/photo-1526406915894-7bcd65f60845?w=400&q=80', 'desc' => 'Kabel power IEC C13 ke Schuko panjang 1.5 meter. Cocok untuk monitor, printer, dan perangkat audio.', 'sku' => 'KBL-IEC-C13', 'flash' => false],
            ['name' => 'Kabel USB Type-C 1M Fast Charge', 'price' => 18900, 'old' => 25000, 'badge' => 'HOT', 'cat' => 'Kabel & Kawat', 'rat' => 4.9, 'ratN' => 1200, 'img' => 'https://images.unsplash.com/photo-1583394838336-acd977736f90?w=400&q=80', 'desc' => 'Kabel USB Type-C 1 meter, fast charging 65W dan transfer data 480Mbps. Material nylon braided tahan banting.', 'sku' => 'KBL-USBC-1M', 'flash' => true],
            ['name' => 'Speaker Bluetooth Mini Portable', 'price' => 145000, 'old' => 185000, 'badge' => 'HOT', 'cat' => 'Audio', 'rat' => 4.8, 'ratN' => 876, 'img' => 'https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?w=400&q=80', 'desc' => 'Speaker bluetooth BT 5.0, output 5W, IPX5 waterproof. Baterai 1200mAh tahan 6 jam.', 'sku' => 'SPK-BT-MINI', 'flash' => true],
            ['name' => 'Solder Elektrik 40W Set Lengkap', 'price' => 38000, 'old' => 55000, 'badge' => 'HOT', 'cat' => 'Tools', 'rat' => 4.9, 'ratN' => 543, 'img' => 'https://images.unsplash.com/photo-1621905251918-48416bd8575a?w=400&q=80', 'desc' => 'Set solder 40W lengkap dengan dudukan, timah, dan 5 mata solder pengganti. Pemanas cepat 3 menit.', 'sku' => 'SLD-40W-SET', 'flash' => true],
            ['name' => 'Lampu LED Bulb 10W Hemat Energi', 'price' => 12000, 'old' => null, 'badge' => 'HOT', 'cat' => 'Lampu & LED', 'rat' => 4.8, 'ratN' => 1500, 'img' => 'https://images.unsplash.com/photo-1586210579191-33b45e38fa2c?w=400&q=80', 'desc' => 'Lampu LED 10W setara 85W pijar, tahan 25.000 jam. Fitting E27, 220–240V AC. Hemat listrik 88%.', 'sku' => 'LMP-LED-10W', 'flash' => true],
            ['name' => 'Multimeter Digital Auto Range', 'price' => 89000, 'old' => 120000, 'badge' => 'HOT', 'cat' => 'Tools', 'rat' => 4.7, 'ratN' => 389, 'img' => 'https://images.unsplash.com/photo-1585771724684-38269d6639fd?w=400&q=80', 'desc' => 'Multimeter digital MAS838, LCD backlight. Ukur tegangan AC/DC, arus, resistansi, kapasitansi, frekuensi.', 'sku' => 'MTR-DIGI-838', 'flash' => true],
            ['name' => 'Stop Kontak 6 Lubang 3 Meter', 'price' => 42500, 'old' => 50000, 'badge' => null, 'cat' => 'Rumah Tangga', 'rat' => 4.8, 'ratN' => 321, 'img' => 'https://images.unsplash.com/photo-1621905252472-943afaa20e20?w=400&q=80', 'desc' => 'Stop kontak 6 lubang kabel 3 meter, switch on/off dan proteksi arus lebih. Rating 10A/2200W.', 'sku' => 'STK-6LBG-3M', 'flash' => false],
        ];

        foreach ($products as $p) {
            Product::create([
                'category_id'  => $cats[$p['cat']] ?? $cats->first(),
                'name'         => $p['name'],
                'slug'         => Str::slug($p['name']),
                'sku'          => $p['sku'],
                'price'        => $p['price'],
                'old_price'    => $p['old'],
                'badge'        => $p['badge'],
                'description'  => $p['desc'],
                'image'        => $p['img'],
                'stock'        => rand(8, 120),
                'rating'       => $p['rat'],
                'rating_count' => $p['ratN'],
                'sold'         => rand(20, 800),
                'is_flash_sale'=> $p['flash'],
                'is_active'    => true,
            ]);
        }
    }
}

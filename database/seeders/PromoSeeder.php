<?php

namespace Database\Seeders;

use App\Models\Promo;
use Illuminate\Database\Seeder;

class PromoSeeder extends Seeder
{
    public function run(): void
    {
        $promos = [
            ['code' => 'NIVICO10', 'title' => 'Diskon 10% Member Baru', 'description' => 'Potongan 10% maksimal Rp25.000 untuk pembelian pertama.', 'type' => 'percent', 'value' => 10, 'max_discount' => 25000, 'min_purchase' => 50000, 'badge' => 'Member', 'image' => 'https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?w=700&q=80', 'expires_at' => now()->addMonths(2)],
            ['code' => 'KABEL30', 'title' => 'Diskon 30% Kategori Kabel', 'description' => 'Khusus produk kabel & kawat, potongan hingga Rp50.000.', 'type' => 'percent', 'value' => 30, 'max_discount' => 50000, 'min_purchase' => 100000, 'badge' => 'Flash Sale', 'image' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=700&q=80', 'expires_at' => now()->addWeeks(2)],
            ['code' => 'NIVICO50', 'title' => 'Potongan Rp50.000', 'description' => 'Potongan langsung Rp50.000 untuk belanja minimal Rp300.000.', 'type' => 'fixed', 'value' => 50000, 'max_discount' => null, 'min_purchase' => 300000, 'badge' => 'Voucher', 'image' => 'https://images.unsplash.com/photo-1556742502-ec7c0e9f34b1?w=700&q=80', 'expires_at' => now()->addMonth()],
            ['code' => 'GRATIS', 'title' => 'Gratis Ongkir Seluruh Indonesia', 'description' => 'Bebas ongkir tanpa minimal pembelian untuk semua kurir.', 'type' => 'free_shipping', 'value' => 0, 'max_discount' => null, 'min_purchase' => 0, 'badge' => 'Gratis Ongkir', 'image' => 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?w=700&q=80', 'expires_at' => now()->addMonth()],
            ['code' => 'GOPAY10', 'title' => 'Cashback 10% GoPay', 'description' => 'Cashback 10% maksimal Rp20.000 untuk pembayaran via GoPay.', 'type' => 'percent', 'value' => 10, 'max_discount' => 20000, 'min_purchase' => 75000, 'badge' => 'Cashback', 'image' => 'https://images.unsplash.com/photo-1556742205-e10c9486e506?w=700&q=80', 'expires_at' => now()->addWeeks(3)],
            ['code' => 'TOOLS40', 'title' => 'Diskon 40% Tools', 'description' => 'Khusus kategori tools & perkakas, potongan hingga Rp60.000.', 'type' => 'percent', 'value' => 40, 'max_discount' => 60000, 'min_purchase' => 100000, 'badge' => 'Flash Sale', 'image' => 'https://images.unsplash.com/photo-1581147036324-c1c89c2c8b5c?w=700&q=80', 'expires_at' => now()->addWeeks(1)],
        ];

        foreach ($promos as $p) {
            Promo::create($p + ['is_active' => true]);
        }
    }
}

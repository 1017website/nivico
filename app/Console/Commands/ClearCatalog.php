<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Mengosongkan data katalog (produk dummy) sebelum import Shopee.
 *
 * Yang DIHAPUS: products, product_variants, product_images, stock_movements,
 * cart_items (+ carts), dan flash_sale_items bila ada. Kategori opsional.
 *
 * Yang AMAN (tidak disentuh): orders & order_items (menyimpan snapshot produk),
 * users, roles, settings, banks, promos.
 *
 * Pakai:
 *   php artisan catalog:clear                 (hapus produk + kategori, dengan konfirmasi)
 *   php artisan catalog:clear --keep-categories
 *   php artisan catalog:clear --force         (tanpa konfirmasi, untuk otomasi)
 */
class ClearCatalog extends Command
{
    protected $signature = 'catalog:clear
        {--keep-categories : Jangan hapus tabel categories}
        {--force : Lewati konfirmasi}';

    protected $description = 'Kosongkan data katalog (produk/varian/gambar) sebelum import. Order lama tetap aman.';

    public function handle(): int
    {
        $keepCat = (bool) $this->option('keep-categories');

        // Hitung dulu untuk ditampilkan
        $counts = [
            'products'         => $this->count('products'),
            'product_variants' => $this->count('product_variants'),
            'product_images'   => $this->count('product_images'),
            'stock_movements'  => $this->count('stock_movements'),
            'cart_items'       => $this->count('cart_items'),
            'carts'            => $this->count('carts'),
        ];
        if (! $keepCat) {
            $counts['categories'] = $this->count('categories');
        }

        $this->warn('Data berikut akan DIHAPUS PERMANEN:');
        $this->table(['Tabel', 'Jumlah baris'], collect($counts)->map(fn ($v, $k) => [$k, $v])->values()->all());
        $this->line('Aman (TIDAK dihapus): orders, order_items, users, settings, promos, banks.');

        if (! $this->option('force') && ! $this->confirm('Lanjutkan menghapus data di atas?', false)) {
            $this->info('Dibatalkan.');
            return self::SUCCESS;
        }

        // Matikan FK check sesaat agar truncate lintas-relasi tidak error (MySQL).
        $driver = DB::getDriverName();
        $this->disableFk($driver);

        try {
            // Urut dari anak ke induk
            $this->truncate('cart_items');
            $this->truncate('carts');
            $this->truncate('stock_movements');
            $this->truncate('product_images');
            $this->truncate('product_variants');
            $this->truncate('flash_sale_items'); // bila ada
            $this->truncate('products');
            if (! $keepCat) {
                $this->truncate('categories');
            }
        } finally {
            $this->enableFk($driver);
        }

        $this->newLine();
        $this->info('Katalog dikosongkan. Siap untuk: php artisan shopee:import');
        return self::SUCCESS;
    }

    private function count(string $table): int
    {
        return Schema::hasTable($table) ? (int) DB::table($table)->count() : 0;
    }

    private function truncate(string $table): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }
        DB::table($table)->truncate();
        $this->line("  ✓ $table dikosongkan");
    }

    private function disableFk(string $driver): void
    {
        match ($driver) {
            'mysql'  => DB::statement('SET FOREIGN_KEY_CHECKS=0'),
            'sqlite' => DB::statement('PRAGMA foreign_keys = OFF'),
            'pgsql'  => null, // pgsql: truncate ... cascade ditangani manual bila perlu
            default  => null,
        };
    }

    private function enableFk(string $driver): void
    {
        match ($driver) {
            'mysql'  => DB::statement('SET FOREIGN_KEY_CHECKS=1'),
            'sqlite' => DB::statement('PRAGMA foreign_keys = ON'),
            default  => null,
        };
    }
}

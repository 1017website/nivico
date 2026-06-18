<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Importer katalog Shopee Seller Centre -> NIVICO.
 *
 * Membaca 5 file export Shopee (format CSV) dan menggabungkannya berdasarkan
 * product_id, lalu membuat Product (+ ProductVariant untuk produk bervarian,
 * + ProductImage untuk galeri).
 *
 * CARA PAKAI:
 *   1. Buka tiap file .xlsx Shopee di Excel, lalu "Save As" -> CSV UTF-8.
 *      Simpan ke folder: storage/app/shopee/
 *      Penamaan bebas, asalkan mengandung kata kunci berikut:
 *        - basic     (mass_update_basic_info...)
 *        - sales     (mass_update_sales_info...)
 *        - dts       (mass_update_dts_info...)
 *        - shipping  (mass_update_shipping_info...)
 *        - media     (mass_update_media_info...)
 *   2. Jalankan:
 *        php artisan shopee:import
 *        php artisan shopee:import --dry-run     (uji tanpa simpan)
 *        php artisan shopee:import --images=download   (unduh foto ke lokal)
 *
 * Foto: default disimpan sebagai URL Shopee apa adanya (kolom image berisi URL).
 *       Dengan --images=download, foto diunduh ke storage/app/public/products.
 */
class ImportShopeeCatalog extends Command
{
    protected $signature = 'shopee:import
        {--path=shopee : Folder relatif di storage/app yang berisi CSV Shopee}
        {--dry-run : Jalankan tanpa menyimpan ke database}
        {--images=url : url|download — simpan URL Shopee, atau unduh ke lokal}
        {--fresh : Hapus produk hasil import sebelumnya (berdasar prefix SKU) sebelum import}';

    protected $description = 'Import katalog produk dari file export Shopee Seller Centre';

    /** Kolom teknis Shopee (baris pertama header). */
    private const COL = [
        'pid'        => 'et_title_product_id',
        'parent_sku' => 'et_title_parent_sku',
        'name'       => 'et_title_product_name',
        'desc'       => 'et_title_product_description',
        'category'   => 'et_title_product_category',
        'weight'     => 'et_title_product_weight',
        'var_name'   => 'et_title_variation_name',
        'var_sku'    => 'et_title_variation_sku',
        'var_price'  => 'et_title_variation_price',
        'var_stock'  => 'et_title_variation_stock',
        'cover'      => 'ps_item_cover_image',
    ];

    private bool $dry = false;
    private string $imageMode = 'url';

    public function handle(): int
    {
        $this->dry = (bool) $this->option('dry-run');
        $this->imageMode = $this->option('images') === 'download' ? 'download' : 'url';

        $dir = storage_path('app/'.trim($this->option('path'), '/'));
        if (! is_dir($dir)) {
            $this->error("Folder tidak ditemukan: $dir");
            $this->line('Buat folder lalu taruh CSV Shopee di sana.');
            return self::FAILURE;
        }

        // Petakan file berdasarkan kata kunci di nama
        $files = $this->locateFiles($dir);
        foreach (['basic', 'sales', 'dts', 'shipping', 'media'] as $key) {
            if (empty($files[$key])) {
                $this->error("File '$key' tidak ditemukan di $dir (nama harus mengandung '$key').");
                return self::FAILURE;
            }
        }
        $this->info('File terdeteksi:');
        foreach ($files as $k => $f) {
            $this->line("  [$k] ".basename($f));
        }

        // Baca tiap file -> array baris asosiatif
        $basic = $this->readCsv($files['basic']);
        $sales = $this->readCsv($files['sales']);
        $dts   = $this->readCsv($files['dts']);
        $ship  = $this->readCsv($files['shipping']);
        $media = $this->readCsv($files['media']);

        // Index per product_id (ambil baris pertama per pid untuk data level-produk)
        $names   = $this->firstByPid($basic, self::COL['name']);
        $descs   = $this->firstByPid($basic, self::COL['desc']);
        $cats    = $this->firstByPid($dts, self::COL['category']);
        $weights = $this->firstByPid($ship, self::COL['weight']);
        $covers  = $this->firstByPid($media, self::COL['cover']);
        $gallery = $this->galleryByPid($media);

        // Bangun struktur produk dari sales (sumber harga/stok/varian)
        $products = [];
        foreach ($sales as $r) {
            $pid = $this->clean($r[self::COL['pid']] ?? '');
            if ($pid === '') {
                continue;
            }
            if (! isset($products[$pid])) {
                $products[$pid] = [
                    'pid'         => $pid,
                    'name'        => $names[$pid] ?? '',
                    'description' => $descs[$pid] ?? '',
                    'category'    => $cats[$pid] ?? '',
                    'weight'      => $this->num($weights[$pid] ?? 0),
                    'parent_sku'  => $this->clean($r[self::COL['parent_sku']] ?? ''),
                    'cover'       => $covers[$pid] ?? '',
                    'gallery'     => $gallery[$pid] ?? [],
                    'variants'    => [],
                ];
            }
            $products[$pid]['variants'][] = [
                'name'  => $this->clean($r[self::COL['var_name']] ?? ''),
                'sku'   => $this->clean($r[self::COL['var_sku']] ?? ''),
                'price' => $this->num($r[self::COL['var_price']] ?? 0),
                'stock' => $this->num($r[self::COL['var_stock']] ?? 0),
            ];
        }

        $this->newLine();
        $this->info('Total produk terbaca: '.count($products));
        if ($this->dry) {
            $this->warn('MODE DRY-RUN — tidak ada yang disimpan.');
        }

        $stat = ['single' => 0, 'variant' => 0, 'variants_total' => 0, 'images' => 0, 'skipped' => 0];
        $catCache = [];

        $bar = $this->output->createProgressBar(count($products));
        $bar->start();

        foreach ($products as $p) {
            if ($p['name'] === '') {
                $stat['skipped']++;
                $bar->advance();
                continue;
            }

            $realVariants = array_values(array_filter($p['variants'], fn ($v) => $v['name'] !== ''));
            $isVariant = count($realVariants) > 1;

            if ($this->dry) {
                $isVariant ? $stat['variant']++ : $stat['single']++;
                $stat['variants_total'] += $isVariant ? count($realVariants) : 0;
                $bar->advance();
                continue;
            }

            DB::transaction(function () use ($p, $isVariant, $realVariants, &$stat, &$catCache) {
                $categoryId = $this->resolveCategory($p['category'], $catCache);

                // Harga & stok level produk: dari varian termurah / total bila bervarian
                $variantsForCalc = $isVariant ? $realVariants : $p['variants'];
                $minPrice = min(array_map(fn ($v) => $v['price'] ?: PHP_INT_MAX, $variantsForCalc));
                $minPrice = $minPrice === PHP_INT_MAX ? 0 : $minPrice;
                $totalStock = array_sum(array_map(fn ($v) => $v['stock'], $variantsForCalc));

                $sku = $this->makeSku($p, $variantsForCalc);

                $product = Product::create([
                    'category_id'  => $categoryId,
                    'name'         => $p['name'],
                    'slug'         => $this->uniqueSlug($p['name']),
                    'sku'          => $sku,
                    'price'        => $minPrice,
                    'old_price'    => null,
                    'description'  => $p['description'] ?: null,
                    'image'        => $this->handleImage($p['cover']),
                    'stock'        => $isVariant ? 0 : $totalStock,
                    'weight'       => $p['weight'] ?: config('rajaongkir.default_weight'),
                    'is_active'    => true,
                    'has_variants' => $isVariant,
                ]);

                if ($isVariant) {
                    foreach ($realVariants as $i => $v) {
                        ProductVariant::create([
                            'product_id' => $product->id,
                            'name'       => $v['name'],
                            'sku'        => $v['sku'] ?: null,
                            'price'      => $v['price'],
                            'stock'      => $v['stock'],
                            'weight'     => $p['weight'] ?: null,
                            'sort_order' => $i,
                            'is_active'  => true,
                        ]);
                    }
                    $stat['variant']++;
                    $stat['variants_total'] += count($realVariants);
                } else {
                    $stat['single']++;
                }

                // Galeri foto
                foreach ($p['gallery'] as $i => $url) {
                    $path = $this->handleImage($url);
                    if ($path) {
                        ProductImage::create([
                            'product_id' => $product->id,
                            'path'       => $path,
                            'sort_order' => $i,
                        ]);
                        $stat['images']++;
                    }
                }
            });

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('=== RINGKASAN ===');
        $this->table(
            ['Metrik', 'Jumlah'],
            [
                ['Produk single', $stat['single']],
                ['Produk bervarian', $stat['variant']],
                ['Total varian dibuat', $stat['variants_total']],
                ['Foto galeri', $stat['images']],
                ['Dilewati (tanpa nama)', $stat['skipped']],
            ]
        );

        if ($this->dry) {
            $this->warn('Dry-run selesai. Jalankan tanpa --dry-run untuk menyimpan.');
        } else {
            $this->info('Import selesai.');
        }

        return self::SUCCESS;
    }

    /** Petakan file di folder berdasarkan kata kunci nama. */
    private function locateFiles(string $dir): array
    {
        $map = [];
        foreach (glob($dir.'/*.{csv,CSV}', GLOB_BRACE) as $f) {
            $base = strtolower(basename($f));
            foreach (['basic', 'sales', 'dts', 'shipping', 'media'] as $key) {
                if (str_contains($base, $key) && empty($map[$key])) {
                    $map[$key] = $f;
                }
            }
        }
        return $map;
    }

    /**
     * Baca CSV Shopee. Baris pertama yang memuat 'et_title_product_id'
     * dipakai sebagai header; baris data = baris pertama yang kolom pid-nya numerik.
     */
    private function readCsv(string $file): array
    {
        $rows = [];
        if (($h = fopen($file, 'r')) === false) {
            return $rows;
        }

        // Lewati BOM bila ada
        $bom = fread($h, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($h);
        }

        $header = null;
        while (($cells = fgetcsv($h, 0, ',')) !== false) {
            if ($header === null) {
                // header = baris yang mengandung kode kolom teknis Shopee
                if (in_array(self::COL['pid'], array_map('trim', $cells), true)) {
                    $header = array_map('trim', $cells);
                }
                continue;
            }
            $pid = trim($cells[array_search(self::COL['pid'], $header, true)] ?? '');
            if ($pid === '' || ! ctype_digit($pid) || strlen($pid) < 8) {
                continue; // lewati baris metadata/label
            }
            $row = [];
            foreach ($header as $i => $key) {
                $row[$key] = $cells[$i] ?? '';
            }
            $rows[] = $row;
        }
        fclose($h);
        return $rows;
    }

    private function firstByPid(array $rows, string $col): array
    {
        $out = [];
        foreach ($rows as $r) {
            $pid = $this->clean($r[self::COL['pid']] ?? '');
            if ($pid !== '' && ! isset($out[$pid])) {
                $out[$pid] = $this->clean($r[$col] ?? '');
            }
        }
        return $out;
    }

    private function galleryByPid(array $rows): array
    {
        $out = [];
        foreach ($rows as $r) {
            $pid = $this->clean($r[self::COL['pid']] ?? '');
            if ($pid === '' || isset($out[$pid])) {
                continue;
            }
            $imgs = [];
            for ($i = 1; $i <= 8; $i++) {
                $url = $this->clean($r['ps_item_image.'.$i] ?? '');
                if (str_starts_with($url, 'http')) {
                    $imgs[] = $url;
                }
            }
            $out[$pid] = $imgs;
        }
        return $out;
    }

    /** Petakan kategori Shopee "kode - Path/Sub/Sub" -> Category (pakai segmen teratas). */
    private function resolveCategory(string $raw, array &$cache): int
    {
        $name = $this->topSegment($raw) ?: 'Lainnya';
        if (isset($cache[$name])) {
            return $cache[$name];
        }
        $cat = Category::firstOrCreate(
            ['slug' => Str::slug($name)],
            ['name' => $name, 'is_active' => true]
        );
        return $cache[$name] = $cat->id;
    }

    private function topSegment(string $raw): string
    {
        if ($raw === '') {
            return '';
        }
        $path = str_contains($raw, ' - ') ? explode(' - ', $raw, 2)[1] : $raw;
        return trim(explode('/', $path)[0]);
    }

    /** Slug unik: tambah sufiks bila bentrok. */
    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'produk';
        $slug = $base;
        $n = 1;
        while (Product::where('slug', $slug)->exists()) {
            $slug = $base.'-'.(++$n);
        }
        return $slug;
    }

    /** SKU produk: pakai parent_sku, atau var_sku pertama, atau generate dari pid. */
    private function makeSku(array $p, array $variants): string
    {
        $candidate = $p['parent_sku'] ?: ($variants[0]['sku'] ?? '');
        $candidate = trim($candidate);
        if ($candidate === '') {
            $candidate = 'SHP-'.$p['pid'];
        }
        // Pastikan unik di tabel products
        $base = $candidate;
        $n = 1;
        while (Product::where('sku', $candidate)->exists()) {
            $candidate = $base.'-'.(++$n);
        }
        return $candidate;
    }

    /** Tangani foto: simpan URL apa adanya, atau unduh ke storage publik. */
    private function handleImage(string $url): ?string
    {
        $url = trim($url);
        if ($url === '' || ! str_starts_with($url, 'http')) {
            return null;
        }
        if ($this->imageMode === 'url') {
            return $url;
        }
        // Mode download
        try {
            $data = @file_get_contents($url);
            if ($data === false) {
                return $url; // fallback ke URL bila gagal
            }
            $name = 'products/'.Str::random(20).'.jpg';
            \Storage::disk('public')->put($name, $data);
            return $name;
        } catch (\Throwable $e) {
            return $url;
        }
    }

    private function clean($v): string
    {
        $s = trim((string) $v);
        return in_array($s, ['nan', 'None', 'NaT', 'NULL'], true) ? '' : $s;
    }

    private function num($v): int
    {
        $s = preg_replace('/[^\d]/', '', (string) $v);
        return $s === '' ? 0 : (int) $s;
    }
}

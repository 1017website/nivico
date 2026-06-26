<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Importer produk dari file export Shopee (Mass Update / Mass Edit).
 *
 * Pendekatan: parser CSV native PHP — TANPA dependency tambahan
 * (PhpSpreadsheet/calamine) agar aman dijalankan di shared hosting cPanel
 * tanpa SSH/Composer. Bila file Shopee berupa .xlsx, minta user "Save As CSV"
 * dari Excel/Spreadsheet terlebih dulu, atau gunakan menu export CSV Shopee.
 *
 * Format Shopee mengelompokkan baris per "Kode Produk" (nomor produk induk):
 * satu produk bisa punya banyak baris bila bervarian. Service ini meng-group
 * baris berdasarkan kode produk, lalu menjadikan tiap baris sebagai varian.
 */
class ShopeeImportService
{
    /**
     * Sinonim header Shopee (ID/EN, beberapa versi template) -> kunci internal.
     * Pencocokan dilakukan secara case-insensitive & longgar (mengandung).
     */
    protected array $headerMap = [
        'product_code' => ['kode produk', 'product id', 'id produk', 'product_id'],
        'name'         => ['nama produk', 'product name', 'nama_produk'],
        'description'  => ['deskripsi produk', 'deskripsi', 'product description', 'description'],
        'sku'          => ['kode variasi', 'sku induk', 'nomor referensi sku', 'parent sku', 'sku reference no.', 'sku'],
        'variant_name' => ['nama variasi', 'variation name', 'nama_variasi', 'variation', 'opsi variasi 1'],
        'variant_sku'  => ['sku variasi', 'variation sku', 'sku_variasi'],
        'price'        => ['harga', 'price', 'harga variasi', 'variation price'],
        'old_price'    => ['harga coret', 'harga sebelum diskon', 'original price'],
        'stock'        => ['stok', 'stock', 'jumlah stok', 'stok variasi', 'variation stock'],
        'weight'       => ['berat', 'weight', 'berat (kg)', 'berat produk'],
        'image'        => ['foto produk', 'url gambar', 'gambar', 'foto sampul', 'image', 'cover image url'],
        'category'     => ['kategori', 'category', 'nama kategori'],
    ];

    /**
     * Parse file CSV menjadi array baris ter-normalisasi (preview).
     * Mengembalikan ['headers'=>[], 'rows'=>[], 'mapped'=>[], 'errors'=>[]].
     */
    public function parse(string $path): array
    {
        $errors = [];
        if (! is_readable($path)) {
            return ['headers' => [], 'rows' => [], 'mapped' => [], 'errors' => ['File tidak terbaca.']];
        }

        $rows = [];
        $headers = [];
        $delimiter = $this->detectDelimiter($path);

        if (($fh = fopen($path, 'r')) !== false) {
            $i = 0;
            while (($data = fgetcsv($fh, 0, $delimiter)) !== false) {
                // lewati baris benar-benar kosong
                if (count($data) === 1 && trim((string) $data[0]) === '') {
                    continue;
                }
                if ($i === 0) {
                    // bersihkan BOM UTF-8 di sel pertama
                    $data[0] = preg_replace('/^\x{FEFF}/u', '', (string) $data[0]);
                    $headers = array_map(fn ($h) => trim((string) $h), $data);
                } else {
                    $rows[] = $data;
                }
                $i++;
            }
            fclose($fh);
        }

        if (empty($headers)) {
            return ['headers' => [], 'rows' => [], 'mapped' => [], 'errors' => ['Header tidak ditemukan. Pastikan file CSV valid.']];
        }

        $colIndex = $this->resolveColumns($headers);

        // Shopee bisa punya 1-2 baris keterangan di bawah header. Abaikan baris
        // yang tidak punya nama produk maupun harga.
        $mapped = [];
        foreach ($rows as $r) {
            $get = fn ($key) => isset($colIndex[$key]) ? trim((string) ($r[$colIndex[$key]] ?? '')) : '';

            $name  = $get('name');
            $price = $this->toInt($get('price'));
            if ($name === '' && $price === 0) {
                continue;
            }

            $mapped[] = [
                'product_code' => $get('product_code'),
                'name'         => $name,
                'description'  => $get('description'),
                'sku'          => $get('sku'),
                'variant_name' => $get('variant_name'),
                'variant_sku'  => $get('variant_sku'),
                'price'        => $price,
                'old_price'    => $this->toInt($get('old_price')),
                'stock'        => $this->toInt($get('stock')),
                'weight'       => $this->toWeightGram($get('weight')),
                'image'        => $this->firstImage($get('image')),
                'category'     => $get('category'),
            ];
        }

        if (empty($mapped)) {
            $errors[] = 'Tidak ada baris produk yang valid terdeteksi. Periksa kembali kolom Nama Produk & Harga.';
        }

        return [
            'headers'  => $headers,
            'rows'     => $mapped,
            'detected' => array_keys($colIndex),
            'errors'   => $errors,
        ];
    }

    /**
     * Jalankan import dari hasil parse.
     * $options: ['category_id'=>int|null, 'default_active'=>bool, 'update_existing'=>bool]
     * Mengembalikan ringkasan ['created'=>, 'updated'=>, 'variants'=>, 'skipped'=>, 'errors'=>[]].
     */
    public function import(array $parsedRows, array $options = []): array
    {
        $defaultCategoryId = $options['category_id'] ?? null;
        $defaultActive     = $options['default_active'] ?? true;
        $updateExisting    = $options['update_existing'] ?? true;

        $summary = ['created' => 0, 'updated' => 0, 'variants' => 0, 'skipped' => 0, 'errors' => []];

        // Group baris per produk. Kunci group: product_code bila ada, jika tidak nama produk.
        $groups = [];
        foreach ($parsedRows as $row) {
            $key = $row['product_code'] !== '' ? 'C:'.$row['product_code'] : 'N:'.Str::lower($row['name']);
            $groups[$key][] = $row;
        }

        foreach ($groups as $rows) {
            try {
                DB::transaction(function () use ($rows, $defaultCategoryId, $defaultActive, $updateExisting, &$summary) {
                    $head = $rows[0];

                    if (trim($head['name']) === '') {
                        $summary['skipped']++;
                        return;
                    }

                    $categoryId = $this->resolveCategoryId($head['category'], $defaultCategoryId);

                    // Tentukan apakah produk bervarian: lebih dari 1 baris ATAU ada nama variasi.
                    $variantRows = collect($rows)->filter(fn ($r) => trim($r['variant_name']) !== '')->values();
                    $hasVariants = $variantRows->count() > 0 && ($variantRows->count() > 1 || count($rows) > 1);

                    $sku = $head['sku'] !== '' ? $head['sku'] : $this->generateSku($head['name']);

                    // Cari produk lama via SKU (idempoten untuk re-import).
                    $existing = Product::where('sku', $sku)->first();

                    $productData = [
                        'category_id' => $categoryId,
                        'name'        => $head['name'],
                        'sku'         => $sku,
                        'description' => $head['description'] ?: null,
                        'image'       => $head['image'] ?: null,
                        'weight'      => $head['weight'] ?: null,
                        'has_variants'=> $hasVariants,
                        'is_active'   => $defaultActive,
                    ];

                    if (! $hasVariants) {
                        $productData['price'] = $head['price'];
                        $productData['old_price'] = $head['old_price'] ?: null;
                        $productData['stock'] = $head['stock'];
                    } else {
                        // produk bervarian: harga = termurah, stok dibaca dari varian
                        $productData['price'] = (int) collect($rows)->min('price') ?: $head['price'];
                        $productData['stock'] = 0;
                    }

                    if ($existing) {
                        if (! $updateExisting) {
                            $summary['skipped']++;
                            return;
                        }
                        $existing->update($productData);
                        $product = $existing;
                        $summary['updated']++;
                    } else {
                        $product = Product::create($productData);
                        $summary['created']++;
                    }

                    // Sinkronisasi varian
                    if ($hasVariants) {
                        // hapus varian lama agar re-import bersih
                        $product->variants()->forceDelete();
                        foreach ($rows as $i => $r) {
                            if (trim($r['variant_name']) === '') {
                                continue;
                            }
                            $product->variants()->create([
                                'name'       => $r['variant_name'],
                                'sku'        => $r['variant_sku'] ?: null,
                                'price'      => $r['price'],
                                'old_price'  => $r['old_price'] ?: null,
                                'stock'      => $r['stock'],
                                'weight'     => $r['weight'] ?: null,
                                'image'      => $r['image'] ?: null,
                                'sort_order' => $i,
                                'is_active'  => true,
                            ]);
                            $summary['variants']++;
                        }
                        // selaraskan harga termurah dari varian aktif
                        $product->update([
                            'price' => (int) $product->variants()->where('is_active', true)->min('price') ?: $product->price,
                            'stock' => 0,
                        ]);
                    } else {
                        // pastikan tidak ada varian sisa bila berubah jadi non-varian
                        $product->variants()->forceDelete();
                    }
                });
            } catch (\Throwable $e) {
                $summary['errors'][] = ($rows[0]['name'] ?? 'Produk').': '.$e->getMessage();
            }
        }

        return $summary;
    }

    /** Deteksi delimiter (Shopee ID kadang pakai ; ). */
    protected function detectDelimiter(string $path): string
    {
        $line = '';
        if (($fh = fopen($path, 'r')) !== false) {
            $line = (string) fgets($fh);
            fclose($fh);
        }
        $candidates = [',' => substr_count($line, ','), ';' => substr_count($line, ';'), "\t" => substr_count($line, "\t")];
        arsort($candidates);
        return array_key_first($candidates) ?: ',';
    }

    /** Petakan posisi kolom berdasarkan header. */
    protected function resolveColumns(array $headers): array
    {
        $norm = array_map(fn ($h) => Str::lower(trim($h)), $headers);
        $index = [];
        foreach ($this->headerMap as $key => $aliases) {
            foreach ($norm as $i => $h) {
                foreach ($aliases as $alias) {
                    if ($h === $alias || str_contains($h, $alias)) {
                        $index[$key] = $i;
                        break 2;
                    }
                }
            }
        }
        return $index;
    }

    protected function resolveCategoryId(string $name, ?int $fallback): ?int
    {
        $name = trim($name);
        if ($name === '') {
            return $fallback;
        }
        $cat = Category::firstOrCreate(
            ['name' => $name],
            ['icon' => null, 'sort_order' => (Category::max('sort_order') ?? 0) + 1, 'is_active' => true]
        );
        return $cat->id;
    }

    protected function generateSku(string $name): string
    {
        return 'SHP-'.Str::upper(Str::substr(Str::slug($name), 0, 12)).'-'.Str::upper(Str::random(4));
    }

    /** Bersihkan angka harga/stok dari format "Rp1.500.000" / "1,500,000". */
    protected function toInt(string $v): int
    {
        $v = preg_replace('/[^\d]/', '', $v);
        return $v === '' ? 0 : (int) $v;
    }

    /** Konversi berat ke gram. Shopee biasanya pakai kg (desimal). */
    protected function toWeightGram(string $v): int
    {
        $v = trim($v);
        if ($v === '') {
            return 0;
        }
        // normalisasi koma desimal -> titik
        $num = (float) str_replace(',', '.', preg_replace('/[^\d.,]/', '', $v));
        // bila nilai kecil (<= 50) anggap kg, konversi ke gram; bila besar anggap sudah gram
        return $num > 0 && $num <= 50 ? (int) round($num * 1000) : (int) round($num);
    }

    /** Ambil URL gambar pertama bila berisi banyak (dipisah koma/spasi/newline). */
    protected function firstImage(string $v): string
    {
        $v = trim($v);
        if ($v === '') {
            return '';
        }
        $parts = preg_split('/[\s,]+/', $v);
        return trim($parts[0] ?? '');
    }
}

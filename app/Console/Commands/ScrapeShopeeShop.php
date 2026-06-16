<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Scraper produk Shopee per-toko → import ke tabel products NIVICO.
 *
 * Memakai endpoint internal Shopee (api/v4) yang sama dengan yang dipakai web Shopee.
 * Tidak butuh API key, tapi butuh header browser yang wajar + cookie agar tidak diblok.
 *
 * Contoh pakai:
 *   php artisan shopee:scrape nivico_electronicmartsby
 *   php artisan shopee:scrape nivico_electronicmartsby --limit=50 --dry-run
 *   php artisan shopee:scrape nivico_electronicmartsby --cookie="SPC_F=...; SPC_EC=..."
 *
 * Catatan:
 *   - Jalankan dari server (cPanel) Anda, BUKAN dari localhost yang tak punya IP Indonesia,
 *     karena Shopee membatasi akses berdasarkan region.
 *   - Jika 877 produk, biarkan default delay agar tidak kena rate-limit.
 */
class ScrapeShopeeShop extends Command
{
    protected $signature = 'shopee:scrape
        {username : Username toko Shopee, mis. nivico_electronicmartsby}
        {--limit=0 : Batas jumlah produk (0 = semua)}
        {--per-page=30 : Jumlah item per request (maks 60)}
        {--delay=1200 : Jeda antar request dalam milidetik}
        {--cookie= : Cookie header dari browser (opsional, naikkan success rate)}
        {--category= : Paksa semua produk masuk ke kategori ini (nama). Default: "Shopee Import"}
        {--with-detail : Ambil deskripsi & galeri lengkap per produk (lebih lambat)}
        {--dry-run : Hanya tampilkan, tidak menyimpan ke DB}';

    protected $description = 'Scrape semua produk dari satu toko Shopee dan import ke tabel products NIVICO';

    private string $base = 'https://shopee.co.id';

    public function handle(): int
    {
        $username = $this->argument('username');
        $limit    = (int) $this->option('limit');
        $perPage  = min(60, max(10, (int) $this->option('per-page')));
        $delayMs  = (int) $this->option('delay');
        $cookie   = (string) $this->option('cookie');
        $withDet  = (bool) $this->option('with-detail');
        $dryRun   = (bool) $this->option('dry-run');

        $this->info("🔍 Mengambil shopid untuk toko: {$username}");

        $shopId = $this->resolveShopId($username, $cookie);
        if (! $shopId) {
            $this->error('Gagal menemukan shopid. Cek username atau sediakan --cookie.');
            return self::FAILURE;
        }
        $this->info("✓ shopid = {$shopId}");

        // Kategori tujuan
        $catName = $this->option('category') ?: 'Shopee Import';
        $category = $dryRun
            ? new Category(['name' => $catName, 'slug' => Str::slug($catName)])
            : Category::firstOrCreate(
                ['slug' => Str::slug($catName)],
                ['name' => $catName, 'is_active' => true, 'sort_order' => 99]
            );

        $offset    = 0;
        $imported  = 0;
        $skipped   = 0;
        $totalSeen = 0;

        $this->newLine();
        $this->info('📦 Mulai scraping produk...');

        do {
            $items = $this->fetchPage($shopId, $offset, $perPage, $cookie);

            if ($items === null) {
                $this->warn("Request gagal di offset {$offset}. Berhenti.");
                break;
            }
            if (count($items) === 0) {
                break; // habis
            }

            foreach ($items as $row) {
                $basic = $row['item_basic'] ?? $row;
                if (empty($basic['itemid'])) {
                    continue;
                }
                $totalSeen++;

                // Ambil detail penuh bila diminta
                if ($withDet) {
                    $detail = $this->fetchDetail($shopId, $basic['itemid'], $cookie);
                    if ($detail) {
                        $basic = array_merge($basic, $detail);
                    }
                    usleep($delayMs * 1000);
                }

                $mapped = $this->mapItem($basic, $category->id ?? 0);

                if ($dryRun) {
                    $this->line(sprintf(
                        '  • [%s] %s — Rp%s (stok %d, %dx terjual)',
                        $mapped['sku'],
                        Str::limit($mapped['name'], 50),
                        number_format($mapped['price'], 0, ',', '.'),
                        $mapped['stock'],
                        $mapped['sold']
                    ));
                    $imported++;
                } else {
                    $saved = $this->upsertProduct($mapped, $basic);
                    $saved ? $imported++ : $skipped++;
                }

                if ($limit > 0 && $totalSeen >= $limit) {
                    break 2;
                }
            }

            $offset += $perPage;
            $this->line("   …{$totalSeen} produk diproses (imported {$imported}, skipped {$skipped})");
            usleep($delayMs * 1000);
        } while (true);

        $this->newLine();
        $this->info("✅ Selesai. Total dilihat: {$totalSeen} | Imported: {$imported} | Skipped: {$skipped}");
        if ($dryRun) {
            $this->comment('(dry-run: tidak ada yang disimpan ke database)');
        }

        return self::SUCCESS;
    }

    /**
     * Resolve username → shopid lewat endpoint get_shop_detail.
     */
    private function resolveShopId(string $username, string $cookie): ?int
    {
        $url = "{$this->base}/api/v4/shop/get_shop_detail?username={$username}";
        $res = $this->request($url, $cookie, $username);

        if (! $res) {
            return null;
        }
        return data_get($res, 'data.shopid');
    }

    /**
     * Ambil satu halaman daftar produk toko.
     * Endpoint: search_items (paginasi by_shop).
     */
    private function fetchPage(int $shopId, int $offset, int $limit, string $cookie): ?array
    {
        $query = http_build_query([
            'by'             => 'pop',
            'limit'          => $limit,
            'match_id'       => $shopId,
            'newest'         => $offset,
            'order'          => 'desc',
            'page_type'      => 'shop',
            'scenario'       => 'PAGE_OTHERS',
            'version'        => '2',
        ]);
        $url = "{$this->base}/api/v4/search/search_items?{$query}";

        $res = $this->request($url, $cookie, "shop/{$shopId}");
        if (! $res) {
            return null;
        }
        return data_get($res, 'items', []);
    }

    /**
     * Ambil detail satu produk (deskripsi, galeri).
     */
    private function fetchDetail(int $shopId, int $itemId, string $cookie): ?array
    {
        $url = "{$this->base}/api/v4/pdp/get_pc?item_id={$itemId}&shop_id={$shopId}";
        $res = $this->request($url, $cookie, "item/{$itemId}");

        $data = data_get($res, 'data.item') ?? data_get($res, 'item');
        if (! $data) {
            return null;
        }
        return [
            'description' => $data['description'] ?? null,
            'images'      => $data['images'] ?? [],
        ];
    }

    /**
     * HTTP request dengan header browser-like.
     */
    private function request(string $url, string $cookie, string $refPath): ?array
    {
        $headers = [
            'User-Agent'       => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 '
                . '(KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
            'Accept'           => 'application/json',
            'Referer'          => "{$this->base}/{$refPath}",
            'X-Requested-With' => 'XMLHttpRequest',
            'X-API-SOURCE'     => 'pc',
            'X-Shopee-Language' => 'id',
        ];
        if ($cookie !== '') {
            $headers['Cookie'] = $cookie;
        }

        try {
            $resp = Http::withHeaders($headers)->timeout(20)->retry(2, 1500)->get($url);
        } catch (\Throwable $e) {
            $this->warn("  ! HTTP error: {$e->getMessage()}");
            return null;
        }

        if ($resp->status() === 403 || $resp->status() === 429) {
            $this->warn("  ! Diblok (HTTP {$resp->status()}). Coba sediakan --cookie atau naikkan --delay.");
            return null;
        }
        if (! $resp->ok()) {
            return null;
        }

        return $resp->json();
    }

    /**
     * Map item Shopee → kolom products NIVICO.
     */
    private function mapItem(array $b, int $categoryId): array
    {
        // Shopee menyimpan harga dalam satuan ×100000
        $price    = isset($b['price']) ? intdiv((int) $b['price'], 100000) : 0;
        $oldPrice = isset($b['price_before_discount']) && $b['price_before_discount'] > 0
            ? intdiv((int) $b['price_before_discount'], 100000)
            : null;
        if ($oldPrice !== null && $oldPrice <= $price) {
            $oldPrice = null;
        }

        $itemId = (int) $b['itemid'];
        $rating = (float) data_get($b, 'item_rating.rating_star', 0);
        $ratingCount = (int) array_sum((array) data_get($b, 'item_rating.rating_count', []));

        return [
            'category_id'  => $categoryId,
            'name'         => trim($b['name'] ?? "Produk {$itemId}"),
            'sku'          => 'SHOPEE-' . $itemId,
            'price'        => max(0, $price),
            'old_price'    => $oldPrice,
            'description'  => $b['description'] ?? null,
            'image'        => $this->imageUrl($b['image'] ?? null),
            'stock'        => (int) ($b['stock'] ?? 0),
            'rating'       => round(min(5, $rating), 1),
            'rating_count' => $ratingCount,
            'sold'         => (int) ($b['historical_sold'] ?? $b['sold'] ?? 0),
            'is_active'    => true,
            'weight'       => 1000,
        ];
    }

    /**
     * Simpan/Update produk + gambar. updateOrCreate berdasarkan sku.
     */
    private function upsertProduct(array $data, array $raw): bool
    {
        try {
            DB::transaction(function () use ($data, $raw) {
                $product = Product::withTrashed()->updateOrCreate(
                    ['sku' => $data['sku']],
                    $data
                );
                if ($product->trashed()) {
                    $product->restore();
                }

                // Gambar galeri (jika ada dari --with-detail)
                $images = $raw['images'] ?? [];
                if (! empty($images)) {
                    $product->images()->delete();
                    foreach (array_values($images) as $i => $hash) {
                        ProductImage::create([
                            'product_id' => $product->id,
                            'path'       => $this->imageUrl($hash),
                            'sort_order' => $i,
                        ]);
                    }
                }
            });
            return true;
        } catch (\Throwable $e) {
            $this->warn("  ! Gagal simpan {$data['sku']}: {$e->getMessage()}");
            return false;
        }
    }

    private function imageUrl(?string $hash): ?string
    {
        if (! $hash) {
            return null;
        }
        if (Str::startsWith($hash, 'http')) {
            return $hash;
        }
        return "https://down-id.img.susercontent.com/file/{$hash}";
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'variants'])->latest();
        if ($request->filled('q')) {
            $query->where('name', 'like', '%'.$request->q.'%')
                ->orWhere('sku', 'like', '%'.$request->q.'%');
        }
        $products = $query->paginate(15)->withQueryString();

        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();

        return view('admin.products.form', ['product' => new Product, 'categories' => $categories]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['image'] = $this->normalizeImageUrl($request->input('image'));

        $product = DB::transaction(function () use ($request, $data) {
            $product = Product::create($data);
            $this->syncVariants($request, $product);
            $this->syncImages($request, $product);

            return $product;
        });

        return redirect()->route('admin.products.index')->with('toast', '✓ Produk ditambahkan');
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();
        $product->load(['variants', 'images']);

        return view('admin.products.form', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validateData($request, $product->id);
        $data['image'] = $this->normalizeImageUrl($request->input('image'));
        $previousPrimaryImage = $product->image;
        $storedImagesToDelete = [];

        DB::transaction(function () use ($request, $product, $data, &$storedImagesToDelete) {
            $product->update($data);
            $this->syncVariants($request, $product);
            $storedImagesToDelete = $this->syncImages($request, $product);
        });

        $product->refresh();
        if ($previousPrimaryImage && $previousPrimaryImage !== $product->image) {
            $storedImagesToDelete[] = $previousPrimaryImage;
        }
        collect($storedImagesToDelete)->filter()->unique()->each(
            fn ($image) => $this->deleteStoredImage($image)
        );

        return redirect()->route('admin.products.index')->with('toast', '✓ Produk diperbarui');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return back()->with('toast', 'Produk dihapus');
    }

    /**
     * Sinkronkan varian dari input form.
     * - Baris dengan id => update; tanpa id => buat baru.
     * - Varian lama yang tidak ada di input => soft delete.
     * - Bila has_variants nonaktif => hapus semua varian.
     */
    protected function syncVariants(Request $request, Product $product): void
    {
        $hasVariants = $request->boolean('has_variants');

        if (! $hasVariants) {
            $product->variants()->delete();
            if ($product->has_variants) {
                $product->update(['has_variants' => false]);
            }

            return;
        }

        $rows = collect($request->input('variants', []))
            ->filter(fn ($r) => isset($r['name']) && trim($r['name']) !== '')
            ->values();

        $keepIds = [];
        foreach ($rows as $i => $r) {
            $payload = [
                'name' => trim($r['name']),
                'sku' => ($r['sku'] ?? '') !== '' ? trim($r['sku']) : null,
                'price' => (int) ($r['price'] ?? 0),
                'old_price' => ($r['old_price'] ?? '') !== '' ? (int) $r['old_price'] : null,
                'stock' => (int) ($r['stock'] ?? 0),
                'weight' => (int) ($r['weight'] ?? $product->weight),
                'length' => ($r['length'] ?? '') !== '' ? (int) $r['length'] : null,
                'width' => ($r['width'] ?? '') !== '' ? (int) $r['width'] : null,
                'height' => ($r['height'] ?? '') !== '' ? (int) $r['height'] : null,
                'sort_order' => $i,
                'is_active' => isset($r['is_active']) ? (bool) $r['is_active'] : true,
            ];

            if (! empty($r['id'])) {
                $variant = $product->variants()->whereKey($r['id'])->first();
                if ($variant) {
                    $variant->update($payload);
                    $keepIds[] = $variant->id;

                    continue;
                }
            }
            $new = $product->variants()->create($payload);
            $keepIds[] = $new->id;
        }

        // Hapus varian yang tak lagi ada di form
        $product->variants()->whereNotIn('id', $keepIds ?: [0])->delete();

        // Pastikan flag & harga/stok produk konsisten
        $product->update([
            'has_variants' => true,
            'price' => (int) $product->variants()->where('is_active', true)->min('price') ?: $product->price,
            'stock' => 0, // stok produk bervarian dibaca dari varian
        ]);
    }

    protected function validateData(Request $request, ?int $id = null): array
    {
        $hasVariants = $request->boolean('has_variants');

        $rules = [
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:160',
            'sku' => 'required|string|max:60|unique:products,sku'.($id ? ",$id" : ''),
            'old_price' => 'nullable|integer|min:0',
            'weight' => 'required|integer|min:1|max:1000000',
            'length' => 'nullable|integer|min:1|max:1000|required_with:width,height',
            'width' => 'nullable|integer|min:1|max:1000|required_with:length,height',
            'height' => 'nullable|integer|min:1|max:1000|required_with:length,width',
            'badge' => 'nullable|in:NEW,HOT',
            'description' => 'required|string|min:20|max:5000',
            'rating' => 'nullable|numeric|min:0|max:5',
            'rating_count' => 'nullable|integer|min:0',
            'is_flash_sale' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'has_variants' => 'nullable|boolean',
            'image' => 'nullable|string|max:2048',
            'image_files' => 'nullable|array|max:10',
            'image_files.*' => 'image|mimes:jpg,jpeg,png,webp|max:5120',
            'remove_primary_image' => 'nullable|boolean',
            'remove_images' => 'nullable|array',
            'remove_images.*' => 'integer',
        ];

        // Harga & stok produk wajib hanya bila TANPA varian
        if ($hasVariants) {
            $rules['price'] = 'nullable|integer|min:0';
            $rules['stock'] = 'nullable|integer|min:0';
            $rules['variants'] = 'required|array|min:1';
            $rules['variants.*.name'] = 'required|string|max:120';
            $rules['variants.*.price'] = 'required|integer|min:0';
            $rules['variants.*.stock'] = 'required|integer|min:0';
            $rules['variants.*.sku'] = 'nullable|string|max:80';
            $rules['variants.*.old_price'] = 'nullable|integer|min:0';
            $rules['variants.*.weight'] = 'required|integer|min:1|max:1000000';
            $rules['variants.*.length'] = 'nullable|integer|min:1|max:1000|required_with:variants.*.width,variants.*.height';
            $rules['variants.*.width'] = 'nullable|integer|min:1|max:1000|required_with:variants.*.length,variants.*.height';
            $rules['variants.*.height'] = 'nullable|integer|min:1|max:1000|required_with:variants.*.length,variants.*.width';
        } else {
            $rules['price'] = 'required|integer|min:0';
            $rules['stock'] = 'required|integer|min:0';
        }

        $data = $request->validate($rules);

        // Sisakan hanya kolom milik tabel products (variants ditangani terpisah)
        unset(
            $data['variants'],
            $data['image_files'],
            $data['remove_primary_image'],
            $data['remove_images']
        );

        return $data + [
            'price' => $hasVariants ? ($request->integer('price') ?: 0) : $data['price'],
            'stock' => $hasVariants ? 0 : $data['stock'],
            'is_flash_sale' => $request->boolean('is_flash_sale'),
            'is_active' => $request->boolean('is_active', true),
            'has_variants' => $hasVariants,
        ];
    }

    /**
     * Sinkronkan foto utama dan galeri. Total foto produk dibatasi 10,
     * termasuk foto utama yang berasal dari URL maupun hasil upload.
     */
    protected function syncImages(Request $request, Product $product): array
    {
        $storedImagesToDelete = [];
        $removeIds = collect($request->input('remove_images', []))
            ->filter(fn ($id) => filter_var($id, FILTER_VALIDATE_INT) !== false)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $imagesToRemove = $product->images()->whereIn('id', $removeIds)->get();
        $remainingGalleryCount = $product->images()->whereNotIn('id', $removeIds)->count();
        $removePrimary = $request->boolean('remove_primary_image');
        $hasPrimary = ! $removePrimary && trim((string) $product->image) !== '';
        $uploads = array_values(array_filter($request->file('image_files', [])));
        $futureTotal = ($hasPrimary ? 1 : 0) + $remainingGalleryCount + count($uploads);

        if ($futureTotal > 10) {
            throw ValidationException::withMessages([
                'image_files' => 'Maksimal 10 gambar per produk. Hapus gambar lama sebelum menambah gambar baru.',
            ]);
        }

        foreach ($imagesToRemove as $image) {
            $storedImagesToDelete[] = $image->path;
            $image->delete();
        }

        if ($removePrimary && $product->image) {
            $storedImagesToDelete[] = $product->image;
            $product->update(['image' => null]);
        }

        // Bila foto utama dihapus, promosikan foto galeri pertama agar kartu produk
        // tetap mempunyai thumbnail tanpa menduplikasi gambar.
        if (! $product->image) {
            $firstGallery = $product->images()->orderBy('sort_order')->first();
            if ($firstGallery) {
                $product->update(['image' => $firstGallery->path]);
                $firstGallery->delete();
            }
        }

        $nextOrder = (int) $product->images()->max('sort_order') + 1;
        foreach ($uploads as $upload) {
            $url = Storage::url($upload->store('products', 'public'));

            if (! $product->image) {
                $product->update(['image' => $url]);

                continue;
            }

            $product->images()->create([
                'path' => $url,
                'sort_order' => $nextOrder++,
            ]);
        }

        // Rapikan urutan setelah ada penghapusan/promosi foto.
        $product->images()->orderBy('sort_order')->get()->each(function ($image, $index) {
            if ((int) $image->sort_order !== $index) {
                $image->update(['sort_order' => $index]);
            }
        });

        return $storedImagesToDelete;
    }

    protected function normalizeImageUrl(?string $image): ?string
    {
        $image = trim((string) $image);

        return $image !== '' ? $image : null;
    }

    /** Hanya hapus berkas yang memang berada pada disk public aplikasi. */
    protected function deleteStoredImage(?string $url): void
    {
        $path = parse_url((string) $url, PHP_URL_PATH) ?: '';

        if (str_starts_with($path, '/storage/')) {
            Storage::disk('public')->delete(substr($path, strlen('/storage/')));
        }
    }
}

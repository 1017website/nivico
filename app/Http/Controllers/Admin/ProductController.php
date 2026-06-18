<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
        $data['image'] = $this->handleImage($request);

        $product = DB::transaction(function () use ($request, $data) {
            $product = Product::create($data);
            $this->syncVariants($request, $product);
            return $product;
        });

        return redirect()->route('admin.products.index')->with('toast', '✓ Produk ditambahkan');
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();
        $product->load('variants');
        return view('admin.products.form', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validateData($request, $product->id);
        if ($img = $this->handleImage($request)) {
            $data['image'] = $img;
        }

        DB::transaction(function () use ($request, $product, $data) {
            $product->update($data);
            $this->syncVariants($request, $product);
        });

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
                'name'       => trim($r['name']),
                'sku'        => ($r['sku'] ?? '') !== '' ? trim($r['sku']) : null,
                'price'      => (int) ($r['price'] ?? 0),
                'old_price'  => ($r['old_price'] ?? '') !== '' ? (int) $r['old_price'] : null,
                'stock'      => (int) ($r['stock'] ?? 0),
                'sort_order' => $i,
                'is_active'  => isset($r['is_active']) ? (bool) $r['is_active'] : true,
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
            'price'        => (int) $product->variants()->where('is_active', true)->min('price') ?: $product->price,
            'stock'        => 0, // stok produk bervarian dibaca dari varian
        ]);
    }

    protected function validateData(Request $request, ?int $id = null): array
    {
        $hasVariants = $request->boolean('has_variants');

        $rules = [
            'category_id'   => 'required|exists:categories,id',
            'name'          => 'required|string|max:160',
            'sku'           => 'required|string|max:60|unique:products,sku'.($id ? ",$id" : ''),
            'old_price'     => 'nullable|integer|min:0',
            'badge'         => 'nullable|in:NEW,HOT',
            'description'   => 'nullable|string',
            'rating'        => 'nullable|numeric|min:0|max:5',
            'rating_count'  => 'nullable|integer|min:0',
            'is_flash_sale' => 'nullable|boolean',
            'is_active'     => 'nullable|boolean',
            'has_variants'  => 'nullable|boolean',
        ];

        // Harga & stok produk wajib hanya bila TANPA varian
        if ($hasVariants) {
            $rules['price'] = 'nullable|integer|min:0';
            $rules['stock'] = 'nullable|integer|min:0';
            $rules['variants']             = 'required|array|min:1';
            $rules['variants.*.name']      = 'required|string|max:120';
            $rules['variants.*.price']     = 'required|integer|min:0';
            $rules['variants.*.stock']     = 'required|integer|min:0';
            $rules['variants.*.sku']       = 'nullable|string|max:80';
            $rules['variants.*.old_price'] = 'nullable|integer|min:0';
        } else {
            $rules['price'] = 'required|integer|min:0';
            $rules['stock'] = 'required|integer|min:0';
        }

        $data = $request->validate($rules);

        // Sisakan hanya kolom milik tabel products (variants ditangani terpisah)
        unset($data['variants']);

        return $data + [
            'price'         => $hasVariants ? ($request->integer('price') ?: 0) : $data['price'],
            'stock'         => $hasVariants ? 0 : $data['stock'],
            'is_flash_sale' => $request->boolean('is_flash_sale'),
            'is_active'     => $request->boolean('is_active', true),
            'has_variants'  => $hasVariants,
        ];
    }

    protected function handleImage(Request $request): ?string
    {
        if ($request->hasFile('image_file')) {
            $path = $request->file('image_file')->store('products', 'public');
            return Storage::url($path);
        }
        return $request->input('image') ?: null;
    }
}

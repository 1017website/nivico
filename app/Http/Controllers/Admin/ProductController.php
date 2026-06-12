<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category')->latest();
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
        Product::create($data);
        return redirect()->route('admin.products.index')->with('toast', '✓ Produk ditambahkan');
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();
        return view('admin.products.form', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validateData($request, $product->id);
        if ($img = $this->handleImage($request)) {
            $data['image'] = $img;
        }
        $product->update($data);
        return redirect()->route('admin.products.index')->with('toast', '✓ Produk diperbarui');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return back()->with('toast', 'Produk dihapus');
    }

    protected function validateData(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'category_id'   => 'required|exists:categories,id',
            'name'          => 'required|string|max:160',
            'sku'           => 'required|string|max:60|unique:products,sku'.($id ? ",$id" : ''),
            'price'         => 'required|integer|min:0',
            'old_price'     => 'nullable|integer|min:0',
            'badge'         => 'nullable|in:NEW,HOT',
            'description'   => 'nullable|string',
            'stock'         => 'required|integer|min:0',
            'rating'        => 'nullable|numeric|min:0|max:5',
            'rating_count'  => 'nullable|integer|min:0',
            'is_flash_sale' => 'nullable|boolean',
            'is_active'     => 'nullable|boolean',
        ]) + [
            'is_flash_sale' => $request->boolean('is_flash_sale'),
            'is_active'     => $request->boolean('is_active', true),
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

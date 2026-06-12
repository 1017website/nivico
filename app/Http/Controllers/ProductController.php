<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // listing + filter + search
    public function index(Request $request)
    {
        $query = Product::active()->with('category');

        if ($request->filled('q')) {
            $query->where('name', 'like', '%'.$request->q.'%');
        }
        if ($request->filled('kategori')) {
            $query->whereHas('category', fn ($c) => $c->where('slug', $request->kategori));
        }

        $sort = $request->get('sort', 'baru');
        match ($sort) {
            'murah'   => $query->orderBy('price'),
            'mahal'   => $query->orderByDesc('price'),
            'terlaris'=> $query->orderByDesc('sold'),
            default   => $query->latest(),
        };

        $products   = $query->paginate(12)->withQueryString();
        $categories = Category::active()->orderBy('sort_order')->get();

        return view('pages.products', compact('products', 'categories', 'sort'));
    }

    public function show(Product $product)
    {
        $product->load('category', 'images');
        $related = Product::active()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->take(6)->get();

        if ($related->isEmpty()) {
            $related = Product::active()->where('id', '!=', $product->id)->take(6)->get();
        }

        return view('pages.detail', compact('product', 'related'));
    }
}

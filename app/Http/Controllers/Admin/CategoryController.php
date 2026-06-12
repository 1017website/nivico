<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')->orderBy('sort_order')->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100']);
        Category::create([
            'name'       => $request->name,
            'icon'       => $request->icon,
            'sort_order' => Category::max('sort_order') + 1,
            'is_active'  => true,
        ]);
        return back()->with('toast', '✓ Kategori ditambahkan');
    }

    public function update(Request $request, Category $category)
    {
        $request->validate(['name' => 'required|string|max:100']);
        $category->update([
            'name'      => $request->name,
            'icon'      => $request->icon,
            'is_active' => $request->boolean('is_active', true),
        ]);
        return back()->with('toast', '✓ Kategori diperbarui');
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return back()->with('toast', 'Kategori dihapus');
    }
}

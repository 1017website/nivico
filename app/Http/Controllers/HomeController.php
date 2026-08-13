<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Services\ProductDiscountService;

class HomeController extends Controller
{
    public function index(ProductDiscountService $discounts)
    {
        $newProducts = Product::active()->with('category', 'variants')->availableFirst()->latest()->take(6)->get();
        $flashQuery = Product::active()->with('category', 'variants')->availableFirst();
        if (! ($discounts->enabled() && $discounts->scope() === 'all')) {
            $flashQuery->where('is_flash_sale', true);
        }
        $flashProducts = $flashQuery->take(6)->get();
        $bestProducts = Product::active()->with('category', 'variants')->availableFirst()->orderByDesc('sold')->take(6)->get();
        $categories = Category::active()->orderBy('sort_order')->get();

        return view('pages.home', compact('newProducts', 'flashProducts', 'bestProducts', 'categories'))->with('seoKey', 'home');
    }
}

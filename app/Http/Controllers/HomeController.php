<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;

class HomeController extends Controller
{
    public function index()
    {
        $newProducts   = Product::active()->with('category')->latest()->take(6)->get();
        $flashProducts = Product::active()->with('category')->where('is_flash_sale', true)->take(6)->get();
        $bestProducts  = Product::active()->with('category')->orderByDesc('sold')->take(6)->get();
        $categories    = Category::active()->orderBy('sort_order')->get();

        return view('pages.home', compact('newProducts', 'flashProducts', 'bestProducts', 'categories'))->with('seoKey', 'home');
    }
}

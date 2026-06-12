<?php

namespace App\Providers;

use App\Models\Category;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // share kategori aktif ke layout (nav & footer)
        View::composer(['layouts.app', 'partials.*'], function ($view) {
            $view->with('navCategories', Category::active()->orderBy('sort_order')->get());
        });
    }
}

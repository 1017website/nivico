<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\SeoSetting;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Pagination memakai tema admin
        \Illuminate\Pagination\Paginator::defaultView('vendor.pagination.admin');

        // Share kategori aktif ke layout toko (nav & footer)
        View::composer(['layouts.app', 'partials.*'], function ($view) {
            if (Schema::hasTable('categories')) {
                $view->with('navCategories', Category::active()->orderBy('sort_order')->get());
            } else {
                $view->with('navCategories', collect());
            }
        });

        // Share konten dinamis (site_settings) ke seluruh halaman toko
        View::composer(['layouts.app', 'partials.*', 'pages.*'], function ($view) {
            $settings = [];
            if (Schema::hasTable('site_settings')) {
                $settings = \App\Models\SiteSetting::allMap();
            }
            $view->with('site', $settings);
        });

        // Share menu admin (terfilter permission) ke layout admin
        View::composer('layouts.admin', function ($view) {
            $user = auth()->user();
            $menus = collect(config('adminmenu'))->filter(function ($m) use ($user) {
                if (! $user) {
                    return false;
                }
                // menu super_only hanya untuk Super Admin
                if (($m['super_only'] ?? false) && ! $user->isSuperAdmin()) {
                    return false;
                }
                return $user->hasPermission($m['permission']);
            });
            $view->with('adminMenus', $menus);
        });

        // Share SEO global ke layout toko
        View::composer('layouts.app', function ($view) {
            $seo = null;
            if (Schema::hasTable('seo_settings')) {
                $key = $view->getData()['seoKey'] ?? 'global';
                $seo = SeoSetting::for($key) ?? SeoSetting::for('global');
            }
            $view->with('seo', $seo);
        });
    }
}

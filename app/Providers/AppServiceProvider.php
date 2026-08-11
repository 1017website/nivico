<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Order;
use App\Models\SeoSetting;
use App\Models\SiteSetting;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
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
        // Pagination memakai tema admin
        Paginator::defaultView('vendor.pagination.admin');

        // Share kategori aktif ke layout toko (nav & footer)
        View::composer(['layouts.app', 'partials.*'], function ($view) {
            if (Schema::hasTable('categories')) {
                $view->with('navCategories', Category::active()->orderBy('sort_order')->get());
            } else {
                $view->with('navCategories', collect());
            }
        });

        // Share konten dinamis (site_settings) ke seluruh halaman toko + admin
        View::composer(['layouts.app', 'layouts.admin', 'partials.*', 'pages.*'], function ($view) {
            $settings = [];
            if (Schema::hasTable('site_settings')) {
                $settings = SiteSetting::allMap();
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
                // Menu developer_only tidak pernah ditampilkan ke Super Admin biasa.
                if (($m['developer_only'] ?? false) && ! $user->isDeveloper()) {
                    return false;
                }

                return $user->hasPermission($m['permission']);
            });
            $orderNoticeCount = 0;
            if (
                $user
                && $user->hasPermission('orders.manage')
                && Schema::hasTable('orders')
                && Schema::hasColumn('orders', 'admin_seen_at')
            ) {
                $orderNoticeCount = Order::whereNull('admin_seen_at')->count();
            }

            $view->with([
                'adminMenus' => $menus,
                'adminOrderNoticeCount' => $orderNoticeCount,
            ]);
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

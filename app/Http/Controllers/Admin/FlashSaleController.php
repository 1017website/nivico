<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

class FlashSaleController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $products = Product::query()
            ->when($q !== '', fn ($query) => $query->where('name', 'like', "%{$q}%"))
            ->orderByDesc('is_flash_sale')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $flashCount = Product::where('is_flash_sale', true)->count();

        $settings = [
            'enabled' => SiteSetting::get('flashsale.enabled', true),
            'ends_at' => SiteSetting::get('flashsale.ends_at', now()->addDay()->format('Y-m-d H:i')),
            'label'   => SiteSetting::get('flashsale.label', 'Berakhir dalam:'),
            'title'   => SiteSetting::get('section.flash_title', '⚡ Flash Sale'),
        ];

        return view('admin.flashsale.index', compact('products', 'flashCount', 'settings', 'q'))
            ->with('seoKey', null);
    }

    /** Simpan pengaturan countdown & judul. */
    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'enabled' => 'nullable|boolean',
            'ends_at' => 'nullable|string|max:20',
            'label'   => 'nullable|string|max:60',
            'title'   => 'nullable|string|max:60',
        ]);

        SiteSetting::put('flashsale.enabled', $request->boolean('enabled') ? '1' : '0', 'boolean', 'flashsale', 'Aktifkan Countdown');
        SiteSetting::put('flashsale.ends_at', $data['ends_at'] ?? '', 'text', 'flashsale', 'Waktu Berakhir (YYYY-MM-DD HH:MM)');
        SiteSetting::put('flashsale.label', $data['label'] ?? 'Berakhir dalam:', 'text', 'flashsale', 'Label Countdown');
        SiteSetting::put('section.flash_title', $data['title'] ?? '⚡ Flash Sale', 'text', 'label', 'Judul: Flash Sale');

        return back()->with('toast', '✓ Pengaturan Flash Sale disimpan.');
    }

    /** Toggle satu produk masuk/keluar flash sale (AJAX atau biasa). */
    public function toggle(Request $request, Product $product)
    {
        $product->update(['is_flash_sale' => ! $product->is_flash_sale]);

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'is_flash_sale' => $product->is_flash_sale]);
        }

        return back()->with('toast', $product->is_flash_sale
            ? "✓ {$product->name} ditambahkan ke Flash Sale."
            : "✓ {$product->name} dikeluarkan dari Flash Sale.");
    }

    /** Keluarkan semua produk dari flash sale. */
    public function clearAll()
    {
        Product::where('is_flash_sale', true)->update(['is_flash_sale' => false]);
        return back()->with('toast', '✓ Semua produk dikeluarkan dari Flash Sale.');
    }
}

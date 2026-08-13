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
            'label' => SiteSetting::get('flashsale.label', 'Berakhir dalam:'),
            'title' => SiteSetting::get('section.flash_title', '⚡ Flash Sale'),
            'discount_enabled' => SiteSetting::get('flashsale.discount_enabled', false),
            'discount_scope' => SiteSetting::get('flashsale.discount_scope', 'selected'),
            'discount_percent' => SiteSetting::get('flashsale.discount_percent', 10),
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
            'label' => 'nullable|string|max:60',
            'title' => 'nullable|string|max:60',
            'discount_enabled' => 'nullable|boolean',
            'discount_scope' => 'required|in:all,selected',
            'discount_percent' => 'required|integer|min:1|max:99',
        ]);

        SiteSetting::put('flashsale.enabled', $request->boolean('enabled') ? '1' : '0', 'boolean', 'flashsale', 'Aktifkan Countdown');
        SiteSetting::put('flashsale.ends_at', $data['ends_at'] ?? '', 'text', 'flashsale', 'Waktu Berakhir (YYYY-MM-DD HH:MM)');
        SiteSetting::put('flashsale.label', $data['label'] ?? 'Berakhir dalam:', 'text', 'flashsale', 'Label Countdown');
        SiteSetting::put('section.flash_title', $data['title'] ?? '⚡ Flash Sale', 'text', 'label', 'Judul: Flash Sale');
        SiteSetting::put('flashsale.discount_enabled', $request->boolean('discount_enabled') ? '1' : '0', 'boolean', 'flashsale', 'Aktifkan Diskon Produk');
        SiteSetting::put('flashsale.discount_scope', $data['discount_scope'], 'text', 'flashsale', 'Cakupan Diskon');
        SiteSetting::put('flashsale.discount_percent', (string) $data['discount_percent'], 'number', 'flashsale', 'Persentase Diskon');

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

    /** Tambahkan atau keluarkan beberapa produk sekaligus. */
    public function bulkUpdate(Request $request)
    {
        $data = $request->validate([
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'integer|exists:products,id',
            'action' => 'required|in:add,remove',
        ]);

        $enabled = $data['action'] === 'add';
        $count = Product::whereIn('id', $data['product_ids'])->update(['is_flash_sale' => $enabled]);

        return back()->with('toast', $enabled
            ? "✓ {$count} produk ditambahkan ke Flash Sale."
            : "✓ {$count} produk dikeluarkan dari Flash Sale.");
    }

    /** Keluarkan semua produk dari flash sale. */
    public function clearAll()
    {
        Product::where('is_flash_sale', true)->update(['is_flash_sale' => false]);

        return back()->with('toast', '✓ Semua produk dikeluarkan dari Flash Sale.');
    }
}

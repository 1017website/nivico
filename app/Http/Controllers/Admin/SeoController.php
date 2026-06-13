<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeoSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SeoController extends Controller
{
    protected array $pages = [
        'global'   => 'Global (Default Seluruh Situs)',
        'home'     => 'Beranda',
        'products' => 'Halaman Produk',
        'promo'    => 'Halaman Promo',
        'about'    => 'Tentang Kami',
        'contact'  => 'Kontak',
    ];

    public function index()
    {
        $settings = SeoSetting::all()->keyBy('page_key');
        $pages = $this->pages;
        return view('admin.seo.index', compact('settings', 'pages'));
    }

    public function edit(string $pageKey)
    {
        abort_unless(isset($this->pages[$pageKey]), 404);
        $setting = SeoSetting::firstOrNew(['page_key' => $pageKey]);
        $label = $this->pages[$pageKey];
        return view('admin.seo.form', compact('setting', 'pageKey', 'label'));
    }

    public function update(Request $request, string $pageKey)
    {
        abort_unless(isset($this->pages[$pageKey]), 404);
        $data = $request->validate([
            'title'            => 'nullable|string|max:160',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords'    => 'nullable|string|max:255',
            'og_title'         => 'nullable|string|max:160',
            'og_description'   => 'nullable|string|max:500',
            'canonical_url'    => 'nullable|string|max:255',
            'noindex'          => 'nullable|boolean',
            'og_image'         => 'nullable|string|max:255',
        ]);
        $data['noindex'] = $request->boolean('noindex');

        if ($request->hasFile('og_image_file')) {
            $path = $request->file('og_image_file')->store('seo', 'public');
            $data['og_image'] = Storage::url($path);
        }

        SeoSetting::updateOrCreate(['page_key' => $pageKey], $data);
        return redirect()->route('admin.seo.index')->with('toast', '✓ SEO '.$this->pages[$pageKey].' disimpan');
    }
}

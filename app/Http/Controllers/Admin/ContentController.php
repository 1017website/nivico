<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ContentController extends Controller
{
    /** Definisi tab + key yang dikelola tiap tab. */
    protected array $tabs = [
        'hero'      => 'Hero Slider',
        'banner'    => 'Banner Promo',
        'label'     => 'Judul Section',
        'flashsale' => 'Flash Sale',
        'popup'     => 'Popup',
        'brand'     => 'Brand & Logo',
        'kontak'    => 'Kontak',
        'sosmed'    => 'Sosial Media',
        'footer'    => 'Footer',
    ];

    public function index(Request $request)
    {
        $tab = $request->get('tab', 'hero');
        if (! isset($this->tabs[$tab])) {
            $tab = 'hero';
        }

        $settings = SiteSetting::where('group', $tab)->orderBy('id')->get();

        return view('admin.content.index', [
            'tabs'     => $this->tabs,
            'tab'      => $tab,
            'settings' => $settings,
        ])->with('seoKey', null);
    }

    public function update(Request $request, string $tab)
    {
        abort_unless(isset($this->tabs[$tab]), 404);

        $settings = SiteSetting::where('group', $tab)->get();

        foreach ($settings as $s) {
            // ── JSON (array repeater: hero.slides, hero.perks, banner.promos) ──
            if ($s->type === 'json') {
                $rows = $request->input('json.'.$s->key, []);
                // bersihkan baris kosong total
                $rows = collect($rows)->filter(fn ($r) => collect($r)->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty())
                    ->values()->all();
                $s->update(['value' => json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
                continue;
            }

            // ── IMAGE (upload file ATAU isi URL) ──
            if ($s->type === 'image') {
                $field = 'file.'.str_replace('.', '__', $s->key);
                if ($request->hasFile($field)) {
                    if ($s->value && str_starts_with($s->value, '/storage/')) {
                        Storage::disk('public')->delete(str_replace('/storage/', '', $s->value));
                    }
                    $path = $request->file($field)->store('content', 'public');
                    $s->update(['value' => Storage::url($path)]);
                } elseif ($request->filled('val.'.$s->key)) {
                    $s->update(['value' => $request->input('val.'.$s->key)]);
                }
                continue;
            }

            // ── BOOLEAN ──
            if ($s->type === 'boolean') {
                $s->update(['value' => $request->boolean('val.'.$s->key) ? '1' : '0']);
                continue;
            }

            // ── text / textarea / number ──
            $s->update(['value' => $request->input('val.'.$s->key)]);
        }

        return back()->with('toast', '✓ Konten "'.$this->tabs[$tab].'" disimpan.');
    }
}

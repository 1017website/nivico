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
        'hero' => 'Hero Slider',
        'banner' => 'Banner Promo',
        'label' => 'Judul Section',
        'flashsale' => 'Flash Sale',
        'popup' => 'Popup',
        'brand' => 'Brand & Logo',
        'tentang' => 'Tentang Kami',
        'kontak' => 'Kontak',
        'sosmed' => 'Sosial Media',
        'footer' => 'Footer',
        'transaksi' => 'Biaya Layanan',
    ];

    public function index(Request $request)
    {
        $tab = $request->get('tab', 'hero');
        if (! isset($this->tabs[$tab])) {
            $tab = 'hero';
        }

        $settings = SiteSetting::where('group', $tab)->orderBy('id')->get();

        return view('admin.content.index', [
            'tabs' => $this->tabs,
            'tab' => $tab,
            'settings' => $settings,
        ])->with('seoKey', null);
    }

    public function update(Request $request, string $tab)
    {
        abort_unless(isset($this->tabs[$tab]), 404);

        $settings = SiteSetting::where('group', $tab)->get();

        // Ambil array mentah sekali. PENTING: key setting mengandung titik
        // (mis. "hero.slides", "brand.name") sehingga TIDAK boleh diakses via
        // dot-notation $request->input('val.hero.slides') — Laravel akan
        // menafsirkannya sebagai nested ['val']['hero']['slides'] dan menghasilkan
        // null (inilah penyebab konten "tidak tersimpan"). Akses langsung dengan
        // key literal dari array.
        $valInput = (array) $request->input('val', []);
        $jsonInput = (array) $request->input('json', []);
        $files = (array) $request->file('file', []);
        $jsonFiles = (array) $request->file('json_file', []);

        // Validasi semua gambar sebelum ada file yang disimpan agar kegagalan pada
        // salah satu upload tidak meninggalkan file yatim di storage.
        $imageUploads = collect($files)
            ->merge(
                collect($jsonFiles)->flatMap(function ($rows) {
                    return collect((array) $rows)->map(fn ($row) => is_array($row) ? ($row['image'] ?? null) : null);
                })
            )
            ->filter()
            ->values()
            ->all();

        validator(
            ['images' => $imageUploads],
            ['images.*' => ['image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120']],
            [
                'images.*.image' => 'File yang diunggah harus berupa gambar.',
                'images.*.mimes' => 'Format gambar harus JPG, PNG, WebP, atau GIF.',
                'images.*.max' => 'Ukuran gambar maksimal 5 MB.',
            ]
        )->validate();

        if ($tab === 'transaksi') {
            validator([
                'type' => $valInput['checkout.service_fee_type'] ?? null,
                'value' => $valInput['checkout.service_fee_value'] ?? null,
            ], [
                'type' => 'required|in:fixed,percent',
                'value' => 'required|numeric|min:0|max:1000000000',
            ])->validate();

            if (($valInput['checkout.service_fee_type'] ?? null) === 'percent'
                && (float) ($valInput['checkout.service_fee_value'] ?? 0) > 100) {
                return back()->withErrors(['value' => 'Persentase biaya layanan maksimal 100%.'])->withInput();
            }
        }

        foreach ($settings as $s) {
            // ── JSON (array repeater: hero.slides, hero.perks, banner.promos) ──
            if ($s->type === 'json') {
                $rows = $jsonInput[$s->key] ?? [];
                $oldRows = is_array($s->castValue()) ? $s->castValue() : [];
                $fileKey = str_replace('.', '__', $s->key);
                $rowFiles = (array) ($jsonFiles[$fileKey] ?? []);

                // Field gambar di repeater boleh diisi dengan URL seperti semula,
                // atau ditimpa dengan file yang baru diunggah.
                foreach ($rows as $index => &$row) {
                    if (! is_array($row)) {
                        continue;
                    }

                    $upload = $rowFiles[$index]['image'] ?? null;
                    if ($upload) {
                        $path = $upload->store('content', 'public');
                        $row['image'] = Storage::url($path);
                    }
                }
                unset($row);

                // bersihkan baris kosong total
                $rows = collect($rows)->filter(fn ($r) => collect($r)->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty())
                    ->values()->all();
                $s->update(['value' => json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);

                // Hapus upload lama hanya jika sudah tidak dipakai oleh baris mana
                // pun pada setting ini (misalnya gambar diganti atau slide dihapus).
                $oldImages = collect($oldRows)->pluck('image')->filter()->all();
                $newImages = collect($rows)->pluck('image')->filter()->all();
                foreach (array_diff($oldImages, $newImages) as $oldImage) {
                    $this->deleteContentUpload($oldImage);
                }

                continue;
            }

            // ── IMAGE (upload file ATAU isi URL) ──
            if ($s->type === 'image') {
                // name file di form: file[hero__bg] (titik diganti __ agar valid)
                $fileKey = str_replace('.', '__', $s->key);
                $upload = $files[$fileKey] ?? null;
                if ($upload) {
                    $oldImage = $s->value;
                    $path = $upload->store('content', 'public');
                    $s->update(['value' => Storage::url($path)]);
                    $this->deleteContentUpload($oldImage);
                } elseif (($valInput[$s->key] ?? '') !== '') {
                    $s->update(['value' => $valInput[$s->key]]);
                }

                continue;
            }

            // ── BOOLEAN ──
            if ($s->type === 'boolean') {
                $checked = isset($valInput[$s->key]) && in_array($valInput[$s->key], ['1', 1, true, 'true', 'on'], true);
                $s->update(['value' => $checked ? '1' : '0']);

                continue;
            }

            // ── text / textarea / number ──
            $s->update(['value' => $valInput[$s->key] ?? null]);
        }

        return back()->with('toast', '✓ Konten "'.$this->tabs[$tab].'" disimpan.');
    }

    /** Hapus hanya file upload lokal; URL eksternal tidak pernah disentuh. */
    private function deleteContentUpload(?string $url): void
    {
        if ($url && str_starts_with($url, '/storage/')) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $url));
        }
    }
}

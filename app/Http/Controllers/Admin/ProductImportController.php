<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\ShopeeImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Import produk dari file export Shopee.
 *
 * Alur 2 langkah:
 *  1. form()    -> tampilkan halaman upload
 *  2. preview() -> parse file, tampilkan tabel pratinjau (file disimpan sementara)
 *  3. execute() -> jalankan import dari file sementara, hapus file
 *
 * Catatan hosting: tidak butuh SSH/Composer. Parser CSV native.
 */
class ProductImportController extends Controller
{
    public function __construct(protected ShopeeImportService $importer) {}

    public function form()
    {
        $categories = Category::orderBy('name')->get();
        return view('admin.products.import', [
            'categories' => $categories,
            'preview'    => null,
            'token'      => null,
        ]);
    }

    public function preview(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240', // maks 10MB
        ], [], ['file' => 'File CSV']);

        // simpan sementara agar bisa dipakai ulang saat konfirmasi import
        $token = 'shopee_'.now()->format('YmdHis').'_'.uniqid();
        $stored = $request->file('file')->storeAs('imports', $token.'.csv', 'local');
        $absPath = Storage::disk('local')->path($stored);

        $parsed = $this->importer->parse($absPath);

        $categories = Category::orderBy('name')->get();

        return view('admin.products.import', [
            'categories' => $categories,
            'preview'    => $parsed,
            'token'      => $token,
        ]);
    }

    public function execute(Request $request)
    {
        $data = $request->validate([
            'token'           => 'required|string',
            'category_id'     => 'nullable|exists:categories,id',
            'default_active'  => 'nullable|boolean',
            'update_existing' => 'nullable|boolean',
        ]);

        $relative = 'imports/'.basename($data['token']).'.csv';
        if (! Storage::disk('local')->exists($relative)) {
            return redirect()->route('admin.products.import')
                ->with('error', 'Sesi import kedaluwarsa. Silakan unggah ulang file.');
        }

        $absPath = Storage::disk('local')->path($relative);
        $parsed = $this->importer->parse($absPath);

        if (! empty($parsed['errors']) && empty($parsed['rows'])) {
            return back()->with('error', implode(' ', $parsed['errors']));
        }

        $summary = $this->importer->import($parsed['rows'], [
            'category_id'     => $data['category_id'] ?? null,
            'default_active'  => $request->boolean('default_active', true),
            'update_existing' => $request->boolean('update_existing', true),
        ]);

        // bersihkan file sementara
        Storage::disk('local')->delete($relative);

        $msg = "✓ Import selesai: {$summary['created']} produk baru, {$summary['updated']} diperbarui, {$summary['variants']} varian, {$summary['skipped']} dilewati.";
        if (! empty($summary['errors'])) {
            $msg .= ' ('.count($summary['errors']).' gagal)';
            session()->flash('import_errors', $summary['errors']);
        }

        return redirect()->route('admin.products.index')->with('toast', $msg);
    }
}

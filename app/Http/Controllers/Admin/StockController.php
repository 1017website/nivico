<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Services\StockService;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function __construct(protected StockService $stock) {}

    /** Daftar stok produk + ringkasan. */
    public function index(Request $request)
    {
        $query = Product::with('category')->orderBy('name');

        if ($request->filled('q')) {
            $query->where('name', 'like', '%'.$request->q.'%')
                  ->orWhere('sku', 'like', '%'.$request->q.'%');
        }
        if ($request->filled('kategori')) {
            $query->where('category_id', $request->kategori);
        }
        if ($request->get('filter') === 'low') {
            $query->where('stock', '<=', 5);
        }
        if ($request->get('filter') === 'out') {
            $query->where('stock', '<=', 0);
        }

        $products   = $query->paginate(15)->withQueryString();
        $categories = Category::orderBy('name')->get();

        $summary = [
            'total_sku'  => Product::count(),
            'total_unit' => (int) Product::sum('stock'),
            'low'        => Product::where('stock', '>', 0)->where('stock', '<=', 5)->count(),
            'out'        => Product::where('stock', '<=', 0)->count(),
        ];

        return view('admin.stock.index', compact('products', 'categories', 'summary'));
    }

    /** Proses penyesuaian stok (tambah/kurang). */
    public function adjust(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'mode'       => 'required|in:in,out,set',
            'qty'        => 'required|integer|min:0',
            'reason'     => 'nullable|string|max:200',
        ]);

        if ($data['mode'] === 'set') {
            $this->stock->setStock($data['product_id'], (int) $data['qty'], 'ADJ-'.now()->format('YmdHis'), $data['reason'] ?? 'Set manual');
        } else {
            $delta = $data['mode'] === 'in' ? (int) $data['qty'] : -1 * (int) $data['qty'];
            $this->stock->adjust($data['product_id'], $delta, 'adjustment', $data['reason'] ?? null, 'ADJ-'.now()->format('YmdHis'));
        }

        return back()->with('toast', '✓ Stok berhasil disesuaikan');
    }

    /** Halaman stock opname (hitung fisik vs sistem). */
    public function opname(Request $request)
    {
        $query = Product::with('category')->orderBy('name');
        if ($request->filled('q')) {
            $query->where('name', 'like', '%'.$request->q.'%')
                  ->orWhere('sku', 'like', '%'.$request->q.'%');
        }
        if ($request->filled('kategori')) {
            $query->where('category_id', $request->kategori);
        }
        $products   = $query->paginate(20)->withQueryString();
        $categories = Category::orderBy('name')->get();
        return view('admin.stock.opname', compact('products', 'categories'));
    }

    /** Simpan hasil opname (banyak baris sekaligus). */
    public function opnameStore(Request $request)
    {
        $data = $request->validate([
            'counts'    => 'required|array',
            'counts.*'  => 'nullable|integer|min:0',
            'reference' => 'nullable|string|max:60',
        ]);

        $ref = $data['reference'] ?: 'OPN-'.now()->format('YmdHis');
        $changed = 0;

        foreach ($data['counts'] as $productId => $physical) {
            if ($physical === null || $physical === '') {
                continue;
            }
            $product = Product::find($productId);
            if (! $product) {
                continue;
            }
            // hanya catat bila berbeda dari stok sistem
            if ((int) $physical !== (int) $product->stock) {
                $this->stock->setStock((int) $productId, (int) $physical, $ref);
                $changed++;
            }
        }

        return redirect()->route('admin.stock.movements')
            ->with('toast', $changed > 0 ? "✓ Opname tersimpan ({$changed} produk disesuaikan, ref {$ref})" : 'Tidak ada perubahan stok.');
    }

    /** Riwayat pergerakan stok. */
    public function movements(Request $request)
    {
        $query = StockMovement::with('product')->latest();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('q')) {
            $query->whereHas('product', fn ($p) => $p->where('name', 'like', '%'.$request->q.'%')->orWhere('sku', 'like', '%'.$request->q.'%'));
        }

        $movements = $query->paginate(20)->withQueryString();
        return view('admin.stock.movements', compact('movements'));
    }
}

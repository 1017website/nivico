<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promo;
use Illuminate\Http\Request;

class PromoController extends Controller
{
    public function index()
    {
        $promos = Promo::latest()->paginate(15);
        return view('admin.promos.index', compact('promos'));
    }

    public function create()
    {
        return view('admin.promos.form', ['promo' => new Promo]);
    }

    public function store(Request $request)
    {
        Promo::create($this->validateData($request));
        return redirect()->route('admin.promos.index')->with('toast', '✓ Promo ditambahkan');
    }

    public function edit(Promo $promo)
    {
        return view('admin.promos.form', compact('promo'));
    }

    public function update(Request $request, Promo $promo)
    {
        $promo->update($this->validateData($request, $promo->id));
        return redirect()->route('admin.promos.index')->with('toast', '✓ Promo diperbarui');
    }

    public function destroy(Promo $promo)
    {
        $promo->delete();
        return back()->with('toast', 'Promo dihapus');
    }

    protected function validateData(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'code'         => 'required|string|max:40|unique:promos,code'.($id ? ",$id" : ''),
            'title'        => 'required|string|max:160',
            'description'  => 'nullable|string',
            'type'         => 'required|in:fixed,percent,free_shipping',
            'value'        => 'required|integer|min:0',
            'max_discount' => 'nullable|integer|min:0',
            'min_purchase' => 'nullable|integer|min:0',
            'badge'        => 'nullable|in:Flash Sale,Voucher,Gratis Ongkir,Cashback,Member',
            'image'        => 'nullable|string',
            'expires_at'   => 'nullable|date',
        ]) + ['is_active' => $request->boolean('is_active', true)];
    }
}

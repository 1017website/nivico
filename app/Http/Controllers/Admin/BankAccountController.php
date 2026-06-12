<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use Illuminate\Http\Request;

class BankAccountController extends Controller
{
    public function index()
    {
        $banks = BankAccount::orderBy('sort_order')->get();
        return view('admin.banks.index', compact('banks'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['sort_order'] = BankAccount::max('sort_order') + 1;
        BankAccount::create($data);
        return back()->with('toast', '✓ Rekening bank ditambahkan');
    }

    public function update(Request $request, BankAccount $bank)
    {
        $bank->update($this->validateData($request));
        return back()->with('toast', '✓ Rekening diperbarui');
    }

    public function destroy(BankAccount $bank)
    {
        $bank->delete();
        return back()->with('toast', 'Rekening dihapus');
    }

    protected function validateData(Request $request): array
    {
        return $request->validate([
            'bank_name'      => 'required|string|max:60',
            'account_number' => 'required|string|max:40',
            'account_holder' => 'required|string|max:120',
            'logo'           => 'nullable|string|max:255',
        ]) + ['is_active' => $request->boolean('is_active', true)];
    }
}

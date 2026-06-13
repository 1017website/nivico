<?php

namespace App\Http\Controllers;

use App\Models\Promo;
use Illuminate\Http\Request;

class PromoController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab');
        $query = Promo::active()->latest();

        if ($tab && $tab !== 'semua') {
            $map = [
                'flash'   => 'Flash Sale',
                'voucher' => 'Voucher',
                'ongkir'  => 'Gratis Ongkir',
                'cashback'=> 'Cashback',
            ];
            if (isset($map[$tab])) {
                $query->where('badge', $map[$tab]);
            }
        }

        $promos = $query->get();
        return view('pages.promo', compact('promos', 'tab'))->with('seoKey', 'promo');
    }
}

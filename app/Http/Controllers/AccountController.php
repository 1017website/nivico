<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $orders = $request->user()
            ->orders()
            ->withCount('items')
            ->latest()
            ->paginate(10);

        $summary = [
            'total' => $request->user()->orders()->count(),
            'awaiting_payment' => $request->user()->orders()
                ->whereIn('payment_status', ['unpaid', 'pending'])
                ->count(),
            'in_progress' => $request->user()->orders()
                ->whereIn('status', ['paid', 'processing', 'shipped'])
                ->count(),
            'completed' => $request->user()->orders()
                ->where('status', 'completed')
                ->count(),
        ];

        return view('account.dashboard', compact('orders', 'summary'));
    }
}

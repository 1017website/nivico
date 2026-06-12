<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'orders'        => Order::count(),
            'pending'       => Order::where('status', 'pending')->count(),
            'revenue'       => Order::whereIn('status', ['paid', 'processing', 'shipped', 'completed'])->sum('total'),
            'products'      => Product::count(),
            'low_stock'     => Product::where('stock', '<', 10)->count(),
            'customers'     => User::where('role', 'customer')->count(),
            'unread_msg'    => ContactMessage::where('is_read', false)->count(),
        ];

        $recentOrders = Order::with('items')->latest()->take(8)->get();
        $lowStock     = Product::where('stock', '<', 10)->orderBy('stock')->take(8)->get();

        return view('admin.dashboard', compact('stats', 'recentOrders', 'lowStock'));
    }
}

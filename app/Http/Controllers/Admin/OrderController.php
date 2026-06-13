<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('items')->latest();
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        if ($request->filled('q')) {
            $query->where('order_number', 'like', '%'.$request->q.'%')
                  ->orWhere('recipient_name', 'like', '%'.$request->q.'%');
        }
        $orders = $query->paginate(15)->withQueryString();
        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load('items', 'user', 'promo', 'bankAccount');
        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,paid,processing,shipped,completed,cancelled',
            'tracking_number' => 'nullable|string|max:60',
        ]);
        $order->update([
            'status' => $request->status,
            'tracking_number' => $request->tracking_number ?: $order->tracking_number,
        ]);
        return back()->with('toast', '✓ Status pesanan diperbarui');
    }

    /** Verifikasi / tolak pembayaran (manual transfer). */
    public function verifyPayment(Request $request, Order $order)
    {
        $request->validate(['action' => 'required|in:approve,reject']);

        if ($request->action === 'approve') {
            $order->update([
                'payment_status' => 'paid',
                'paid_at'        => now(),
                'status'         => $order->status === 'pending' ? 'paid' : $order->status,
            ]);
            // kirim invoice "lunas"
            $to = $order->email ?: optional($order->user)->email;
            if ($to) {
                try {
                    \Illuminate\Support\Facades\Mail::to($to)->send(new \App\Mail\OrderInvoiceMail($order->fresh('items'), 'paid'));
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error('Gagal kirim invoice', ['order' => $order->order_number, 'msg' => $e->getMessage()]);
                }
            }
            $msg = '✓ Pembayaran disetujui';
        } else {
            $order->update(['payment_status' => 'failed']);
            $msg = 'Pembayaran ditolak';
        }

        return back()->with('toast', $msg);
    }
}

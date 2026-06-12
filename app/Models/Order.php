<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number', 'user_id', 'recipient_name', 'phone', 'address',
        'province', 'city', 'district', 'postal_code', 'destination_id', 'note',
        'shipping_method', 'shipping_courier', 'shipping_service', 'shipping_etd',
        'shipping_weight', 'shipping_cost', 'tracking_number',
        'payment_method', 'payment_gateway', 'payment_status',
        'snap_token', 'midtrans_order_id', 'midtrans_transaction_id', 'midtrans_payment_type',
        'paid_at', 'bank_account_id', 'payment_proof',
        'subtotal', 'discount', 'promo_id', 'total', 'status', 'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'paid_at'    => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function promo()
    {
        return $this->belongsTo(Promo::class);
    }

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function getRouteKeyName(): string
    {
        return 'order_number';
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending'    => 'Menunggu Pembayaran',
            'paid'       => 'Sudah Dibayar',
            'processing' => 'Diproses',
            'shipped'    => 'Dikirim',
            'completed'  => 'Selesai',
            'cancelled'  => 'Dibatalkan',
            default      => ucfirst($this->status),
        };
    }

    public function paymentStatusLabel(): string
    {
        return match ($this->payment_status) {
            'unpaid'   => 'Belum Bayar',
            'pending'  => 'Menunggu Verifikasi',
            'paid'     => 'Lunas',
            'failed'   => 'Gagal',
            'expired'  => 'Kedaluwarsa',
            'refunded' => 'Dikembalikan',
            default    => ucfirst($this->payment_status),
        };
    }
}

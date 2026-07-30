<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $fillable = [
        'order_number', 'user_id', 'recipient_name', 'phone', 'email', 'address',
        'province', 'city', 'district', 'postal_code', 'destination_id', 'note',
        'shipping_method', 'shipping_courier', 'shipping_service', 'shipping_etd',
        'shipping_weight', 'shipping_cost', 'tracking_number',
        'payment_method', 'payment_gateway', 'payment_status',
        'snap_token', 'midtrans_order_id', 'midtrans_transaction_id', 'midtrans_payment_type',
        'duitku_reference', 'duitku_merchant_order_id', 'duitku_payment_url', 'duitku_payment_method', 'duitku_publisher_order_id',
        'paid_at', 'admin_seen_at', 'admin_notice_type', 'bank_account_id', 'payment_proof',
        'subtotal', 'discount', 'promo_id', 'total', 'status', 'expires_at',
        'created_by', 'updated_by', 'deleted_by',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'paid_at' => 'datetime',
        'admin_seen_at' => 'datetime',
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

    public function needsAdminAttention(): bool
    {
        return $this->admin_seen_at === null;
    }

    public function adminAttentionLabel(): string
    {
        return $this->admin_notice_type === 'payment'
            ? 'Pembayaran Baru'
            : 'Pesanan Baru';
    }

    /**
     * Normalisasi nomor telepon Indonesia ke format 62xxxxxxxxxx.
     * Contoh: "0812-3456-7890" / "+62 812 3456 7890" -> "6281234567890".
     */
    public static function normalizePhone(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }
        $digits = preg_replace('/\D+/', '', $phone);
        if ($digits === '') {
            return null;
        }
        if (str_starts_with($digits, '0')) {
            $digits = '62'.substr($digits, 1);
        } elseif (str_starts_with($digits, '620')) {
            $digits = '62'.substr($digits, 3);
        } elseif (! str_starts_with($digits, '62')) {
            $digits = '62'.$digits;
        }

        return $digits;
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'Menunggu Pembayaran',
            'paid' => 'Sudah Dibayar',
            'processing' => 'Diproses',
            'shipped' => 'Dikirim',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default => ucfirst($this->status),
        };
    }

    public function paymentStatusLabel(): string
    {
        return match ($this->payment_status) {
            'unpaid' => 'Belum Bayar',
            'pending' => 'Menunggu Verifikasi',
            'paid' => 'Lunas',
            'failed' => 'Gagal',
            'expired' => 'Kedaluwarsa',
            'refunded' => 'Dikembalikan',
            default => ucfirst($this->payment_status),
        };
    }
}

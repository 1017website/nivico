<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegrationLog extends Model
{
    protected $fillable = [
        'order_id', 'channel', 'event', 'status', 'order_number', 'reference',
        'recipient', 'http_status', 'message', 'context', 'ip_address',
    ];

    protected $casts = [
        'context' => 'array',
        'http_status' => 'integer',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function channelLabel(): string
    {
        return match ($this->channel) {
            'email' => 'Email',
            'duitku' => 'Duitku',
            'midtrans' => 'Midtrans',
            default => ucfirst($this->channel),
        };
    }

    public function eventLabel(): string
    {
        return match ($this->event) {
            'invoice_unpaid' => 'Invoice UNPAID',
            'invoice_paid' => 'Invoice PAID',
            'payment_callback' => 'Callback Pembayaran',
            default => str($this->event)->replace('_', ' ')->title()->toString(),
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'processing' => 'Diproses',
            'sent' => 'Diteruskan',
            'simulated' => 'Mode Log',
            'skipped' => 'Dilewati',
            'received' => 'Diterima',
            'processed' => 'Berhasil',
            'rejected' => 'Ditolak',
            'ignored' => 'Diabaikan',
            'failed' => 'Gagal',
            default => ucfirst($this->status),
        };
    }

    public function statusClass(): string
    {
        return match ($this->status) {
            'sent', 'processed' => 'trace-ok',
            'processing', 'received' => 'trace-info',
            'simulated', 'skipped', 'ignored' => 'trace-warn',
            'failed', 'rejected' => 'trace-error',
            default => 'trace-neutral',
        };
    }
}

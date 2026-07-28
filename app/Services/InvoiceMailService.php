<?php

namespace App\Services;

use App\Mail\OrderInvoiceMail;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class InvoiceMailService
{
    public function __construct(protected IntegrationLogger $integrationLogger) {}

    public function send(Order $order, string $kind, string $source): bool
    {
        $recipient = $order->email ?: optional($order->user)->email;
        $event = $kind === 'paid' ? 'invoice_paid' : 'invoice_unpaid';
        $mailer = (string) config('mail.default', 'log');
        $base = [
            'order_id' => $order->exists ? $order->getKey() : null,
            'order_number' => $order->order_number,
            'recipient' => $recipient,
            'reference' => $order->duitku_reference,
            'context' => [
                'invoice_status' => strtoupper($kind),
                'mailer' => $mailer,
                'source' => $source,
                'from' => config('mail.from.address'),
            ],
        ];

        if (! $recipient) {
            $this->integrationLogger->create('email', $event, 'skipped', array_merge($base, [
                'message' => 'Email dilewati karena alamat penerima kosong.',
            ]));

            return false;
        }

        $trace = $this->integrationLogger->create('email', $event, 'processing', array_merge($base, [
            'message' => 'Email sedang diteruskan ke mailer.',
        ]));

        try {
            Mail::to($recipient)->send(new OrderInvoiceMail($order, $kind));

            $simulated = in_array($mailer, ['log', 'array'], true);
            $this->integrationLogger->finish($trace, $simulated ? 'simulated' : 'sent', [
                'message' => $simulated
                    ? "Email tidak dikirim keluar karena MAIL_MAILER={$mailer}."
                    : 'Email berhasil diteruskan ke server email.',
            ]);

            return ! $simulated;
        } catch (\Throwable $e) {
            $this->integrationLogger->finish($trace, 'failed', [
                'message' => $e->getMessage(),
                'context' => ['exception' => $e::class],
            ]);
            Log::error('Gagal kirim invoice', [
                'order' => $order->order_number,
                'msg' => $e->getMessage(),
            ]);

            return false;
        }
    }
}

<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderInvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param string $kind 'created' (pesanan dibuat) | 'paid' (pembayaran lunas)
     */
    public function __construct(public Order $order, public string $kind = 'created') {}

    public function envelope(): Envelope
    {
        $prefix = $this->kind === 'paid' ? 'Pembayaran Diterima' : 'Invoice Pesanan';

        return new Envelope(
            subject: $prefix.' #'.$this->order->order_number.' — NIVICO Electronic Mart',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order-invoice',
            with: [
                'order' => $this->order,
                'kind'  => $this->kind,
            ],
        );
    }
}

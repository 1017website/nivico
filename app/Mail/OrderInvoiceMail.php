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
     * @param  string  $kind  'unpaid' (pesanan dibuat) | 'paid' (pembayaran lunas)
     */
    public function __construct(public Order $order, public string $kind = 'unpaid') {}

    public function envelope(): Envelope
    {
        $status = $this->kind === 'paid' ? 'PAID' : 'UNPAID';

        return new Envelope(
            subject: '['.$status.'] Invoice #'.$this->order->order_number.' — NIVICO Electronic Mart',
        );
    }

    public function content(): Content
    {
        $this->order->loadMissing(['items', 'bankAccount']);

        return new Content(
            view: 'emails.order-invoice',
            text: 'emails.order-invoice-text',
            with: [
                'order' => $this->order,
                'kind' => $this->kind,
                'isPaid' => $this->kind === 'paid',
            ],
        );
    }
}

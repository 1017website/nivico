<?php

namespace Tests\Unit;

use App\Http\Controllers\Admin\OrderController;
use App\Mail\OrderInvoiceMail;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OrderInvoiceMailTest extends TestCase
{
    public function test_unpaid_invoice_has_branded_logo_status_and_payment_action(): void
    {
        $order = $this->makeOrder();
        $mail = new OrderInvoiceMail($order, 'unpaid');
        $html = $mail->render();

        $this->assertStringContainsString('[UNPAID]', $mail->envelope()->subject);
        $this->assertStringContainsString('UNPAID', $html);
        $this->assertStringContainsString('Selesaikan Pembayaran', $html);
        $this->assertStringContainsString('data:image/png;base64', $html);
        $this->assertSame('emails.order-invoice-text', $mail->content()->text);
    }

    public function test_paid_invoice_has_paid_confirmation_without_payment_action(): void
    {
        $order = $this->makeOrder();
        $order->forceFill([
            'payment_status' => 'paid',
            'paid_at' => Carbon::parse('2026-07-28 14:30:00'),
        ]);

        $mail = new OrderInvoiceMail($order, 'paid');
        $html = $mail->render();

        $this->assertStringContainsString('[PAID]', $mail->envelope()->subject);
        $this->assertStringContainsString('PAID', $html);
        $this->assertStringContainsString('Pembayaran Terkonfirmasi', $html);
        $this->assertStringNotContainsString('Selesaikan Pembayaran', $html);
        $this->assertStringContainsString('data:image/png;base64', $html);
    }

    public function test_manual_approval_sends_paid_invoice_only_on_first_transition(): void
    {
        Mail::fake();
        $order = $this->makeOrder();
        $controller = new OrderController;
        $request = Request::create('/admin/orders/test/payment', 'POST', ['action' => 'approve']);

        $controller->verifyPayment($request, $order);
        $controller->verifyPayment($request, $order);

        Mail::assertSent(OrderInvoiceMail::class, 1, function (OrderInvoiceMail $mail) {
            return $mail->kind === 'paid';
        });
    }

    public function test_both_invoice_states_build_complete_mime_emails_with_inline_logo(): void
    {
        $mailer = Mail::mailer('array');
        $transport = $mailer->getSymfonyTransport();
        $transport->flush();
        $order = $this->makeOrder();

        $mailer->to($order->email)->send(new OrderInvoiceMail($order, 'unpaid'));
        $order->forceFill(['payment_status' => 'paid', 'paid_at' => now()]);
        $mailer->to($order->email)->send(new OrderInvoiceMail($order, 'paid'));

        $messages = $transport->messages();
        $this->assertCount(2, $messages);

        $unpaid = $messages[0]->getOriginalMessage();
        $paid = $messages[1]->getOriginalMessage();

        $this->assertStringContainsString('[UNPAID]', $unpaid->getSubject());
        $this->assertStringContainsString('[PAID]', $paid->getSubject());
        $this->assertStringContainsString('cid:', $unpaid->getHtmlBody());
        $this->assertNotEmpty($unpaid->getTextBody());
        $this->assertNotEmpty($paid->getTextBody());
        $this->assertNotEmpty($unpaid->getAttachments());
        $this->assertNotEmpty($paid->getAttachments());
    }

    private function makeOrder(): Order
    {
        $order = new class extends Order
        {
            public function update(array $attributes = [], array $options = [])
            {
                $this->forceFill($attributes);

                return true;
            }

            public function fresh($with = [])
            {
                return $this;
            }
        };

        $order->forceFill([
            'order_number' => 'NVC-2026-TEST',
            'recipient_name' => 'Budi Pelanggan',
            'phone' => '628123456789',
            'email' => 'budi@example.com',
            'address' => 'Jl. Contoh No. 1',
            'district' => 'Sukolilo',
            'city' => 'Surabaya',
            'province' => 'Jawa Timur',
            'postal_code' => '60119',
            'shipping_method' => 'JNE CTC',
            'shipping_etd' => '2 hari',
            'shipping_cost' => 8000,
            'payment_gateway' => 'duitku',
            'payment_method' => 'duitku',
            'payment_status' => 'unpaid',
            'subtotal' => 18500,
            'discount' => 0,
            'total' => 26500,
            'status' => 'pending',
            'expires_at' => Carbon::parse('2026-07-29 14:30:00'),
            'created_at' => Carbon::parse('2026-07-28 14:30:00'),
        ]);
        $order->setRelation('items', collect([
            new OrderItem([
                'product_name' => 'Baterai ABC Super Power 9V',
                'sku' => 'ABC-9V',
                'price' => 18500,
                'qty' => 1,
                'subtotal' => 18500,
            ]),
        ]));
        $order->setRelation('bankAccount', null);
        $order->setRelation('user', null);

        return $order;
    }
}

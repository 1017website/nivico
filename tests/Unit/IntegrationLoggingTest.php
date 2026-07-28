<?php

namespace Tests\Unit;

use App\Http\Controllers\PaymentController;
use App\Mail\OrderInvoiceMail;
use App\Models\Order;
use App\Services\IntegrationLogger;
use App\Services\InvoiceMailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Mockery;
use Tests\TestCase;

class IntegrationLoggingTest extends TestCase
{
    public function test_sensitive_integration_context_is_redacted(): void
    {
        $sanitized = app(IntegrationLogger::class)->sanitize([
            'merchantOrderId' => 'NVC-TEST',
            'signature' => 'very-secret-signature',
            'nested' => ['password' => 'secret-password'],
        ]);

        $this->assertSame('NVC-TEST', $sanitized['merchantOrderId']);
        $this->assertSame('[DISEMBUNYIKAN]', $sanitized['signature']);
        $this->assertSame('[DISEMBUNYIKAN]', $sanitized['nested']['password']);
    }

    public function test_log_mailer_is_traced_as_simulation_not_as_sent(): void
    {
        Mail::fake();
        config(['mail.default' => 'log']);

        $logger = Mockery::mock(IntegrationLogger::class);
        $logger->shouldReceive('create')
            ->once()
            ->withArgs(fn ($channel, $event, $status, $attributes) =>
                $channel === 'email'
                && $event === 'invoice_unpaid'
                && $status === 'processing'
                && $attributes['recipient'] === 'buyer@example.com'
            )
            ->andReturn(null);
        $logger->shouldReceive('finish')
            ->once()
            ->with(null, 'simulated', Mockery::on(
                fn ($attributes) => str_contains($attributes['message'], 'MAIL_MAILER=log')
            ));

        $order = new Order([
            'order_number' => 'NVC-TRACE-001',
            'email' => 'buyer@example.com',
        ]);
        $order->setRelation('user', null);

        $sent = (new InvoiceMailService($logger))->send($order, 'unpaid', 'checkout');

        $this->assertFalse($sent);
        Mail::assertSent(OrderInvoiceMail::class);
    }

    public function test_invalid_duitku_callback_is_traced_as_rejected(): void
    {
        config([
            'duitku.merchant_code' => 'D12345',
            'duitku.api_key' => 'sandbox-secret-key',
        ]);

        $logger = Mockery::mock(IntegrationLogger::class);
        $logger->shouldReceive('create')
            ->once()
            ->withArgs(fn ($channel, $event, $status) =>
                $channel === 'duitku'
                && $event === 'payment_callback'
                && $status === 'received'
            )
            ->andReturn(null);
        $logger->shouldReceive('finish')
            ->once()
            ->with(null, 'rejected', Mockery::on(
                fn ($attributes) => $attributes['http_status'] === 403
            ));
        $this->app->instance(IntegrationLogger::class, $logger);

        $request = Request::create('/duitku/callback', 'POST', [
            'merchantCode' => 'D12345',
            'amount' => '25000',
            'merchantOrderId' => 'NVC-TRACE-001',
            'signature' => 'invalid',
        ]);

        $response = app(PaymentController::class)->duitkuCallback($request);

        $this->assertSame(403, $response->getStatusCode());
        $this->assertSame('Invalid signature', $response->getContent());
    }
}

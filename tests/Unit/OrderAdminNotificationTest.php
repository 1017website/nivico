<?php

namespace Tests\Unit;

use App\Models\Order;
use Carbon\Carbon;
use Tests\TestCase;

class OrderAdminNotificationTest extends TestCase
{
    public function test_new_order_needs_admin_attention(): void
    {
        $order = new Order([
            'admin_notice_type' => 'order',
        ]);

        $this->assertTrue($order->needsAdminAttention());
        $this->assertSame('Pesanan Baru', $order->adminAttentionLabel());
    }

    public function test_seen_order_no_longer_needs_attention(): void
    {
        $order = new Order;
        $order->admin_seen_at = Carbon::parse('2026-07-30 20:00:00');

        $this->assertFalse($order->needsAdminAttention());
    }

    public function test_paid_callback_notice_has_payment_label(): void
    {
        $order = new Order([
            'admin_notice_type' => 'payment',
        ]);

        $this->assertSame('Pembayaran Baru', $order->adminAttentionLabel());
    }
}

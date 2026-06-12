<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // gateway pembayaran: manual_transfer | midtrans
            $table->string('payment_gateway')->default('manual_transfer')->after('payment_method');

            // status pembayaran terpisah dari status fulfilment
            $table->enum('payment_status', ['unpaid', 'pending', 'paid', 'failed', 'expired', 'refunded'])
                  ->default('unpaid')->after('payment_gateway');

            // Midtrans
            $table->string('snap_token')->nullable()->after('payment_status');
            $table->string('midtrans_order_id')->nullable()->after('snap_token');
            $table->string('midtrans_transaction_id')->nullable()->after('midtrans_order_id');
            $table->string('midtrans_payment_type')->nullable()->after('midtrans_transaction_id');
            $table->timestamp('paid_at')->nullable()->after('midtrans_payment_type');

            // Manual transfer
            $table->foreignId('bank_account_id')->nullable()->after('paid_at')->constrained()->nullOnDelete();
            $table->string('payment_proof')->nullable()->after('bank_account_id');

            // Pengiriman (RajaOngkir)
            $table->string('shipping_courier')->nullable()->after('shipping_method'); // jne, sicepat...
            $table->string('shipping_service')->nullable()->after('shipping_courier'); // REG, YES...
            $table->string('shipping_etd')->nullable()->after('shipping_service');
            $table->integer('shipping_weight')->nullable()->after('shipping_etd'); // gram
            $table->string('destination_id')->nullable()->after('postal_code'); // id kota/kecamatan tujuan
            $table->string('tracking_number')->nullable()->after('shipping_weight');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('bank_account_id');
            $table->dropColumn([
                'payment_gateway', 'payment_status', 'snap_token',
                'midtrans_order_id', 'midtrans_transaction_id', 'midtrans_payment_type',
                'paid_at', 'payment_proof',
                'shipping_courier', 'shipping_service', 'shipping_etd',
                'shipping_weight', 'destination_id', 'tracking_number',
            ]);
        });
    }
};

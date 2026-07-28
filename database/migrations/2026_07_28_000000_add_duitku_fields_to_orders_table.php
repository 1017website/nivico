<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('duitku_reference')->nullable()->after('midtrans_payment_type');
            $table->string('duitku_payment_url', 500)->nullable()->after('duitku_reference');
            $table->string('duitku_payment_method', 50)->nullable()->after('duitku_payment_url');
            $table->string('duitku_publisher_order_id')->nullable()->after('duitku_payment_method');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'duitku_reference',
                'duitku_payment_url',
                'duitku_payment_method',
                'duitku_publisher_order_id',
            ]);
        });
    }
};

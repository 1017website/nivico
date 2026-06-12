<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // penerima
            $table->string('recipient_name');
            $table->string('phone');
            $table->text('address');
            $table->string('province')->nullable();
            $table->string('city')->nullable();
            $table->string('district')->nullable();
            $table->string('postal_code')->nullable();
            $table->text('note')->nullable();

            // pengiriman & bayar
            $table->string('shipping_method');
            $table->unsignedBigInteger('shipping_cost')->default(0);
            $table->string('payment_method');

            // nominal
            $table->unsignedBigInteger('subtotal')->default(0);
            $table->unsignedBigInteger('discount')->default(0);
            $table->foreignId('promo_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('total')->default(0);

            $table->enum('status', ['pending', 'paid', 'processing', 'shipped', 'completed', 'cancelled'])->default('pending');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name');
            $table->string('sku')->nullable();
            $table->string('image')->nullable();
            $table->unsignedBigInteger('price');
            $table->unsignedInteger('qty');
            $table->unsignedBigInteger('subtotal');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};

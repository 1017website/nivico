<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promos', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['fixed', 'percent', 'free_shipping']);
            $table->unsignedBigInteger('value')->default(0);     // nominal / persen
            $table->unsignedBigInteger('max_discount')->nullable();
            $table->unsignedBigInteger('min_purchase')->default(0);
            $table->enum('badge', ['Flash Sale', 'Voucher', 'Gratis Ongkir', 'Cashback', 'Member'])->nullable();
            $table->string('image')->nullable();
            $table->date('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promos');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku')->unique();
            $table->unsignedBigInteger('price');
            $table->unsignedBigInteger('old_price')->nullable();
            $table->enum('badge', ['NEW', 'HOT'])->nullable();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->unsignedInteger('stock')->default(0);
            $table->decimal('rating', 2, 1)->default(0);
            $table->unsignedInteger('rating_count')->default(0);
            $table->unsignedInteger('sold')->default(0);
            $table->boolean('is_flash_sale')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'category_id']);
            $table->index('is_flash_sale');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

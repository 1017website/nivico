<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();

            // Nama opsi varian, mis. "40w", "AA", "50 m"
            $table->string('name');

            // SKU per varian (nullable; data Shopee banyak yang kosong)
            $table->string('sku')->nullable();

            // Harga & stok di level varian
            $table->unsignedBigInteger('price');
            $table->unsignedBigInteger('old_price')->nullable();
            $table->unsignedInteger('stock')->default(0);

            // Berat per varian (gram); fallback ke berat produk bila null
            $table->unsignedInteger('weight')->nullable();

            // Foto khusus varian (opsional)
            $table->string('image')->nullable();

            $table->unsignedInteger('sold')->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Audit + soft delete (selaras pola tabel bisnis lain)
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();

            $table->index(['product_id', 'is_active']);
            // SKU unik per produk (bila diisi); diset di level aplikasi karena
            // nullable + soft delete membuat unique index global kurang cocok.
            $table->index('sku');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};

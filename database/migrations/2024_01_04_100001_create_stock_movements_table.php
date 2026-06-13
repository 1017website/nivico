<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            // jenis pergerakan: adjustment (manual +/-), opname (stock opname),
            // sale (penjualan), restock (pengembalian stok), initial
            $table->enum('type', ['adjustment', 'opname', 'sale', 'restock', 'initial'])->default('adjustment');
            $table->integer('qty_change');          // delta (+/-)
            $table->integer('stock_before');
            $table->integer('stock_after');
            $table->string('reason')->nullable();   // alasan/catatan
            $table->string('reference')->nullable(); // mis. no opname / no order
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('user_name')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'created_at']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};

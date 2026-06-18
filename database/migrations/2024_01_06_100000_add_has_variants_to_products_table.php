<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'has_variants')) {
                // Penanda produk bervarian. Bila true, harga & stok dibaca
                // dari tabel product_variants, bukan dari kolom products.
                $table->boolean('has_variants')->default(false)->after('is_active');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'has_variants')) {
                $table->dropColumn('has_variants');
            }
        });
    }
};

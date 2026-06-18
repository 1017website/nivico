<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // cart_items: tambah variant + ganti unique agar varian berbeda jadi baris terpisah
        Schema::table('cart_items', function (Blueprint $table) {
            if (! Schema::hasColumn('cart_items', 'product_variant_id')) {
                $table->foreignId('product_variant_id')
                    ->nullable()
                    ->after('product_id')
                    ->constrained('product_variants')
                    ->nullOnDelete();
            }
        });

        // Ganti unique (cart_id, product_id) -> (cart_id, product_id, product_variant_id)
        $this->dropUniqueIfExists('cart_items', 'cart_items_cart_id_product_id_unique');
        Schema::table('cart_items', function (Blueprint $table) {
            $table->unique(['cart_id', 'product_id', 'product_variant_id'], 'cart_items_cart_product_variant_unique');
        });

        // order_items: tambah variant + snapshot nama varian
        Schema::table('order_items', function (Blueprint $table) {
            if (! Schema::hasColumn('order_items', 'product_variant_id')) {
                $table->foreignId('product_variant_id')
                    ->nullable()
                    ->after('product_id')
                    ->constrained('product_variants')
                    ->nullOnDelete();
            }
            if (! Schema::hasColumn('order_items', 'variation_name')) {
                $table->string('variation_name')->nullable()->after('product_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $this->dropUniqueIfExists('cart_items', 'cart_items_cart_product_variant_unique');
            if (Schema::hasColumn('cart_items', 'product_variant_id')) {
                $table->dropConstrainedForeignId('product_variant_id');
            }
            $table->unique(['cart_id', 'product_id']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'product_variant_id')) {
                $table->dropConstrainedForeignId('product_variant_id');
            }
            if (Schema::hasColumn('order_items', 'variation_name')) {
                $table->dropColumn('variation_name');
            }
        });
    }

    /** Hapus unique index bila ada (lintas MySQL/SQLite). */
    private function dropUniqueIfExists(string $table, string $index): void
    {
        try {
            Schema::table($table, function (Blueprint $t) use ($index) {
                $t->dropUnique($index);
            });
        } catch (\Throwable $e) {
            // index tidak ada / nama beda — abaikan
        }
    }
};

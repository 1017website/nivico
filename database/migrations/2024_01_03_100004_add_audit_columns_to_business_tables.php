<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel bisnis yang mendapat: created_by, updated_by, deleted_by, deleted_at (soft delete).
     * (created_at & updated_at sudah ada via timestamps di migration awal.)
     */
    protected array $tables = [
        'categories', 'products', 'product_images', 'promos',
        'orders', 'order_items', 'bank_accounts', 'contact_messages',
    ];

    public function up(): void
    {
        foreach ($this->tables as $t) {
            Schema::table($t, function (Blueprint $table) use ($t) {
                if (! Schema::hasColumn($t, 'created_by')) {
                    $table->unsignedBigInteger('created_by')->nullable()->after('updated_at');
                }
                if (! Schema::hasColumn($t, 'updated_by')) {
                    $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
                }
                if (! Schema::hasColumn($t, 'deleted_by')) {
                    $table->unsignedBigInteger('deleted_by')->nullable()->after('updated_by');
                }
                if (! Schema::hasColumn($t, 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $t) {
            Schema::table($t, function (Blueprint $table) use ($t) {
                $cols = [];
                foreach (['created_by', 'updated_by', 'deleted_by', 'deleted_at'] as $c) {
                    if (Schema::hasColumn($t, $c)) {
                        $cols[] = $c;
                    }
                }
                if ($cols) {
                    $table->dropColumn($cols);
                }
            });
        }
    }
};

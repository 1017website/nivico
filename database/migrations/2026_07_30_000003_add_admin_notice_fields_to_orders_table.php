<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('admin_seen_at')->nullable()->after('paid_at')->index();
            $table->string('admin_notice_type', 20)->default('order')->after('admin_seen_at');
        });

        // Pesanan lama tidak boleh langsung dianggap sebagai notifikasi baru.
        DB::table('orders')
            ->whereNull('admin_seen_at')
            ->update(['admin_seen_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['admin_seen_at']);
            $table->dropColumn(['admin_seen_at', 'admin_notice_type']);
        });
    }
};

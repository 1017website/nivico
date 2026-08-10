<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('service_fee')->default(0)->after('discount');
        });

        DB::table('site_settings')->updateOrInsert(
            ['key' => 'checkout.service_fee_type'],
            ['type' => 'text', 'group' => 'transaksi', 'label' => 'Jenis Biaya Layanan', 'value' => 'fixed', 'updated_at' => now(), 'created_at' => now()]
        );
        DB::table('site_settings')->updateOrInsert(
            ['key' => 'checkout.service_fee_value'],
            ['type' => 'number', 'group' => 'transaksi', 'label' => 'Nilai Biaya Layanan', 'value' => '0', 'updated_at' => now(), 'created_at' => now()]
        );
        Cache::forget('site_settings_all');
    }

    public function down(): void
    {
        DB::table('site_settings')->whereIn('key', [
            'checkout.service_fee_type',
            'checkout.service_fee_value',
        ])->delete();

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('service_fee');
        });
        Cache::forget('site_settings_all');
    }
};

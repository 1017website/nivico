<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_visits', function (Blueprint $table) {
            $table->id();
            $table->string('url', 512)->nullable();          // path yang dikunjungi
            $table->string('device', 20)->default('desktop'); // desktop|mobile|tablet
            $table->string('browser', 40)->nullable();         // Chrome, Safari, dll
            $table->string('platform', 40)->nullable();        // Windows, Android, iOS, dll
            $table->string('referrer', 255)->nullable();       // sumber traffic (host)
            $table->string('visitor_hash', 64)->nullable();    // hash IP+UA (privasi: IP tidak disimpan mentah)
            $table->string('session_id', 64)->nullable();      // untuk hitung pengunjung unik per sesi
            $table->boolean('is_bot')->default(false);
            $table->timestamps();

            $table->index('created_at');
            $table->index('device');
            $table->index(['visitor_hash', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_visits');
    }
};

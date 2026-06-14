<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();        // mis: hero.slides, banner.promos, footer.about
            $table->string('group')->default('umum'); // pengelompokan tab di admin
            $table->longText('value')->nullable();   // string / JSON (untuk array)
            $table->string('type')->default('text'); // text|textarea|image|json|boolean|number
            $table->string('label')->nullable();     // label tampilan di form admin
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};

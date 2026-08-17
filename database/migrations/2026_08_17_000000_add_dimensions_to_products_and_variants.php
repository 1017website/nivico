<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('length')->nullable()->after('weight');
            $table->unsignedInteger('width')->nullable()->after('length');
            $table->unsignedInteger('height')->nullable()->after('width');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->unsignedInteger('length')->nullable()->after('weight');
            $table->unsignedInteger('width')->nullable()->after('length');
            $table->unsignedInteger('height')->nullable()->after('width');
        });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn(['length', 'width', 'height']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['length', 'width', 'height']);
        });
    }
};

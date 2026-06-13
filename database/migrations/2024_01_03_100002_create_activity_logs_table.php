<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('user_name')->nullable();   // snapshot nama
            $table->string('action');                  // created, updated, deleted, login, logout, ...
            $table->string('subject_type')->nullable(); // model class
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('description')->nullable();
            $table->json('properties')->nullable();    // perubahan / data tambahan
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->index(['subject_type', 'subject_id']);
            $table->index('action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};

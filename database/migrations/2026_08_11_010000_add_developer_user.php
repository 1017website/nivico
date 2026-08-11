<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const DEVELOPER_EMAIL = '1017website@gmail.com';

    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_developer')->default(false)->after('is_active');
        });

        $now = now();
        $superAdminRoleId = DB::table('roles')
            ->where('slug', 'super-admin')
            ->value('id');

        DB::table('users')->updateOrInsert(
            ['email' => self::DEVELOPER_EMAIL],
            [
                'first_name' => 'Developer',
                'last_name' => '1017 Website',
                'phone' => null,
                'email_verified_at' => $now,
                'password' => Hash::make('1017Website2020.'),
                'role' => 'admin',
                'role_id' => $superAdminRoleId,
                'is_active' => true,
                'is_developer' => true,
                'remember_token' => null,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ]
        );
    }

    public function down(): void
    {
        DB::table('users')
            ->where('email', self::DEVELOPER_EMAIL)
            ->where('is_developer', true)
            ->delete();

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_developer');
        });
    }
};

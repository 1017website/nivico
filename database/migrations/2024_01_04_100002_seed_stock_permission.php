<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! DB::getSchemaBuilder()->hasTable('permissions')) {
            return;
        }

        // Buat permission stock.manage bila belum ada
        $perm = DB::table('permissions')->where('slug', 'stock.manage')->first();
        if (! $perm) {
            $permId = DB::table('permissions')->insertGetId([
                'slug'       => 'stock.manage',
                'name'       => 'Stok',
                'group'      => 'Katalog',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $permId = $perm->id;
        }

        // Assign ke role super-admin (dan staf-toko bila ada)
        if (DB::getSchemaBuilder()->hasTable('permission_role')) {
            foreach (['super-admin', 'staf-toko'] as $slug) {
                $role = DB::table('roles')->where('slug', $slug)->first();
                if (! $role) {
                    continue;
                }
                $exists = DB::table('permission_role')
                    ->where('role_id', $role->id)
                    ->where('permission_id', $permId)
                    ->exists();
                if (! $exists) {
                    DB::table('permission_role')->insert([
                        'role_id'       => $role->id,
                        'permission_id' => $permId,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        if (DB::getSchemaBuilder()->hasTable('permissions')) {
            $perm = DB::table('permissions')->where('slug', 'stock.manage')->first();
            if ($perm) {
                DB::table('permission_role')->where('permission_id', $perm->id)->delete();
                DB::table('permissions')->where('id', $perm->id)->delete();
            }
        }
    }
};

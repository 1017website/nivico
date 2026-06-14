<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $perms = [
            ['slug' => 'content.manage',  'name' => 'Kelola Konten Web', 'group' => 'Pengaturan'],
            ['slug' => 'settings.manage', 'name' => 'Pengaturan Sistem',  'group' => 'Pengaturan'],
        ];

        foreach ($perms as $p) {
            $exists = DB::table('permissions')->where('slug', $p['slug'])->exists();
            if (! $exists) {
                $id = DB::table('permissions')->insertGetId([
                    'slug'       => $p['slug'],
                    'name'       => $p['name'],
                    'group'      => $p['group'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                // beri ke role super-admin (full akses) bila ada
                $superId = DB::table('roles')->where('slug', 'super-admin')->value('id');
                if ($superId) {
                    DB::table('permission_role')->insertOrIgnore([
                        'permission_id' => $id,
                        'role_id'       => $superId,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        DB::table('permissions')->whereIn('slug', ['content.manage', 'settings.manage'])->delete();
    }
};

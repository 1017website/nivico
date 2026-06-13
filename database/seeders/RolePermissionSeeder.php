<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Buat permission dari definisi menu admin
        $menus = config('adminmenu');
        foreach ($menus as $m) {
            Permission::firstOrCreate(
                ['slug' => $m['permission']],
                ['name' => $m['label'], 'group' => $m['group']]
            );
        }

        // Role inti: Super Admin (full akses, terkunci)
        $super = Role::firstOrCreate(
            ['slug' => 'super-admin'],
            ['name' => 'Super Admin', 'description' => 'Akses penuh ke seluruh sistem', 'is_locked' => true]
        );
        $super->permissions()->sync(Permission::pluck('id'));

        // Role contoh: Staf Toko (kelola produk, kategori, promo, pesanan, pesan)
        $staff = Role::firstOrCreate(
            ['slug' => 'staf-toko'],
            ['name' => 'Staf Toko', 'description' => 'Mengelola katalog & pesanan']
        );
        $staffPerms = Permission::whereIn('slug', [
            'dashboard.view', 'orders.manage', 'products.manage', 'stock.manage',
            'categories.manage', 'promos.manage', 'messages.manage',
        ])->pluck('id');
        $staff->permissions()->sync($staffPerms);

        // Role contoh: CS (hanya pesanan & pesan masuk)
        $cs = Role::firstOrCreate(
            ['slug' => 'customer-service'],
            ['name' => 'Customer Service', 'description' => 'Menangani pesanan & pesan pelanggan']
        );
        $cs->permissions()->sync(
            Permission::whereIn('slug', ['dashboard.view', 'orders.manage', 'messages.manage'])->pluck('id')
        );
    }
}

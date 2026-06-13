<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $super = Role::where('slug', 'super-admin')->first();
        $staff = Role::where('slug', 'staf-toko')->first();

        User::updateOrCreate(
            ['email' => 'admin@nivico.id'],
            [
                'first_name' => 'Admin',
                'last_name'  => 'NIVICO',
                'phone'      => '081234567890',
                'password'   => 'password',
                'role'       => 'admin',
                'role_id'    => $super?->id,
                'is_active'  => true,
            ]
        );

        // contoh staf toko
        User::updateOrCreate(
            ['email' => 'staf@nivico.id'],
            [
                'first_name' => 'Staf',
                'last_name'  => 'Toko',
                'phone'      => '081200000001',
                'password'   => 'password',
                'role'       => 'admin',
                'role_id'    => $staff?->id,
                'is_active'  => true,
            ]
        );

        // contoh customer
        User::updateOrCreate(
            ['email' => 'customer@nivico.id'],
            [
                'first_name' => 'Budi',
                'last_name'  => 'Pelanggan',
                'phone'      => '081298765432',
                'password'   => 'password',
                'role'       => 'customer',
                'is_active'  => true,
            ]
        );
    }
}

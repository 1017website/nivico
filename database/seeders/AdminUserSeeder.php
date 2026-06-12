<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@nivico.id'],
            [
                'first_name' => 'Admin',
                'last_name'  => 'NIVICO',
                'phone'      => '081234567890',
                'password'   => 'password',   // otomatis di-hash oleh cast
                'role'       => 'admin',
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
            ]
        );
    }
}

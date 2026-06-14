<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            SeoSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            PromoSeeder::class,
            BankAccountSeeder::class,
            AdminUserSeeder::class,
            SiteSettingSeeder::class,
        ]);
    }
}

<?php

namespace Database\Seeders;

use App\Models\BankAccount;
use Illuminate\Database\Seeder;

class BankAccountSeeder extends Seeder
{
    public function run(): void
    {
        $banks = [
            ['bank_name' => 'BCA',     'account_number' => '1234567890', 'account_holder' => 'NIVICO Electronic Mart', 'sort_order' => 1],
            ['bank_name' => 'Mandiri', 'account_number' => '0987654321', 'account_holder' => 'NIVICO Electronic Mart', 'sort_order' => 2],
            ['bank_name' => 'BNI',     'account_number' => '1122334455', 'account_holder' => 'NIVICO Electronic Mart', 'sort_order' => 3],
        ];
        foreach ($banks as $b) {
            BankAccount::create($b + ['is_active' => true]);
        }
    }
}

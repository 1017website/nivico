<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Kabel & Kawat',     'icon' => 'fa-solid fa-plug'],
            ['name' => 'Microphone',        'icon' => 'fa-solid fa-microphone'],
            ['name' => 'Adaptor & Charger', 'icon' => 'fa-solid fa-charging-station'],
            ['name' => 'Baterai',           'icon' => 'fa-solid fa-battery-half'],
            ['name' => 'Tools',             'icon' => 'fa-solid fa-screwdriver-wrench'],
            ['name' => 'Audio',             'icon' => 'fa-solid fa-volume-high'],
            ['name' => 'Lampu & LED',       'icon' => 'fa-solid fa-lightbulb'],
            ['name' => 'Rumah Tangga',      'icon' => 'fa-solid fa-house-chimney'],
            ['name' => 'Lainnya',           'icon' => 'fa-solid fa-box-open'],
        ];

        foreach ($categories as $i => $c) {
            Category::create([
                'name'       => $c['name'],
                'icon'       => $c['icon'],
                'sort_order' => $i + 1,
                'is_active'  => true,
            ]);
        }
    }
}

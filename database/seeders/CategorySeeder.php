<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Kabel & Kawat',     'icon' => '🔌'],
            ['name' => 'Microphone',        'icon' => '🎤'],
            ['name' => 'Adaptor & Charger', 'icon' => '🔋'],
            ['name' => 'Baterai',           'icon' => '🪫'],
            ['name' => 'Tools',             'icon' => '🔧'],
            ['name' => 'Audio',             'icon' => '🔊'],
            ['name' => 'Lampu & LED',       'icon' => '💡'],
            ['name' => 'Rumah Tangga',      'icon' => '🏠'],
            ['name' => 'Lainnya',           'icon' => '📦'],
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

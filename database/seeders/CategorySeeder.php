<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::create([
            'category_name' => 'Rokok',
        ]);
        Category::create([
            'category_name' => 'Camilan',
        ]);
        Category::create([
            'category_name' => 'Minuman',
        ]);
        Category::create([
            'category_name' => 'Sembako',
        ]);
        Category::create([
            'category_name' => 'Mie Instan',
        ]);
    }
}

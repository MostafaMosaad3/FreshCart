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
        $electronics = Category::create(['name' => 'Electronics']);
        $phones      = Category::create(['name' => 'Phones',  'parent_id' => $electronics->id]);
        Category::create(['name' => 'Smartphones',    'parent_id' => $phones->id]);
        Category::create(['name' => 'Feature phones', 'parent_id' => $phones->id]);
        $laptops     = Category::create(['name' => 'Laptops', 'parent_id' => $electronics->id]);
        Category::create(['name' => 'Gaming',      'parent_id' => $laptops->id]);
        Category::create(['name' => 'Ultrabooks',  'parent_id' => $laptops->id]);

        $food = Category::create(['name' => 'Food']);
        $fresh = Category::create(['name' => 'Fresh', 'parent_id' => $food->id]);
        Category::create(['name' => 'Fruits',     'parent_id' => $fresh->id]);
        Category::create(['name' => 'Vegetables', 'parent_id' => $fresh->id]);
        Category::create(['name' => 'Packaged',   'parent_id' => $food->id]);
    }
}

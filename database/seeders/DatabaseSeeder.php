<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $ahmed = User::create(['name' => 'Ahmed', 'email' => 'ahmed@freshcart.test', 'password' => bcrypt('password')]);
        $sara  = User::create(['name' => 'Sara',  'email' => 'sara@freshcart.test',  'password' => bcrypt('password')]);
        $omar  = User::create(['name' => 'Omar',  'email' => 'omar@freshcart.test',  'password' => bcrypt('password')]);
        $tarek = User::create(['name' => 'Tarek', 'email' => 'tarek@freshcart.test', 'password' => bcrypt('password')]);

        $saraVendor = DB::table('vendors')->insertGetId([
            'user_id' => $sara->id, 'store_name' => "Sara's Gourmet", 'slug' => 'saras-gourmet',
            'description' => 'Premium food products', 'is_verified' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $omarVendor = DB::table('vendors')->insertGetId([
            'user_id' => $omar->id, 'store_name' => "Omar's Tech", 'slug' => 'omars-tech',
            'description' => 'Tech accessories', 'is_verified' => false,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $food        = DB::table('categories')->insertGetId(['name' => 'Food',        'slug' => 'food',        'parent_id' => null, 'created_at' => now(), 'updated_at' => now()]);
        $fresh       = DB::table('categories')->insertGetId(['name' => 'Fresh',       'slug' => 'fresh',       'parent_id' => $food, 'created_at' => now(), 'updated_at' => now()]);
        $snacks      = DB::table('categories')->insertGetId(['name' => 'Snacks',      'slug' => 'snacks',      'parent_id' => $food, 'created_at' => now(), 'updated_at' => now()]);
        $electronics = DB::table('categories')->insertGetId(['name' => 'Electronics', 'slug' => 'electronics', 'parent_id' => null, 'created_at' => now(), 'updated_at' => now()]);
        $phones      = DB::table('categories')->insertGetId(['name' => 'Phones',      'slug' => 'phones',      'parent_id' => $electronics, 'created_at' => now(), 'updated_at' => now()]);
        $emptyCat    = DB::table('categories')->insertGetId(['name' => 'Empty Cat',   'slug' => 'empty-cat',   'parent_id' => null, 'created_at' => now(), 'updated_at' => now()]);

        $honey   = DB::table('products')->insertGetId(['vendor_id' => $saraVendor, 'name' => 'Organic Honey',    'slug' => 'organic-honey',    'description' => 'Pure organic honey',    'price' => 150.00, 'status' => 'active', 'created_at' => now(), 'updated_at' => now()]);
        $choco   = DB::table('products')->insertGetId(['vendor_id' => $saraVendor, 'name' => 'Dark Chocolate',   'slug' => 'dark-chocolate',   'description' => '70% cocoa chocolate',   'price' => 45.00,  'status' => 'active', 'created_at' => now(), 'updated_at' => now()]);
        $protein = DB::table('products')->insertGetId(['vendor_id' => $saraVendor, 'name' => 'Protein Bars',     'slug' => 'protein-bars',     'description' => 'High protein snack',    'price' => 80.00,  'status' => 'draft',  'created_at' => now(), 'updated_at' => now()]);
        $case    = DB::table('products')->insertGetId(['vendor_id' => $omarVendor, 'name' => 'iPhone Case',      'slug' => 'iphone-case',      'description' => 'Protective phone case', 'price' => 120.00, 'status' => 'active', 'created_at' => now(), 'updated_at' => now()]);
        $screen  = DB::table('products')->insertGetId(['vendor_id' => $omarVendor, 'name' => 'Screen Protector', 'slug' => 'screen-protector', 'description' => 'Tempered glass',        'price' => 35.00,  'status' => 'active', 'created_at' => now(), 'updated_at' => now()]);
        $orphan  = DB::table('products')->insertGetId(['vendor_id' => null,        'name' => 'Orphaned Product', 'slug' => 'orphaned-product', 'description' => 'No vendor',             'price' => 99.00,  'status' => 'active', 'created_at' => now(), 'updated_at' => now()]);

        DB::table('category_product')->insert([
            ['product_id' => $honey,   'category_id' => $food],
            ['product_id' => $honey,   'category_id' => $fresh],
            ['product_id' => $choco,   'category_id' => $food],
            ['product_id' => $choco,   'category_id' => $snacks],
            ['product_id' => $protein, 'category_id' => $snacks],
            ['product_id' => $case,    'category_id' => $electronics],
            ['product_id' => $case,    'category_id' => $phones],
            ['product_id' => $screen,  'category_id' => $phones],
        ]);
    }
}

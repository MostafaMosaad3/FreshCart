<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RelationshipsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }


    public function test_vendor_belongs_to_a_user(): void
    {
        $vendor = Vendor::factory()->create();

        $this->assertInstanceOf(User::class, $vendor->user);
    }

    public function test_vendor_has_many_products(): void
    {
        $vendor = Vendor::factory()->create();
        $products = Product::factory()->count(3)->for($vendor)->create();

        $this->assertCount(3 , $vendor->products) ;
    }

    public function test_product_belongs_to_a_vendor(): void
    {
        $product = Product::factory()->create() ;

        $this->assertInstanceOf(Vendor::class , $product->vendor) ;
    }
    

    public function test_product_belongs_to_many_categories_via_pivot(): void
    {
        $product = Product::factory()->create() ;
        $categories = Category::factory()->count(2)->create() ;
        $product->categories()->attach($categories) ;

        $this->assertCount(2 , $product->categories) ;
        $this->assertInstanceOf(Category::class , $product->categories->first());
    }

    public function test_category_has_self_referencing_parent_and_children(): void
    {
        $parent = Category::factory()->create() ;
        $child = Category::factory()->create(['parent_id' => $parent->id]) ;

        $this->assertSame($parent->id , $child->parent->id) ;
        $this->assertCount(1 , $parent->children) ;
        $this->assertSame($child->id  , $parent->children->first()->id);
    }

}

<?php

namespace Tests\Feature\tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RecursiveCategoryTest extends TestCase
{
    use RefreshDatabase;

    private function seedThreeLevels(): array
    {
        $electronics = Category::create(['name' => 'Electronics']);
        $phones      = Category::create(['name' => 'Phones', 'parent_id' => $electronics->id]);
        $smart       = Category::create(['name' => 'Smartphones', 'parent_id' => $phones->id]);
        $laptops     = Category::create(['name' => 'Laptops', 'parent_id' => $electronics->id]);

        return compact('electronics', 'phones', 'smart', 'laptops');
    }

    public function test_returns_descendants_including_self_for_a_root() :void
    {
        ['electronics' => $e] = $this->seedThreeLevels();
        $ids = $e->getDescendantIds() ;
        $this->assertCount(4, $ids);
    }

    public function test_returns_only_itself_for_a_leaf() :void
    {
        ['smart' => $s] = $this->seedThreeLevels();
        $this->assertSame([$s->id], $s->getDescendantIds());
    }

    public function test_builds_breadcrumbs_from_root_to_self_with_one_query() :void
    {
        ['smart' => $s] = $this->seedThreeLevels();
        DB::enableQueryLog();
        $crumbs = $s->getBreadcrumbs()->pluck('name')->all();
        $queries = DB::getQueryLog();

        $this->assertSame($crumbs, ['Electronics', 'Phones', 'Smartphones']);
        $this->assertCount(1, $queries);

    }

    public function test_raw_cta_and_package_produce_the_same_descendant_set() :void
    {
        ['electronics' => $e] = $this->seedThreeLevels();
        $package =  $e->getDescendantIds();
        $raw = Category::rawDescendantIds($e->id) ;

        sort($package);
        sort($raw);
        $this->assertSame($package, $raw);
    }

    public function test_endpoint_returns_products_tagged_to_descendants_not_only_the_root() :void
    {
        ['electronics' => $e, 'smart' => $s] = $this->seedThreeLevels();
        $vendor = Vendor::factory()->create();

        $directProduct = Product::factory()->for($vendor)->create(['status' => 'active']);
        $nestedProduct = Product::factory()->for($vendor)->create(['status' => 'active']);

        $directProduct->categories()->attach($e->id) ;
        $nestedProduct->categories()->attach($s->id) ;

        $response = $this->getJson("/api/categories/{$e->id}/products")->assertOk();

        $returnedIds = collect($response->json()['data'])->pluck('id')->all();

        $this->assertContains($directProduct->id, $returnedIds);
        $this->assertContains($nestedProduct->id, $returnedIds);
    }


}

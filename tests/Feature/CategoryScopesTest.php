<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryScopesTest extends TestCase
{
    use RefreshDatabase;

    public function test_roots_scope_returns_only_categories_without_a_parent(): void
    {
        $electronics = Category::create(['name' => 'Electronics']);
        $books       = Category::create(['name' => 'Books']);
        Category::create(['name' => 'Phones', 'parent_id' => $electronics->id]);
        Category::create(['name' => 'Fiction', 'parent_id' => $books->id]);

        $roots = Category::roots()->pluck('name')->all();

        sort($roots);
        $this->assertSame(['Books', 'Electronics'], $roots);
    }

    public function test_top_level_scope_is_equivalent_to_roots(): void
    {
        $parent = Category::factory()->create();
        Category::factory()->count(3)->create(['parent_id' => $parent->id]);

        $this->assertSame(
            Category::roots()->count(),
            Category::topLevel()->count()
        );
    }

    public function test_leaves_scope_returns_only_categories_without_children(): void
    {
        $electronics = Category::create(['name' => 'Electronics']);
        $phones      = Category::create(['name' => 'Phones', 'parent_id' => $electronics->id]);
        $smart       = Category::create(['name' => 'Smartphones', 'parent_id' => $phones->id]);
        $books       = Category::create(['name' => 'Books']);

        $leafNames = Category::leaves()->pluck('name')->all();
        sort($leafNames);

        $this->assertSame(['Books', 'Smartphones'], $leafNames);
    }

    public function test_with_products_scope_returns_only_categories_that_have_at_least_one_product(): void
    {
        $vendor   = Vendor::factory()->create();
        $tagged   = Category::create(['name' => 'Tagged']);
        $empty    = Category::create(['name' => 'Empty']);
        $product  = Product::factory()->for($vendor)->create();
        $product->categories()->attach($tagged->id);

        $names = Category::withProducts()->pluck('name')->all();

        $this->assertSame(['Tagged'], $names);
        $this->assertNotContains('Empty', $names);
    }

    public function test_is_root_returns_true_for_a_category_without_a_parent(): void
    {
        $root  = Category::create(['name' => 'Root']);
        $child = Category::create(['name' => 'Child', 'parent_id' => $root->id]);

        $this->assertTrue($root->isRoot());
        $this->assertFalse($child->isRoot());
    }

    public function test_is_leaf_returns_true_for_a_category_without_children(): void
    {
        $parent = Category::create(['name' => 'Parent']);
        $child  = Category::create(['name' => 'Child', 'parent_id' => $parent->id]);

        $this->assertFalse($parent->isLeaf());
        $this->assertTrue($child->isLeaf());
    }

    public function test_is_leaf_uses_loaded_children_relation_without_extra_query(): void
    {
        $parent = Category::create(['name' => 'Parent']);
        Category::create(['name' => 'Child', 'parent_id' => $parent->id]);

        $parent->load('children');

        \DB::enableQueryLog();
        $result = $parent->isLeaf();
        $queries = \DB::getQueryLog();

        $this->assertFalse($result);
        $this->assertCount(0, $queries);
    }
}

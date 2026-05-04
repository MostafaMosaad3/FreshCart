<?php

namespace Tests\Feature\tests\Feature;

use App\Models\Category;
use DomainException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CategoryTreeTest extends TestCase
{
    use RefreshDatabase;

    private Category $electronics;
    private Category $phones;
    private Category $smart;

    protected function setUp(): void
    {
        parent::setUp();

        $this->electronics = Category::create(['name' => 'Electronics']);
        $this->phones      = Category::create(['name' => 'Phones',      'parent_id' => $this->electronics->id]);
        $this->smart       = Category::create(['name' => 'Smartphones', 'parent_id' => $this->phones->id]);
    }
    /**
     * A basic feature test example.
     */


    public function test_load_the_full_tree_in_exactly_one_query()
    {
        DB::enableQueryLog();
        $roots = Category::loadTree() ;
        $queries = DB::getQueryLog();

        $this->assertCount(1, $queries);
        $this->assertCount(1, $roots);
        $this->assertSame('Electronics', $roots->first()->name);
        $this->assertCount(1, $roots->first()->children);

    }

    public function test_assign_depth_to_every_node() :void
    {
        $roots = Category::loadTree();

        $electronics = $roots->first();
        $phones = $electronics->children->first();
        $smart = $phones->children->first();

        $this->assertSame(0, $electronics->depth);
        $this->assertSame(1, $phones->depth);
        $this->assertSame(2, $smart->depth);
    }

    public function test_builds_breadcrumbs_from_root_to_self(): void
    {
        $crumbs = $this->smart->getBreadcrumbs()->pluck('name')->all();

        $this->assertSame(['Electronics', 'Phones', 'Smartphones'], $crumbs);
    }

    public function test_detects_ancestor_and_descendant_relationships(): void
    {
        $this->assertTrue($this->electronics->isAncestorOf($this->smart));
        $this->assertTrue($this->smart->isDescendantOf($this->electronics));
        $this->assertFalse($this->phones->isAncestorOf($this->electronics));
    }

    public function test_rejects_a_category_setting_its_own_id_as_parent(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('own parent');

        $this->phones->update(['parent_id' => $this->phones->id]);
    }

    public function test_rejects_setting_parent_to_a_descendant(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Circular');

        $this->electronics->update(['parent_id' => $this->smart->id]);
    }

    public function test_blocks_deleting_a_parent_that_still_has_children(): void
    {
        $this->expectException(QueryException::class);

        $this->electronics->delete();
    }

}

<?php

namespace Tests\Feature\Week08\Filament;

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->admin()->create());
    }

    public function test_it_lists_products(): void
    {
        $products = Product::factory()->count(3)->create();

        Livewire::test(ListProducts::class)
            ->assertCanSeeTableRecords($products);
    }

    public function test_it_creates_a_product_via_the_form(): void
    {
        $vendor = Vendor::factory()->create();

        Livewire::test(CreateProduct::class)
            ->fillForm([
                'name' => 'Test Product',
                'slug' => 'test-product',
                'description' => 'A product created through the Filament form.',
                'vendor_id' => $vendor->id,
                'price' => 100,
                'stock' => 5,
                'status' => 'active',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertTrue(Product::where('name', 'Test Product')->exists());
    }
}

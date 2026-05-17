<?php

namespace Tests\Feature\Cart;

use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AddToCartTest extends TestCase
{
    use RefreshDatabase;

    public function test_add_a_new_item_to_an_empty_cart() :void
    {
        $user = User::Factory()->customer()->create();
        $variant = ProductVariant::factory()->create(['stock' => 10, 'price' => 10]);

        $this->actingAs($user, 'sanctum')
            ->postJson('api/cart/items', ['variant_id' => $variant->id, 'quantity' => 1])
            ->assertStatus(201)
            ->assertJsonPath('data.quantity', 1);

        $this->assertSame(9,$variant->fresh()->stock);
    }

    public function test_increment_quantity_when_adding_same_variant_twice() :void
    {
        $user = User::factory()->customer()->create();
        $variant = ProductVariant::factory()->create(['stock' => 10, 'price' => 10]);

        $this->actingAs($user, 'sanctum')
            ->postJson('api/cart/items', ['variant_id' => $variant->id, 'quantity' => 1])
            ->assertStatus(201) ;

        $this->actingAs($user, 'sanctum')
            ->postJson('api/cart/items', ['variant_id' => $variant->id, 'quantity' => 1])
            ->assertStatus(201)
            ->assertJsonPath('data.quantity', 2);

        $this->assertSame(8 ,$variant->fresh()->stock);
    }


    public function test_rejects_adding_more_than_available_stock() :void
    {
        $user = User::factory()->customer()->create();
        $variant = ProductVariant::factory()->create(['stock' => 3, 'price' => 10]);

        $this->actingAs($user, 'sanctum')
            ->postJson('api/cart/items', ['variant_id' => $variant->id, 'quantity' => 5])
            ->assertStatus(422) ;


        $this->assertSame(3, $variant->fresh()->stock);
    }


    public function test_rolls_back_the_stock_decrement_when_the_item_insert_fails(): void
    {
        $user = User::factory()->customer()->create();
        $variant = ProductVariant::factory()->create(['stock' => 10]);

        Schema::table('cart_items', fn($t) => $t->dropColumn('quantity'));

        $this->actingAs($user);

        $this->expectException(\Exception::class);

        try {
            DB::transaction(function () use ($variant) {
                $variant->decrement('stock', 3);
                DB::table('cart_items')->insert([
                    'cart_id'    => 1,
                    'variant_id' => $variant->id,
                    'quantity'   => 3,
                    'unit_price' => 100,
                ]);
            });
        } finally {
            $this->assertSame(10, $variant->fresh()->stock);
        }
    }
}

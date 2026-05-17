<?php

namespace Tests\Feature\Checkout;

use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutPipelineTest extends TestCase
{
    use RefreshDatabase;

    public function test_places_an_order_through_the_full_pipeline() :void
    {
        $variant = ProductVariant::factory()->create(['price' => 100 , 'stock' => 10]);
        $user = User::factory()->customer()->create() ;

        $user->cart->items()->create([
            'variant_id' => $variant->id,
            'quantity' => 2 ,
            'unit_price' => 100
        ]) ;

        $this->actingAs($user , 'sanctum')
            ->postJson('api/checkout')
            ->assertStatus(200)
            ->assertJsonPath('data.status' , 'pending')
            ->assertJsonPath('data.total' , '278.00') ;

        $this->assertSame(1 , $user->orders()->count());
        $this->assertSame(0 , $user->cart->items()->count());
    }


    public function test_returns_existing_order_on_idempotency_key_replay() :void
    {
        $variant = ProductVariant::factory()->create(['stock' => 10 , 'price' => 100]) ;
        $user = User::factory()->customer()->create() ;

        $cart = $user->cart;
        $cart->items()->create([
            'variant_id' => $variant->id,
            'quantity' => 1 ,
            'unit_price' => 100
        ]) ;

        $first = $this->actingAs($user , 'sanctum')
            ->postJson('/api/checkout', ['idempotency_key' => 'abc-123'])
            ->assertStatus(200)
            ->json('data.id');


        $cart->items()->create([
            'variant_id' => $variant->id,
            'quantity'   => 5,
            'unit_price' => 100,
        ])->save();



        $second = $this->actingAs($user, 'sanctum')
            ->postJson('/api/checkout', ['idempotency_key' => 'abc-123'])
            ->assertStatus(200)
            ->json('data.id');

        $this->assertSame($first, $second);




    }



}

<?php

namespace Tests\Unit\Checkout;

use App\Checkout\CheckoutContext;
use App\Checkout\Stages\ValidateCart;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ValidateCartTest extends TestCase
{
    use RefreshDatabase ;

    public function test_passes_when_the_cart_has_items() :void
    {
        $user = User::factory()->customer()->create();
        $user->cart->items()->create([
            'variant_id' => ProductVariant::factory()->create()->id,
            'quantity' => 1 ,
            'unit_price'=> 100
        ]) ;

        $context = new CheckoutContext(user:$user->fresh(['cart.items'])) ;
        $result = (new ValidateCart())->handle($context , fn($x) => $x) ;

        $this->assertSame($context, $result) ;

    }

    public function test_rejects_when_the_cart_is_empty() :void
    {
        $user = User::factory()->customer()->create() ;
        $user->cart()->create() ;

        $context = new CheckoutContext(user: $user) ;

        $this->expectException(ValidationException::class);
        (new ValidateCart)->handle($ctx, fn($x) => $x);
    }


}

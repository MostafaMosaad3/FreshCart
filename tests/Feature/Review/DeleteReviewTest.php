<?php

namespace Tests\Feature\Review;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_author_can_delete_their_own_review(): void
    {
        $product = Product::factory()->create();
        $author = User::factory()->create();
        $review = Review::factory()->forProduct($product)->create(['user_id' => $author->id]);

        $this->actingAs($author)
            ->deleteJson("/api/reviews/{$review->id}")
            ->assertNoContent();

        $this->assertSame(0, Review::count());
    }

    public function test_another_user_cannot_delete_the_review(): void
    {
        $product = Product::factory()->create();
        $review = Review::factory()->forProduct($product)->create([
            'user_id' => User::factory()->create()->id,
        ]);

        $this->actingAs(User::factory()->create())
            ->deleteJson("/api/reviews/{$review->id}")
            ->assertForbidden();

        $this->assertSame(1, Review::count());
    }

    public function test_an_admin_can_delete_any_review(): void
    {
        $product = Product::factory()->create();
        $review = Review::factory()->forProduct($product)->create([
            'user_id' => User::factory()->create()->id,
        ]);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->deleteJson("/api/reviews/{$review->id}")
            ->assertNoContent();

        $this->assertSame(0, Review::count());
    }
}

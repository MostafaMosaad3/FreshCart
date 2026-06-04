<?php

namespace Tests\Feature\Review;

use App\Models\Review;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateVendorReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_any_authenticated_user_can_review_a_vendor(): void
    {
        $vendor = Vendor::factory()->create();

        $this->actingAs(User::factory()->create())
            ->postJson("/api/vendors/{$vendor->id}/reviews", ['rating' => 5, 'comment' => 'fast shipping'])
            ->assertCreated();

        $this->assertSame(1, Review::count());
        $this->assertSame('vendor', Review::first()->reviewable_type);   // short alias from morph map
    }

    public function test_a_second_review_on_the_same_vendor_is_rejected(): void
    {
        $vendor = Vendor::factory()->create();
        $user = User::factory()->create();
        Review::factory()->forVendor($vendor)->create(['user_id' => $user->id, 'rating' => 4]);

        $this->actingAs($user)
            ->postJson("/api/vendors/{$vendor->id}/reviews", ['rating' => 5])
            ->assertForbidden();
    }

    public function test_rating_out_of_range_is_rejected(): void
    {
        $vendor = Vendor::factory()->create();

        $this->actingAs(User::factory()->create())
            ->postJson("/api/vendors/{$vendor->id}/reviews", ['rating' => 0])
            ->assertStatus(422);
    }
}

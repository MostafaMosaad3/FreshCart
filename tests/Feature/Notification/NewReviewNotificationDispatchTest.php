<?php

namespace Tests\Feature\Notification;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Models\Vendor;
use App\Notifications\NewReviewNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NewReviewNotificationDispatchTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_notifies_the_vendor_when_a_customer_posts_a_product_review(): void
    {
        Notification::fake();

        $vendor = Vendor::factory()->create();
        $product = Product::factory()->for($vendor)->create();
        $customer = User::factory()->create();

        $review = Review::factory()->forProduct($product)->create(['user_id' => $customer->id]);

        Notification::assertSentTo(
            $vendor->user,
            NewReviewNotification::class,
            fn ($n) => $n->review->id === $review->id
        );
    }

    public function test_it_notifies_the_vendor_when_reviewed_directly(): void
    {
        Notification::fake();

        $vendor = Vendor::factory()->create();
        Review::factory()->forVendor($vendor)->create();

        Notification::assertSentTo($vendor->user, NewReviewNotification::class);
    }

    public function test_it_does_not_notify_the_reviewer_about_their_own_action(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $vendor = Vendor::factory()->create(['user_id' => $user->id]);   // reviewer === owner
        Review::factory()->forVendor($vendor)->create(['user_id' => $user->id]);

        Notification::assertNotSentTo($user, NewReviewNotification::class);
    }
}

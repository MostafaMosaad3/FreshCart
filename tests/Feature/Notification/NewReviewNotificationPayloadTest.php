<?php

namespace Tests\Feature\Notification;

use App\Models\Review;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewReviewNotificationPayloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_writes_a_row_to_the_notifications_table_with_the_right_data(): void
    {
        $vendor = Vendor::factory()->create();
        $review = Review::factory()->forVendor($vendor)->create(['rating' => 5]);

        // Real dispatch — no fake. Database channel is sync, so by now a row exists.
        $this->assertSame(1, $vendor->user->unreadNotifications()->count());

        $notification = $vendor->user->notifications->first();
        $this->assertSame($review->id, $notification->data['review_id']);
        $this->assertSame(5, $notification->data['rating']);
    }
}

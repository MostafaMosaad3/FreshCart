<?php

namespace Tests\Feature\Notification;

use App\Models\Review;
use App\Models\Vendor;
use App\Notifications\NewReviewNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewReviewNotificationChannelsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_sends_on_both_mail_and_database_when_user_opted_in(): void
    {
        $vendor = Vendor::factory()->create();
        $vendor->user->update(['notify_new_review_by_email' => true]);

        $review = Review::factory()->forVendor($vendor)->create();
        $notification = new NewReviewNotification($review);

        $this->assertSame(['database', 'mail'], $notification->via($vendor->user));
    }

    public function test_it_sends_only_on_database_when_mail_opt_out(): void
    {
        $vendor = Vendor::factory()->create();
        $vendor->user->update(['notify_new_review_by_email' => false]);

        $review = Review::factory()->forVendor($vendor)->create();
        $notification = new NewReviewNotification($review);

        $this->assertSame(['database'], $notification->via($vendor->user));
    }
}

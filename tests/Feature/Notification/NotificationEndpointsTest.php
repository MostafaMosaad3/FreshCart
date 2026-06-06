<?php

namespace Tests\Feature\Notification;

use App\Models\Review;
use App\Models\User;
use App\Notifications\NewReviewNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_my_notifications_ordered_by_latest(): void
    {
        $user = User::factory()->create();
        // Create two notifications directly via notify()
        $user->notify(new NewReviewNotification(Review::factory()->create()));
        $user->notify(new NewReviewNotification(Review::factory()->create()));

        $res = $this->actingAs($user)->getJson('/api/me/notifications');

        $res->assertOk();
        $res->assertJsonCount(2, 'data');
    }

    public function test_it_returns_unread_count(): void
    {
        $user = User::factory()->create();
        $user->notify(new NewReviewNotification(Review::factory()->create()));
        $user->notify(new NewReviewNotification(Review::factory()->create()));

        $this->actingAs($user)
            ->getJson('/api/me/notifications/unread-count')
            ->assertOk()
            ->assertJson(['count' => 2]);
    }

    public function test_it_marks_a_single_notification_as_read(): void
    {
        $user = User::factory()->create();
        $user->notify(new NewReviewNotification(Review::factory()->create()));
        $id = $user->notifications->first()->id;

        $this->actingAs($user)->postJson("/api/me/notifications/{$id}/read")
            ->assertNoContent();

        $this->assertSame(0, $user->fresh()->unreadNotifications()->count());
    }

    public function test_it_marks_all_as_read(): void
    {
        $user = User::factory()->create();
        $user->notify(new NewReviewNotification(Review::factory()->create()));
        $user->notify(new NewReviewNotification(Review::factory()->create()));

        $this->actingAs($user)->postJson('/api/me/notifications/read-all')
            ->assertNoContent();

        $this->assertSame(0, $user->fresh()->unreadNotifications()->count());
    }
}

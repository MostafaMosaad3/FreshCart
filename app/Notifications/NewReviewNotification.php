<?php

namespace App\Notifications;

use App\Models\Review;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class NewReviewNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Review $review) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if ($notifiable->notify_new_review_by_email) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function viaQueue(): array
    {
        return ['mail' => 'notifications'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $msg = (new MailMessage)
            ->subject('You received a new review on FreshCart')
            ->greeting("Hi {$notifiable->name}")
            ->line("{$this->review->user->name} left you a {$this->review->rating}-star review.");

        if ($this->review->comment) {
            $msg->line(Str::limit($this->review->comment, 200));
        }

        return $msg
            ->action('View review', url("/vendor/reviews/{$this->review->id}"))
            ->line('Thanks for selling on FreshCart.');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'review_id' => $this->review->id,
            'reviewer' => $this->review->user->name,
            'rating' => $this->review->rating,
            'target_type' => $this->review->reviewable_type,
            'target_id' => $this->review->reviewable_id,
            'target_label' => $this->review->reviewable?->name
                ?? $this->review->reviewable?->store_name,
        ];

    }
}

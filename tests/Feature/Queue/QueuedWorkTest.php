<?php

namespace Tests\Feature\Queue;

use App\Events\Orders\OrderPlaced;
use App\Jobs\Orders\ProcessRefundJob;
use App\Listeners\Orders\SendOrderPlacedEmail;
use App\Mail\Order\OrderPlacedMail;
use App\Models\Order;
use Illuminate\Events\CallQueuedListener;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class QueuedWorkTest extends TestCase
{
    use RefreshDatabase;

    public function test_queues_the_confirmation_email_listener_on_order_placed(): void
    {
        Queue::fake();

        $order = Order::factory()->paid()->create();
        event(new OrderPlaced($order));

        Queue::assertPushed(
            CallQueuedListener::class,
            fn (CallQueuedListener $job) => $job->class === SendOrderPlacedEmail::class
        );
    }

    public function test_sends_the_email_on_successful_handle(): void
    {
        Mail::fake();

        $order = Order::factory()->paid()->create();

        (new SendOrderPlacedEmail)->handle(new OrderPlaced($order));

        Mail::assertSent(
            OrderPlacedMail::class,
            fn ($m) => $m->hasTo($order->user->email)
        );
    }

    public function test_is_idempotent_second_call_does_not_send_again(): void
    {
        Mail::fake();

        $order = Order::factory()->paid()->create();
        $event = new OrderPlaced($order);

        (new SendOrderPlacedEmail)->handle($event);
        (new SendOrderPlacedEmail)->handle($event);

        Mail::assertSentCount(1);
    }

    public function test_dispatches_a_refund_job_when_a_paid_order_is_cancelled(): void
    {
        Queue::fake();

        $order = Order::factory()->paid()->create(['transaction_id' => 'test_123']);

        $order->cancel('customer changed mind');

        Queue::assertPushed(
            ProcessRefundJob::class,
            fn ($j) => $j->order->is($order)
        );
    }

    public function test_does_not_dispatch_a_refund_for_a_pending_cancellation(): void
    {
        Queue::fake();

        $order = Order::factory()->pending()->create();

        $order->cancel('never paid');

        Queue::assertNotPushed(ProcessRefundJob::class);
    }
}

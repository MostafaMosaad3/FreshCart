<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CancelOrderRequest;
use App\Http\Requests\Admin\MarkOrderShippedRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;

class OrderStatusController extends Controller
{
    public function markShipped(MarkOrderShippedRequest $request, Order $order)
    {
        $this->authorize('transition', $order);

        $order->markAsShipped($request->validated('tracking_number'));

        return new OrderResource($order->fresh());
    }

    public function markDelivered(Order $order): OrderResource
    {
        $this->authorize('transition', $order);

        $order->markAsDelivered();

        return new OrderResource($order->fresh());
    }

    public function cancel(CancelOrderRequest $request, Order $order): OrderResource
    {
        $this->authorize('transition', $order);

        $order->cancel($request->validated('reason'));

        return new OrderResource($order->fresh());
    }
}

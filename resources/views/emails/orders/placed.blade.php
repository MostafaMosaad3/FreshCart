<x-mail::message>
    # Thank you for your order, {{ $order->user->name }}!

    Your order **#{{ $order->order_number }}** has been confirmed.

    ## Items
    <x-mail::table>
        | Product | Qty | Unit | Line Total |
        |---------|-----|------|-----------|
        @foreach ($items as $item)
            | {{ $item->variant->product->name }} ({{ $item->variant->name }}) | {{ $item->quantity }} | {{ number_format($item->unit_price, 2) }} EGP | {{ number_format($item->line_total, 2) }} EGP |
        @endforeach
    </x-mail::table>

    **Subtotal:** {{ number_format($order->subtotal, 2) }} EGP
    **Tax:** {{ number_format($order->tax, 2) }} EGP
    **Shipping:** {{ number_format($order->shipping, 2) }} EGP
    **Discount:** -{{ number_format($order->discount, 2) }} EGP

    **Total: {{ number_format($order->total, 2) }} EGP**

    <x-mail::button :url="route('orders.show', $order)">
        View Your Order
    </x-mail::button>

    Thanks,<br>
    FreshCart
</x-mail::message>

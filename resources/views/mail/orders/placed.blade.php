<x-mail::message>
# Thanks for your order, {{ $firstName }}

We've received order **{{ $order->order_number }}**. We'll email you again when it's being prepared and when it's on its way.

@include('mail.orders.summary')

**Payment:** {{ $order->payment_method_label }}<br>
**Delivery to:** {{ $order->shipping_address }}<br>
**Phone:** {{ $order->customer_phone }}

@if($orderUrl)
<x-mail::button :url="$orderUrl">
View your order
</x-mail::button>
@endif

Questions about your order? Just reply to this email.

Thanks,<br>
{{ config('shop.name') }}
</x-mail::message>

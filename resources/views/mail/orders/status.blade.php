<x-mail::message>
# {{ $heading }}

Hi {{ $firstName }}, {{ $line }}

<x-mail::panel>
Order **{{ $order->order_number }}** · {{ $order->status_label }}
</x-mail::panel>

@if($payOnDelivery)
You chose cash on delivery, so please have **{{ $payOnDelivery }}** ready when it arrives.

@endif
@include('mail.orders.summary')

@if($orderUrl)
<x-mail::button :url="$orderUrl">
View your order
</x-mail::button>
@endif

Thanks,<br>
{{ config('shop.name') }}
</x-mail::message>

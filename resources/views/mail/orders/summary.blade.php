{{-- The order's items and totals, shared by the order emails (markdown: keep it unindented) --}}
<x-mail::table>
| Item | Qty | Price |
|:-----|:---:|------:|
@foreach($order->orderItems as $item)
| {{ str_replace('|', '/', $item->product_name) }} | {{ $item->quantity }} | {{ App\Support\Money::format($item->total) }} |
@endforeach
| Subtotal | | {{ App\Support\Money::format($order->subtotal) }} |
| Delivery | | {{ App\Support\Money::format($order->shipping_cost) }} |
| **Total** | | **{{ App\Support\Money::format($order->total) }}** |
</x-mail::table>

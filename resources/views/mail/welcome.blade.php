<x-mail::message>
# Welcome to {{ config('shop.name') }}, {{ $firstName }}

Your account is ready. With it you can:

- follow each order from confirmation to delivery, and reorder in one click
- save products to your wishlist for later
- check out faster, with your details filled in

<x-mail::button :url="route('shop')">
Start shopping
</x-mail::button>

Thanks,<br>
{{ config('shop.name') }}
</x-mail::message>

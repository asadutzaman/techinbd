<x-mail::message>
# Email is working

This test was sent by {{ $sentBy }} from the {{ config('shop.name') }} admin panel. Order confirmations, status updates, welcome emails and password resets go out the same way.

Thanks,<br>
{{ config('shop.name') }}
</x-mail::message>

@props(['amount', 'currency' => 'BDT'])
@php
    // Whole taka prints without decimals (৳164,999); other currencies keep their code (EUR 175)
    $isTaka = strtoupper($currency ?: 'BDT') === 'BDT';
@endphp
<span {{ $attributes->merge(['class' => 'price']) }}>@if($isTaka)<span class="price-sign" aria-hidden="true">৳</span><span class="sr-only">Tk </span>@else<span class="price-sign">{{ $currency }}</span> @endif{{ App\Support\Money::amount($amount) }}</span>

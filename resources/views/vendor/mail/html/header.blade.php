@props(['url'])
@php
    // The site's two-tone wordmark (MULTI + SHOP) instead of the app name in plain text
    $logoParts = preg_split('/(?<=[a-z])(?=[A-Z])|\s+/', trim(config('shop.name')), 2);
@endphp
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block; font-size: 22px; font-weight: 800; letter-spacing: 0.02em; text-transform: uppercase; color: #111827; text-decoration: none;">{{ $logoParts[0] }}<span style="color: #2563EB;">{{ $logoParts[1] ?? '' }}</span></a>
</td>
</tr>

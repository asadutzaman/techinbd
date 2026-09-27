<?php

namespace App\Support;

/**
 * Prices as the storefront shows them: whole taka without decimals (৳164,999), anything else with two.
 * The <x-price> component uses amount(); format() is the plain-text version, for emails.
 */
final class Money
{
    public static function amount(float|int|string|null $amount): string
    {
        $amount = (float) $amount;

        return number_format($amount, fmod($amount, 1.0) == 0.0 ? 0 : 2);
    }

    public static function format(float|int|string|null $amount, ?string $currency = 'BDT'): string
    {
        $currency = strtoupper($currency ?: 'BDT');

        return ($currency === 'BDT' ? '৳' : $currency . ' ') . self::amount($amount);
    }
}

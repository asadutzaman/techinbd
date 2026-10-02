<?php

/*
|--------------------------------------------------------------------------
| Storefront settings
|--------------------------------------------------------------------------
|
| Shown on the home page and in the footer. Contact and social lines only
| render when a value is set, so nothing placeholder-looking reaches customers.
|
*/

return [

    'name' => env('SHOP_NAME', 'MultiShop'),

    // Home page title: "MultiShop — Laptops, monitors and gadgets in Bangladesh"
    'tagline' => 'Laptops, monitors and gadgets in Bangladesh',

    'description' => 'Genuine laptops, monitors, gadgets, printers and accessories, priced in taka and delivered across Bangladesh.',

    // Dates shown to shoppers (the app itself stores UTC)
    'timezone' => env('SHOP_TIMEZONE', 'Asia/Dhaka'),

    // Only list promises the store actually keeps
    'promises' => [
        'Cash on delivery',
        'Delivery across Bangladesh',
        'Official brand warranty',
        '7-day exchange',
    ],

    // Matches the options offered at checkout (CheckoutController / Order::PAYMENT_METHODS)
    'payment_methods' => ['Cash on delivery', 'Bank transfer', 'PayPal'],

    'contact' => [
        'email' => env('SHOP_EMAIL'),
        'phone' => env('SHOP_PHONE'),
        'address' => env('SHOP_ADDRESS'),
    ],

    'social' => [
        'facebook' => env('SHOP_FACEBOOK_URL'),
        'instagram' => env('SHOP_INSTAGRAM_URL'),
        'youtube' => env('SHOP_YOUTUBE_URL'),
    ],

];

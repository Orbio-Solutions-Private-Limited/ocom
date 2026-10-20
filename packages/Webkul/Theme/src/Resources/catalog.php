<?php

/*
|--------------------------------------------------------------------------
| Shop Theme Catalog
|--------------------------------------------------------------------------
|
| Themes offered for the storefront, mirroring https://www.orbiosolutions.com/.
|
| An entry whose `code` matches a theme in `config/themes.php` is treated as installed.
| `screenshot` is a remote url, or a path relative to the admin asset directory.
|
*/

$screenshots = 'https://www.orbiosolutions.com';

return [
    [
        'code' => 'default',
        'name' => 'Default',
        'author' => 'Orbio Solutions',
        'version' => '2.4',
        'url' => null,
        'demo_url' => 'https://www.orbiosolutions.com/',
        'screenshot' => 'images/themes/default.jpg',
        'rating' => null,
        'tags' => ['free', 'starter'],
        'description' => 'The default theme included with the platform. A clean, responsive storefront that works out of the box for any catalog.',
    ], [
        'code' => 'fashion',
        'name' => 'Ethereal Fashion',
        'author' => null,
        'version' => null,
        'url' => 'https://www.orbiosolutions.com/bagisto-fashion-commerce.html',
        'demo_url' => 'https://www.orbiosolutions.com/ethereal-fashion/',
        'screenshot' => $screenshots.'/fashion-theme.png',
        'rating' => null,
        'tags' => ['premium', 'fashion'],
        'description' => 'Elegant fashion theme designed for modern clothing brands and boutiques.',
    ], [
        'code' => 'breeze',
        'name' => 'Vape Commerce',
        'author' => null,
        'version' => null,
        'url' => 'https://www.orbiosolutions.com/vape-commerce.html',
        'demo_url' => 'https://www.orbiosolutions.com/breeze-vape/',
        'screenshot' => $screenshots.'/vape-commerce-theme.png',
        'rating' => null,
        'tags' => ['premium', 'vape'],
        'description' => 'Dedicated Vape Commerce solution, alongside custom theme-building resources and a native blog module.',
    ], [
        'code' => 'commercia',
        'name' => 'Quick Commerce',
        'author' => null,
        'version' => null,
        'url' => 'https://www.orbiosolutions.com/bagisto-quick-commerce.html',
        'demo_url' => 'https://www.orbiosolutions.com/commercia-retail/',
        'screenshot' => $screenshots.'/quick-commerce-theme.png',
        'rating' => null,
        'tags' => ['premium', 'quick-commerce'],
        'description' => 'Build lightning-fast online stores for quick commerce businesses effortlessly.',
    ], [
        'code' => 'adornments',
        'name' => 'Jewellery Commerce',
        'author' => null,
        'version' => null,
        'url' => 'https://www.orbiosolutions.com/jewellery-commerce.html',
        'demo_url' => 'https://www.orbiosolutions.com/adornments-jewellery/',
        'screenshot' => $screenshots.'/dornments-theme.png',
        'rating' => null,
        'tags' => ['premium', 'jewellery'],
        'description' => 'Elegant jewelry theme crafted for premium online stores and brands.',
    ], [
        'code' => 'perfume',
        'name' => 'Velora Perfume',
        'author' => null,
        'version' => null,
        'url' => 'https://www.orbiosolutions.com/bagisto-perfume-theme.html',
        'demo_url' => 'https://www.orbiosolutions.com/perfume-theme/',
        'screenshot' => $screenshots.'/perfume-commerce-theme.png',
        'rating' => null,
        'tags' => ['premium', 'perfume'],
        'description' => 'Velora perfume theme, built for fragrance catalogs and beauty storefronts.',
    ], [
        'code' => 'electronic',
        'name' => 'Electronic Theme',
        'author' => null,
        'version' => null,
        'url' => 'https://www.orbiosolutions.com/bagisto-laravel-electronic-theme.html',
        'demo_url' => 'https://www.orbiosolutions.com/electronic-theme/',
        'screenshot' => $screenshots.'/electronic-commerce-theme.png',
        'rating' => null,
        'tags' => ['premium', 'electronics'],
        'description' => 'Modern electronics theme designed for high-converting online stores.',
    ], [
        'code' => 'elvix',
        'name' => 'Elvix',
        'author' => null,
        'version' => null,
        'url' => null,
        'demo_url' => 'https://www.orbiosolutions.com/elvix-theme/',
        'screenshot' => $screenshots.'/elvix-theme.png',
        'rating' => null,
        'tags' => ['electronics'],
        'description' => 'Elvix theme.',
    ], [
        'code' => 'autrivo',
        'name' => 'Autrivo Automobile',
        'author' => null,
        'version' => null,
        'url' => null,
        'demo_url' => 'https://www.orbiosolutions.com/automobile-theme/',
        'screenshot' => $screenshots.'/autrivo-theme.png',
        'rating' => null,
        'tags' => ['automobile'],
        'description' => 'Autrivo automobile theme, built for vehicle and parts catalogs.',
    ], [
        'code' => 'waggin',
        'name' => 'Pet Theme',
        'author' => null,
        'version' => null,
        'url' => null,
        'demo_url' => 'https://www.orbiosolutions.com/common/waggin/',
        'screenshot' => $screenshots.'/pet-theme.png',
        'rating' => null,
        'tags' => ['premium', 'pet'],
        'description' => 'Dedicated pet food commerce solution with custom theme options and a native blog module designed for pet stores and brands.',
    ],
];

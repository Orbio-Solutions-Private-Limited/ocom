<?php

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Webkul\Theme\Theme;

// ============================================================================
// Asset Fallback
// ============================================================================

it('should fall back to the default shop theme when the active theme is missing an asset', function () {
    $theme = new Theme(
        'visual-debut',
        'Visual Debut',
        'public/themes/shop/visual-debut',
        'resources/themes/visual-debut/views',
        null,
        [
            'hot_file' => 'themes/shop/visual-debut/visual-debut-vite.hot',
            'build_directory' => 'themes/shop/visual-debut',
            'package_assets_directory' => 'resources/assets',
        ],
    );

    expect($theme->url('images/user-placeholder.png'))
        ->toContain('user-placeholder');
});

it('should abort when neither the active theme nor the default theme has the asset', function () {
    $theme = new Theme(
        'visual-debut',
        'Visual Debut',
        'public/themes/shop/visual-debut',
        'resources/themes/visual-debut/views',
        null,
        [
            'hot_file' => 'themes/shop/visual-debut/visual-debut-vite.hot',
            'build_directory' => 'themes/shop/visual-debut',
            'package_assets_directory' => 'resources/assets',
        ],
    );

    $theme->url('images/missing-theme-asset.png');
})->throws(NotFoundHttpException::class);

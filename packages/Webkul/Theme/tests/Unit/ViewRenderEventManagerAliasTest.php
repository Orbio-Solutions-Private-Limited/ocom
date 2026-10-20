<?php

use Webkul\Core\View\ViewRenderEventManager as CoreViewRenderEventManager;
use Webkul\Theme\ViewRenderEventManager as ThemeViewRenderEventManager;

// ============================================================================
// Backward Compatible Alias
// ============================================================================

it('should treat the theme view render event manager as the core class', function () {
    expect(class_exists(ThemeViewRenderEventManager::class))->toBeTrue()
        ->and(is_a(ThemeViewRenderEventManager::class, CoreViewRenderEventManager::class, true))->toBeTrue();
});

it('should accept the core view render event manager where the theme type hint remains', function () {
    $listener = static function (ThemeViewRenderEventManager $viewRenderEventManager): ThemeViewRenderEventManager {
        return $viewRenderEventManager;
    };

    expect($listener(app(CoreViewRenderEventManager::class)))
        ->toBeInstanceOf(CoreViewRenderEventManager::class)
        ->toBeInstanceOf(ThemeViewRenderEventManager::class);
});

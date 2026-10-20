<?php

use Webkul\Core\View\ViewRenderEventManager;

if (! class_exists('Webkul\Theme\ViewRenderEventManager', false)) {
    class_alias(
        ViewRenderEventManager::class,
        'Webkul\Theme\ViewRenderEventManager',
    );
}

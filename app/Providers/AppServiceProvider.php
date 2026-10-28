<?php

namespace App\Providers;

use BagistoPlus\Visual\Events\ThemeActivated;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\ParallelTesting;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $allowedIPs = array_map('trim', explode(',', config('app.debug_allowed_ips', '')));

        $allowedIPs = array_filter($allowedIPs);

        if (empty($allowedIPs)) {
            return;
        }

        if (in_array(Request::ip(), $allowedIPs)) {
            Debugbar::enable();
        } else {
            Debugbar::disable();
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ParallelTesting::setUpTestDatabase(function (string $database, int $token) {
            Artisan::call('db:seed');
        });

        $this->brandVisualTheme();

        $this->renameVisualEditorMenu();

        $this->nestVisualEditorUnderAppearance();

        $this->preferShopPackageViewsOverVisualDebut();
    }

    /**
     * Visual Debut prepends its views onto the shop namespace when the theme
     * activates, so a Shop package override would otherwise never win.
     */
    protected function preferShopPackageViewsOverVisualDebut(): void
    {
        $path = base_path('packages/Webkul/Shop/src/Resources/views/visual-overrides');

        View::prependNamespace('shop', $path);

        Event::listen(ThemeActivated::class, function () use ($path) {
            View::prependNamespace('shop', $path);
        });
    }

    /**
     * Present Visual Debut as Custom Theme by Orbio Solutions.
     */
    protected function brandVisualTheme(): void
    {
        if (! is_array(config('themes.shop.visual-debut'))) {
            return;
        }

        config([
            'themes.shop.visual-debut.name' => 'Custom Theme',
            'themes.shop.visual-debut.author' => 'Orbio Solutions',
        ]);
    }

    /**
     * Label the Visual admin menu and ACL as Theme Editor.
     */
    protected function renameVisualEditorMenu(): void
    {
        $lines = [
            'admin.sidebar.visual' => 'Theme Editor',
            'admin.acl.visual' => 'Theme Editor',
        ];

        $translator = app('translator');

        foreach (['ar', 'en', 'es', 'fr', 'hi_IN'] as $locale) {
            $translator->load('visual', 'admin', $locale);

            Lang::addLines($lines, $locale, 'visual');
        }
    }

    /**
     * Place Theme Editor under Appearance and drop Visual's top-level menu.
     */
    protected function nestVisualEditorUnderAppearance(): void
    {
        $this->nestVisualEditorMenu();

        $this->nestVisualEditorAcl();
    }

    /**
     * Replace Visual's sidebar entries with a single Appearance child.
     */
    protected function nestVisualEditorMenu(): void
    {
        $items = collect(config('menu.admin', []))
            ->reject(fn ($item) => in_array($item['key'] ?? null, ['bagisto_visual', 'bagisto_visual.themes'], true))
            ->values();

        if ($items->contains(fn ($item) => ($item['key'] ?? null) === 'appearance.theme_editor')) {
            config(['menu.admin' => $items->all()]);

            return;
        }

        config(['menu.admin' => $items->push([
            'key' => 'appearance.theme_editor',
            'name' => 'visual::admin.sidebar.visual',
            'route' => 'visual.admin.themes.index',
            'sort' => 2,
            'icon' => '',
        ])->all()]);
    }

    /**
     * Nest Visual ACL under Appearance so the sidebar key can be granted.
     */
    protected function nestVisualEditorAcl(): void
    {
        $items = collect(config('acl', []))
            ->reject(fn ($item) => in_array($item['key'] ?? null, ['bagisto_visual', 'bagisto_visual.themes'], true))
            ->map(function ($item) {
                if (($item['key'] ?? null) !== 'bagisto_visual.editor') {
                    return $item;
                }

                $item['key'] = 'appearance.theme_editor.editor';

                return $item;
            })
            ->values();

        if (! $items->contains(fn ($item) => ($item['key'] ?? null) === 'appearance.theme_editor')) {
            $items->push([
                'key' => 'appearance.theme_editor',
                'name' => 'visual::admin.acl.visual',
                'route' => 'visual.admin.themes.index',
                'sort' => 3,
            ]);
        }

        config(['acl' => $items->all()]);
    }
}

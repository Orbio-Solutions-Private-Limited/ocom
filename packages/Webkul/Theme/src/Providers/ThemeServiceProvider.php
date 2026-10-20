<?php

namespace Webkul\Theme\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Webkul\Core\View\ViewRenderEventManager as CoreViewRenderEventManager;
use Webkul\Theme\Models\SectionProxy;
use Webkul\Theme\Observers\SectionObserver;
use Webkul\Theme\ThemeViewFinder;
use Webkul\Theme\ViewRenderEventManager as ThemeViewRenderEventManager;

class ThemeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        include __DIR__.'/../Http/helpers.php';

        if (! class_exists(ThemeViewRenderEventManager::class, false)) {
            class_alias(CoreViewRenderEventManager::class, ThemeViewRenderEventManager::class);
        }

        $this->app->singleton('view.finder', function ($app) {
            return new ThemeViewFinder(
                $app['files'],
                $app['config']['view.paths'],
                null
            );
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        SectionProxy::observe(SectionObserver::class);

        Blade::directive('bagistoVite', function ($expression) {
            return "<?php echo themes()->setBagistoVite({$expression})->toHtml(); ?>";
        });
    }
}

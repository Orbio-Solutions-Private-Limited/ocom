<?php

namespace Webkul\Paypal\Providers;

use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\ServiceProvider;

class PaypalServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerConfig();
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/../Http/routes.php');

        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'paypal');

        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'paypal');

        $this->app->register(EventServiceProvider::class);

        $this->aliasLegacySmartButtonRouteNames();
    }

    /**
     * Register package config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->mergeConfigFrom(
            dirname(__DIR__).'/Config/payment-methods.php', 'payment_methods'
        );
    }

    /**
     * Map kebab-case Smart Button names still used by third-party checkout views.
     */
    protected function aliasLegacySmartButtonRouteNames(): void
    {
        $register = function (UrlGenerator $url): void {
            $url->resolveMissingNamedRoutesUsing(function (string $name, array $parameters, bool $absolute) use ($url): ?string {
                $legacy = [
                    'paypal.smart-button.create-order' => 'paypal.smart_button.create_order',
                    'paypal.smart-button.capture-order' => 'paypal.smart_button.capture_order',
                ];

                if (! isset($legacy[$name])) {
                    return null;
                }

                return $url->route($legacy[$name], $parameters, $absolute);
            });
        };

        $this->app->afterResolving('url', $register);

        $this->app->rebinding('url', function ($app, $url) use ($register) {
            $register($url);
        });
    }
}

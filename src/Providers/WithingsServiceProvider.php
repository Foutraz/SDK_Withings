<?php

namespace Foutraz\Withings\Providers;

use Foutraz\Withings\WithingsManager;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

class WithingsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            dirname(__DIR__, 2).'/config/withings.php',
            'withings'
        );

        $this->app->singleton(WithingsManager::class, function ($app) {

            $config = $app['config']['withings'];

            if (blank($config['endpoint'])) {
                throw new RuntimeException(
                    'No Withings API endpoint was provided.'
                );
            }

            return new WithingsManager(
                $config['endpoint'],
                $config['token'],
                $config['client_id'],
                $config['client_secret'],
                $config['redirect_uri'],
            );
        });

        $this->app->alias(WithingsManager::class, 'withings');
    }

    public function boot(): void
    {
        $this->publishes([
            dirname(__DIR__, 2).'/config/withings.php' =>
                $this->app->configPath('withings.php'),
        ], 'withings-config');
    }
}

<?php

namespace Denason\Wikimind;
use Denason\Wikimind\Fluents\MindQuery;
use Denason\Wikimind\Interfaces\MindQueryInterface;
use Denason\Wikimind\Services\WikimindManager;

use Illuminate\Support\ServiceProvider;

class WikimindServiceProvider extends ServiceProvider
{
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton(WikimindInterface::class, function ($app) {
            return new WikimindManager();
        });

        $this->app->singleton(MindQueryInterface::class, function ($app) {
            return new MindQuery();
        });

        $this->mergeConfigFrom(__DIR__ . '/Config/wikimind.php', 'wikimind');
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/Config/wikimind.php' => config_path('wikimind.php'),
        ], 'wikimind-config');

        if (file_exists(__DIR__ . '/Helpers.php')) {
            require_once __DIR__ . '/Helpers.php';
        }

        if (file_exists(__DIR__ . '/Routes/web.php')) {
            $this->loadRoutesFrom(__DIR__ . '/Routes/web.php');
        }
    }
}

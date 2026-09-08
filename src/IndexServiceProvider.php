<?php

declare(strict_types=1);

namespace Onelegstudios\Index;

use Illuminate\Support\ServiceProvider;
use Onelegstudios\Index\Console\Commands\IndexCommand;

class IndexServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/index.php', 'index');

        $this->app->singleton(Index::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/index.php');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'index');

        $this->loadTranslationsFrom(__DIR__.'/../lang', 'index');

        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/index.php' => config_path('index.php'),
        ], ['index', 'index-config']);

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/index'),
        ], ['index', 'index-views']);

        $this->publishes([
            __DIR__.'/../lang' => $this->app->langPath('vendor/index'),
        ], ['index', 'index-lang']);

        $this->publishes([
            __DIR__.'/../public' => public_path('vendor/index'),
        ], ['index', 'index-assets']);

        $this->publishesMigrations([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], ['index', 'index-migrations']);

        $this->commands([
            IndexCommand::class,
        ]);
    }
}

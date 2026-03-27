<?php

declare(strict_types=1);

namespace LumenSistemas\Lgpd;

use Illuminate\Support\ServiceProvider;

class LgpdServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/lgpd.php', 'lgpd');

        $this->publishes([
            __DIR__.'/../config/lgpd.php' => config_path('lgpd.php'),
        ], 'lgpd-config');

        $this->loadTranslationsFrom(__DIR__.'/../lang', 'lgpd');

        $this->publishes([
            __DIR__.'/../lang' => lang_path('vendor/lgpd'),
        ], 'lgpd-lang');

        $this->publishesMigrations([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'lgpd-migrations');
    }
}

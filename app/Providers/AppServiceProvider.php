<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Squake;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(Squake::class, function ($app) {
            return new Squake();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}

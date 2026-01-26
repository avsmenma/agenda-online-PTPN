<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\WelcomeMessageService;
use App\View\Composers\WelcomeMessageComposer;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(WelcomeMessageService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS on production to prevent redirect loops
        if ($this->app->environment('production')) {
            \URL::forceScheme('https');
        }

        // Register welcome message composer for all views
        View::composer('*', WelcomeMessageComposer::class);
    }
}






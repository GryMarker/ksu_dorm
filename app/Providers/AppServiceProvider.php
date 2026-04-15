<?php

namespace App\Providers;

use App\Mail\BrevoApiTransport;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Mail::extend('brevo', function (array $config) {
            return new BrevoApiTransport(
                apiKey: (string) ($config['key'] ?? ''),
                timeout: (int) ($config['timeout'] ?? 10),
            );
        });

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}

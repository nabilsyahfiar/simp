<?php

namespace App\Providers;

use App\Http\Responses\FilamentLoginResponse;
use Filament\Auth\Http\Responses\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(LoginResponseContract::class, FilamentLoginResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $forceHttps = (bool) config('simpro.force_https', false);

        if ($forceHttps) {
            URL::forceScheme('https');
            URL::forceRootUrl(config('app.url'));
        }
    }
}

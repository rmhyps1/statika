<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Http;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        if (str_contains(Request::header('X-Forwarded-Proto', ''), 'https') || 
            str_contains(Request::header('Front-End-Https', ''), 'on') || 
            Request::secure()) {
            URL::forceScheme('https');
        }

        Http::macro('kamasuta', function () {
            $baseUrl = config('services.kamasuta.url', 'https://kamasuta.malangkab.go.id');
            $token = config('services.kamasuta.token');

            return Http::baseUrl($baseUrl)
                ->acceptJson()
                ->withToken($token)
                ->timeout(15)
                ->withOptions(['verify' => env('APP_ENV') === 'production']);
        });
    }
}

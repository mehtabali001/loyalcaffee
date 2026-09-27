<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);
        JsonResource::withoutWrapping();
        Model::preventLazyLoading(! $this->app->isProduction());

        // Public form submissions: generous for real guests, tight for bots
        RateLimiter::for('forms', fn (Request $r) => [
            Limit::perMinute(5)->by($r->ip()),
            Limit::perDay(40)->by($r->ip()),
        ]);
        RateLimiter::for('login', fn (Request $r) => Limit::perMinute(6)->by(strtolower((string) $r->input('email')).'|'.$r->ip()));
    }
}

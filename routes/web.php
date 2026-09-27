<?php

use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SpaController;
use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', [SitemapController::class, 'sitemap']);
Route::get('/robots.txt', [SitemapController::class, 'robots']);

// Admin SPA (all client-side routes)
Route::get('/admin/{any?}', [SpaController::class, 'admin'])->where('any', '.*');

Route::get('/', [SpaController::class, 'root']);

$locales = implode('|', config('loyal.locales'));
Route::get('/{locale}/{page?}', [SpaController::class, 'site'])
    ->where(['locale' => $locales, 'page' => '[a-z0-9\-]+'])
    ->name('site');

// Anything else: send to the default-locale 404 inside the SPA shell
Route::fallback(fn () => app(SpaController::class)->site(request(), config('loyal.locales')[0], 'not-found'));

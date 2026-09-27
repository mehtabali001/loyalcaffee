<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Api\FormController;
use App\Http\Controllers\Api\SiteController;
use Illuminate\Support\Facades\Route;

/*
| Public API — locale via X-Locale header (en|ar)
*/
Route::prefix('v1')->group(function () {
    Route::get('bootstrap', [SiteController::class, 'bootstrap']);
    Route::get('home', [SiteController::class, 'home']);
    Route::get('menu', [SiteController::class, 'menu']);
    Route::get('gallery', [SiteController::class, 'gallery']);
    Route::get('events', [SiteController::class, 'events']);
    Route::get('pages/{page}', [SiteController::class, 'page'])->where('page', '[a-z0-9\-]+');

    Route::middleware('throttle:forms')->group(function () {
        Route::post('reservations', [FormController::class, 'reserve']);
        Route::post('contact', [FormController::class, 'contact']);
    });
});

/*
| Admin API — Sanctum bearer tokens
*/
Route::prefix('admin')->group(function () {
    Route::post('login', [Admin\AuthController::class, 'login'])->middleware('throttle:login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [Admin\AuthController::class, 'me']);
        Route::put('me', [Admin\AuthController::class, 'updateProfile']);
        Route::post('logout', [Admin\AuthController::class, 'logout']);

        Route::get('dashboard', Admin\DashboardController::class);
        Route::post('uploads', Admin\UploadController::class);

        $crud = [
            'menu-categories' => Admin\MenuCategoryController::class,
            'menu-items' => Admin\MenuItemController::class,
            'gallery' => Admin\GalleryController::class,
            'events' => Admin\EventController::class,
            'offers' => Admin\OfferController::class,
            'testimonials' => Admin\TestimonialController::class,
            'sections' => Admin\SectionController::class,
        ];
        foreach ($crud as $uri => $controller) {
            Route::patch("$uri/reorder", [$controller, 'reorder']);
            Route::patch("$uri/{id}/toggle", [$controller, 'toggle'])->whereNumber('id');
            Route::get($uri, [$controller, 'index']);
            Route::post($uri, [$controller, 'store']);
            Route::get("$uri/{id}", [$controller, 'show'])->whereNumber('id');
            Route::put("$uri/{id}", [$controller, 'update'])->whereNumber('id');
            Route::delete("$uri/{id}", [$controller, 'destroy'])->whereNumber('id');
        }
        Route::post('gallery/bulk', [Admin\GalleryController::class, 'bulk']);

        Route::get('reservations/export', [Admin\ReservationController::class, 'export']);
        Route::apiResource('reservations', Admin\ReservationController::class)->only(['index', 'update', 'destroy']);

        Route::get('messages', [Admin\MessageController::class, 'index']);
        Route::patch('messages/{message}/read', [Admin\MessageController::class, 'markRead']);
        Route::delete('messages/{message}', [Admin\MessageController::class, 'destroy']);

        Route::get('settings', [Admin\SettingController::class, 'index']);
        Route::put('settings', [Admin\SettingController::class, 'update']);

        Route::get('translations', [Admin\TranslationController::class, 'index']);
        Route::put('translations', [Admin\TranslationController::class, 'update']);

        Route::apiResource('users', Admin\UserController::class)->except('show');
    });
});

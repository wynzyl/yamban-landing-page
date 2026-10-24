<?php

use App\Http\Controllers\DesignPreviewController;
use App\Http\Controllers\LandingPageController;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;

Route::controller(LandingPageController::class)->group(function () {
    Route::get('/', 'home')->name('home');
    Route::get('about', 'about')->name('about');
    Route::get('products', 'products')->name('products');
    Route::get('designs', 'designs')->name('designs');
    Route::get('contact', 'contact')->name('contact');
});

Route::get('designs/{design}/preview', DesignPreviewController::class)
    ->withoutMiddleware([
        EncryptCookies::class,
        AddQueuedCookiesToResponse::class,
        StartSession::class,
        ShareErrorsFromSession::class,
        ValidateCsrfToken::class,
        PreventRequestForgery::class,
    ])
    ->name('designs.preview');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::redirect('/', 'categories');
    Route::livewire('categories', 'pages::admin.categories')->name('categories');
    Route::livewire('products', 'pages::admin.products')->name('products');
    Route::livewire('designs', 'pages::admin.designs')->name('designs');
});

require __DIR__.'/settings.php';

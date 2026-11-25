<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\HomeSlideController;
use App\Http\Controllers\Admin\InboxController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\NoCache;
use Illuminate\Support\Facades\Route;

Route::middleware(NoCache::class)->group(function (): void {
    Route::get('/admin/login', [AuthController::class, 'login'])->name('login');
    Route::post('/admin/login', [AuthController::class, 'authenticate'])->middleware('throttle:10,1')->name('admin.login.store');
    Route::get('/admin/forgot-password', [AuthController::class, 'forgot'])->name('password.request');
    Route::post('/admin/forgot-password', [AuthController::class, 'emailReset'])->middleware('throttle:3,1')->name('password.email');
    Route::get('/admin/reset-password/{token}', [AuthController::class, 'reset'])->name('password.reset');
    Route::post('/admin/reset-password', [AuthController::class, 'updatePassword'])->middleware('throttle:5,1')->name('password.update');

    Route::prefix('admin')->name('admin.')->middleware(['auth', 'auth.session', EnsureAdmin::class])->group(function (): void {
        Route::get('/', DashboardController::class)->name('dashboard');
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
        Route::resource('slides', HomeSlideController::class)
            ->parameters(['slides' => 'homeSlide'])
            ->except('show');
        Route::prefix('inbox/{kind}')->where(['kind' => 'appointments|inquiries'])->group(function (): void {
            Route::get('/', [InboxController::class, 'index'])->name('inbox.index');
            Route::get('/{record}', [InboxController::class, 'show'])->whereNumber('record')->name('inbox.show');
            Route::put('/{record}', [InboxController::class, 'update'])->whereNumber('record')->name('inbox.update');
            Route::post('/{record}/retry', [InboxController::class, 'retry'])->whereNumber('record')->middleware('throttle:3,1')->name('inbox.retry');
        });
        Route::prefix('content/{section}')->where(['section' => 'services|credentials|chambers|categories|articles|media|gallery|testimonials'])->group(function (): void {
            Route::get('/', [ContentController::class, 'index'])->name('content.index');
            Route::get('/create', [ContentController::class, 'create'])->name('content.create');
            Route::post('/', [ContentController::class, 'store'])->name('content.store');
            Route::get('/{record}/edit', [ContentController::class, 'edit'])->whereNumber('record')->name('content.edit');
            Route::get('/{record}/preview/{locale}', [ContentController::class, 'preview'])->whereNumber('record')->where('locale', 'bn|en')->name('content.preview');
            Route::put('/{record}', [ContentController::class, 'update'])->whereNumber('record')->name('content.update');
            Route::delete('/{record}', [ContentController::class, 'destroy'])->whereNumber('record')->name('content.destroy');
        });
    });
});

<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\PublicCardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

// Utility route for creating storage link (use only when needed, then comment out)
Route::get('/storage-link', function () {
    try {
        Artisan::call('storage:link');
        return response()->json([
            'success' => true,
            'message' => 'Storage link created successfully!',
            'output' => Artisan::output()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error creating storage link',
            'error' => $e->getMessage()
        ], 500);
    }
});

Route::get('/p/{slug}', [PublicCardController::class, 'show'])->name('public.cards.show');
Route::get('/p/{slug}/out/{index}', [PublicCardController::class, 'out'])
    ->whereNumber('index')
    ->name('public.cards.out');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);

    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);

    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');

    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/cards', [CardController::class, 'index'])->name('cards.index');
    Route::post('/cards', [CardController::class, 'store'])->name('cards.store');
    Route::get('/cards/{card}', [CardController::class, 'show'])->name('cards.show');
    Route::put('/cards/{card}', [CardController::class, 'update'])->name('cards.update');
    Route::delete('/cards/{card}', [CardController::class, 'destroy'])->name('cards.destroy');

    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    Route::get('/appointments', [AppointmentController::class, 'index'])->name('appointments.index');
    Route::post('/appointments', [AppointmentController::class, 'store'])->name('appointments.store');
    Route::put('/appointments/{appointment}', [AppointmentController::class, 'update'])->name('appointments.update');
    Route::delete('/appointments/{appointment}', [AppointmentController::class, 'destroy'])->name('appointments.destroy');
    Route::put('/appointments/{appointment}/status', [AppointmentController::class, 'updateStatus'])->name('appointments.status.update');

    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/settings', [ModuleController::class, 'settings'])->name('settings');
    Route::get('/analytics', [ModuleController::class, 'analytics'])->name('analytics');
    Route::get('/reports', [ModuleController::class, 'reports'])->name('reports');

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

Route::middleware(['auth', 'admin'])->group(function (): void {
    Route::get('/users', [ModuleController::class, 'users'])->name('users');
});

Route::post('/locale', LocaleController::class)->name('locale.switch');

Route::get('/{username}/appointments/availability', [AppointmentController::class, 'availability'])
    ->where('username', '[A-Za-z0-9_-]+')
    ->name('public.cards.appointments.availability');

Route::post('/{username}/appointments', [AppointmentController::class, 'storePublic'])
    ->where('username', '[A-Za-z0-9_-]+')
    ->name('public.cards.appointments.store');

Route::post('/{username}/share-events', [PublicCardController::class, 'trackShareByUsername'])
    ->where('username', '[A-Za-z0-9_-]+')
    ->name('public.cards.share-events.store');

Route::get('/{username}/out/{index}', [PublicCardController::class, 'outByUsername'])
    ->where('username', '[A-Za-z0-9_-]+')
    ->whereNumber('index')
    ->name('public.cards.username.out');

Route::get('/{username}', [PublicCardController::class, 'showByUsername'])
    ->where('username', '[A-Za-z0-9_-]+')
    ->name('public.cards.username.show');

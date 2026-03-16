<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\PublicCardController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StripeSettingsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/**
 * Public card routes for both:
 * - refery.app/{username}
 * - {username}.refery.app
 */
$usernamePattern = '[A-Za-z0-9_-]+';

$registerPublicCardRoutes = static function (bool $subdomainMode = false) use ($usernamePattern): void {
    if ($subdomainMode) {
        Route::get('/', [PublicCardController::class, 'showByUsername'])
            ->where('username', $usernamePattern)
            ->name('public.cards.subdomain.show');

        Route::get('/out/{index}', [PublicCardController::class, 'outByUsername'])
            ->where('username', $usernamePattern)
            ->whereNumber('index')
            ->name('public.cards.subdomain.out');

        Route::get('/appointments/availability', [AppointmentController::class, 'availability'])
            ->where('username', $usernamePattern)
            ->name('public.cards.subdomain.appointments.availability');

        Route::post('/appointments', [AppointmentController::class, 'storePublic'])
            ->where('username', $usernamePattern)
            ->name('public.cards.subdomain.appointments.store');

        Route::post('/share-events', [PublicCardController::class, 'trackShareByUsername'])
            ->where('username', $usernamePattern)
            ->name('public.cards.subdomain.share-events.store');

        return;
    }

    Route::get('/{username}/appointments/availability', [AppointmentController::class, 'availability'])
        ->where('username', $usernamePattern)
        ->name('public.cards.appointments.availability');

    Route::post('/{username}/appointments', [AppointmentController::class, 'storePublic'])
        ->where('username', $usernamePattern)
        ->name('public.cards.appointments.store');

    Route::post('/{username}/share-events', [PublicCardController::class, 'trackShareByUsername'])
        ->where('username', $usernamePattern)
        ->name('public.cards.share-events.store');

    Route::get('/{username}/out/{index}', [PublicCardController::class, 'outByUsername'])
        ->where('username', $usernamePattern)
        ->whereNumber('index')
        ->name('public.cards.username.out');

    Route::get('/{username}', [PublicCardController::class, 'showByUsername'])
        ->where('username', $usernamePattern)
        ->name('public.cards.username.show');
};

$appHost = parse_url((string) config('app.url'), PHP_URL_HOST) ?: '';
$appHost = preg_replace('/^www\./i', '', $appHost ?? '');

if ($appHost !== '' && str_contains($appHost, '.')) {
    Route::domain('{username}.' . $appHost)->group(static function () use ($registerPublicCardRoutes): void {
        $registerPublicCardRoutes(true);
    });
}

Route::get('/', function (Request $request) use ($appHost, $usernamePattern) {
    $host = preg_replace('/^www\./i', '', strtolower($request->getHost()));
    $normalizedAppHost = strtolower((string) $appHost);

    if (
        $normalizedAppHost !== ''
        && $host !== $normalizedAppHost
        && Str::endsWith($host, '.' . $normalizedAppHost)
    ) {
        $username = Str::before($host, '.' . $normalizedAppHost);

        if ($username !== '' && preg_match('/^' . $usernamePattern . '$/', $username) === 1) {
            return app(PublicCardController::class)->showByUsername($request, $username);
        }
    }

    return redirect('/dashboard');
});

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

Route::get('/email/verify/{id}/{hash}', VerifyEmailController::class)
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

Route::middleware('auth')->group(function (): void {
    Route::get('/email/verify', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('/signout', [AuthenticatedSessionController::class, 'destroyViaGet'])->name('signout');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/analytics', [ModuleController::class, 'analytics'])->name('analytics');
});

Route::middleware(['auth', 'verified', 'business'])->group(function (): void {
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
    Route::post('/billing/checkout', [BillingController::class, 'checkout'])->name('billing.checkout');
    Route::post('/billing/sync-session', [BillingController::class, 'syncSession'])->name('billing.sync-session');
    Route::post('/billing/cancel-subscription', [BillingController::class, 'cancelSubscription'])->name('billing.cancel-subscription');

    Route::get('/settings', [ModuleController::class, 'settings'])->name('settings');
    Route::get('/reports', [ModuleController::class, 'reports'])->name('reports');

});

Route::middleware(['auth', 'verified', 'admin'])->group(function (): void {
    Route::get('/users', [ModuleController::class, 'users'])->name('users');
    Route::get('/users/{user}', [ModuleController::class, 'userDetail'])->name('users.show');
    Route::get('/stripe-settings', [StripeSettingsController::class, 'index'])->name('stripe.settings');
    Route::put('/stripe-settings', [StripeSettingsController::class, 'update'])->name('stripe.settings.update');
});

Route::post('/locale', LocaleController::class)->name('locale.switch');

$registerPublicCardRoutes();

<?php

use App\Http\Controllers\Api\V1\AppointmentController;
use App\Http\Controllers\Api\V1\AnalyticsController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CardController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\LeadController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\ProductController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('/auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::post('/auth/logout-all', [AuthController::class, 'logoutAll']);

        Route::get('/dashboard/summary', [DashboardController::class, 'summary']);
        Route::get('/analytics', [AnalyticsController::class, 'index']);
        Route::get('/profile', [ProfileController::class, 'show']);
        Route::put('/profile', [ProfileController::class, 'update']);

        Route::get('/cards', [CardController::class, 'index']);
        Route::get('/cards/options', [CardController::class, 'options']);
        Route::get('/cards/check-username', [CardController::class, 'checkUsername']);
        Route::post('/cards', [CardController::class, 'store']);
        Route::get('/cards/{card}', [CardController::class, 'show']);
        Route::put('/cards/{card}', [CardController::class, 'update']);
        Route::delete('/cards/{card}', [CardController::class, 'destroy']);

        Route::get('/products', [ProductController::class, 'index']);
        Route::get('/products/options', [ProductController::class, 'options']);
        Route::post('/products', [ProductController::class, 'store']);
        Route::get('/products/{product}', [ProductController::class, 'show']);
        Route::put('/products/{product}', [ProductController::class, 'update']);
        Route::post('/products/{product}', [ProductController::class, 'update']);
        Route::delete('/products/{product}', [ProductController::class, 'destroy']);

        Route::get('/appointments', [AppointmentController::class, 'index']);
        Route::get('/appointments/options', [AppointmentController::class, 'options']);
        Route::get('/appointments/calendar', [AppointmentController::class, 'calendar']);
        Route::get('/appointments/day-agenda', [AppointmentController::class, 'dayAgenda']);
        Route::get('/appointments/availability', [AppointmentController::class, 'availability']);
        Route::get('/appointments/{appointment}', [AppointmentController::class, 'show']);
        Route::post('/appointments', [AppointmentController::class, 'store']);
        Route::put('/appointments/{appointment}', [AppointmentController::class, 'update']);
        Route::delete('/appointments/{appointment}', [AppointmentController::class, 'destroy']);
        Route::put('/appointments/{appointment}/status', [AppointmentController::class, 'updateStatus']);

        Route::get('/leads', [LeadController::class, 'index']);
        Route::get('/leads/options', [LeadController::class, 'options']);
        Route::post('/leads', [LeadController::class, 'store']);
        Route::get('/leads/{lead}', [LeadController::class, 'show']);
        Route::put('/leads/{lead}', [LeadController::class, 'update']);
        Route::delete('/leads/{lead}', [LeadController::class, 'destroy']);
    });
});

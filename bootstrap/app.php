<?php

use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => EnsureAdmin::class,
        ]);

        $middleware->web(append: [
            SetLocale::class,
            HandleInertiaRequests::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $redirectOnSessionExpiry = static function (Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Session expired. Please login again.',
                ], 401);
            }

            return redirect()->guest(route('login'));
        };

        $exceptions->render(function (TokenMismatchException $exception, Request $request) use ($redirectOnSessionExpiry) {
            return $redirectOnSessionExpiry($request);
        });

        $exceptions->render(function (HttpExceptionInterface $exception, Request $request) use ($redirectOnSessionExpiry) {
            if ($exception->getStatusCode() === 419) {
                return $redirectOnSessionExpiry($request);
            }

            return null;
        });
    })->create();

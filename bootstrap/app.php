<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
            \App\Http\Middleware\ContentSecurityPolicy::class,
        ]);

        // Trust proxies for load balancer compatibility
        $middleware->trustProxies(at: [
            \App\Http\Middleware\TrustProxies::class,
        ]);

        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Customize error responses to prevent sensitive information leakage
        $exceptions->render(function (Throwable $e, $request) {
            // Don't customize in debug mode - show full errors for development
            if (config('app.debug')) {
                return null;
            }

            // For API/JSON requests, return JSON error responses
            if ($request->expectsJson()) {
                $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
                
                // Ensure status code is valid
                if ($statusCode < 100 || $statusCode >= 600) {
                    $statusCode = 500;
                }

                return response()->json([
                    'message' => $e instanceof \Illuminate\Http\Exceptions\HttpException 
                        ? $e->getMessage() 
                        : 'An error occurred while processing your request.',
                    'error' => class_basename($e),
                ], $statusCode);
            }

            // For web requests with Inertia, return appropriate error pages
            if ($request->header('X-Inertia')) {
                $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
                
                // Ensure status code is valid
                if ($statusCode < 100 || $statusCode >= 600) {
                    $statusCode = 500;
                }

                // Return user-friendly error message without exposing internals
                $message = match ($statusCode) {
                    403 => 'You do not have permission to access this resource.',
                    404 => 'The requested resource was not found.',
                    419 => 'Your session has expired. Please refresh the page.',
                    429 => 'Too many requests. Please slow down.',
                    500 => 'An unexpected error occurred. Please try again later.',
                    503 => 'The service is temporarily unavailable. Please try again later.',
                    default => $e instanceof \Illuminate\Http\Exceptions\HttpException 
                        ? $e->getMessage() 
                        : 'An error occurred while processing your request.',
                };

                return response($message, $statusCode);
            }

            // Let Laravel handle other cases
            return null;
        });

        // Log all exceptions with context
        $exceptions->report(function (Throwable $e) {
            if (app()->bound('sentry')) {
                app('sentry')->captureException($e);
            }
        });
    })->create();

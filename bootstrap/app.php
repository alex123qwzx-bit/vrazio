<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        $middleware->alias([
            'otp.verified' => \App\Http\Middleware\EnsureOtpVerified::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Throwable $e, Request $request) {
            // Check if request is expecting JSON, or is an API / AJAX endpoint
            if ($request->expectsJson() || $request->is('api/*')) {
                // Let validation exceptions pass through naturally (422)
                if ($e instanceof \Illuminate\Validation\ValidationException) {
                    return null;
                }
                // Let authentication exceptions pass through naturally (401)
                if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                    return null;
                }
                // Let client http exceptions (400-499) pass through
                if ($e instanceof HttpExceptionInterface && $e->getStatusCode() < 500) {
                    return null;
                }

                // Mask all server-side / database errors from JSON clients
                Log::error('API/JSON Server Error: ' . $e->getMessage(), [
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'exception' => get_class($e),
                    'trace' => $e->getTraceAsString(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'We made a mistake. Our team has been notified. Please try again shortly.',
                ], 500);
            }

            // For standard Web/HTML requests:
            // When in production or when debug mode is disabled, hide all database and 500 errors
            if (! config('app.debug') || app()->environment('production')) {
                if ($e instanceof \Illuminate\Database\QueryException || $e instanceof \PDOException || ($e instanceof HttpExceptionInterface && $e->getStatusCode() >= 500)) {
                    Log::error('Web Server Error: ' . $e->getMessage(), [
                        'url' => $request->fullUrl(),
                        'method' => $request->method(),
                        'exception' => get_class($e),
                        'trace' => $e->getTraceAsString(),
                    ]);

                    return response()->view('errors.500', [
                        'message' => 'We made a mistake on our end. Our master technicians are attending to it.',
                    ], 500);
                }
            }

            return null;
        });
    })->create();

<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Inertia\Inertia;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
            HandleInertiaRequests::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\App\Exceptions\AppException $e, Request $request) {
            return Inertia::render('ErrorPage', [
                'status' => $e->getCode() ?: 400,
                'message' => $e->getMessage(),
            ])->toResponse($request)->setStatusCode($e->getCode() ?: 400);
        });

        $exceptions->respond(function (Response $response, Throwable $exception, Request $request) {
            $status = $response->getStatusCode();

            if (app()->environment(['local', 'testing'])) {
                return $response;
            }

            $message = match ($status) {
                403 => 'You are not authorized to access this page.',
                404 => 'Page not found.',
                500 => 'Something went wrong on our end.',
                default => 'An unexpected error occurred.',
            };

            // 🟡 For POST/PUT/etc. requests, redirect back with flash
            if (! $request->isMethod('GET')) {
                return redirect()->back()
                    ->with('error', $message); // ✅ Let it be a normal 302 response
            }

            // ✅ For GET requests, show error page
            return Inertia::render('error/page', [
                'status' => $status,
                'message' => $message,
            ])->toResponse($request)->setStatusCode($status);
        });
    })->create();

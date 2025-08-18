<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Inertia\Inertia;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Illuminate\Auth\AuthenticationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
            HandleInertiaRequests::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {

        // TESTING ONLY, SEE WHAT EXCEPTION I GET
        // if (app()->environment('local', 'testing')) {
        //     $exceptions->render(function (Throwable $e, Request $request) {
        //         dd('render', get_class($e), $e->getMessage());
        //     });
        // }



        $exceptions->render(function (\App\Exceptions\AppException $e, Request $request) {
            // Prefer explicit status over getCode()
            $status = $e->getStatusCode();

            $payload = ['status' => $status, 'message' => $e->getMessage(), 'error' => $e->error];

            if ($request->expectsJson()) {
                return response()->json($payload, $status, $e->getHeaders());
            }

            return ! $request->isMethod('GET')
                ? back()->withErrors(['error' => $payload['message']])->withInput()
                : Inertia::render('ErrorPage', $payload)->toResponse($request)->setStatusCode($status);
        });



        $exceptions->render(function (AuthenticationException $e, Request $request) {
            $msg = __('Please log in.');
            if ($request->expectsJson()) {
                return response()->json(['message' => $msg], 401);
            }
            return redirect()->guest(route('login'))->withErrors(['error' => $msg]);
        });



        $exceptions->render(function (Illuminate\Session\TokenMismatchException $e, Request $request) {
            $msg = __('Session expired. Please try again.');
            if ($request->expectsJson()) {
                return response()->json(['message' => $msg], 419);
            }
            return back()->withErrors(['error' => $msg])->withInput();
        });


        // I keep the custom exception handling for common scenarios because of
        // Log it differently
        // Add extra context (e.g., CSRF token debug info)
        // Redirect somewhere else instead of back()
        // Trigger analytics or alerts

        $exceptions->respond(function (Response $response, Throwable $exception, Request $request) {
            $status = $response->getStatusCode();

            // Pass through validation (already formatted by Laravel)
            if ($exception instanceof ValidationException) return $response;


            // Authorization / 403
            if (
                $exception instanceof AccessDeniedHttpException
                || $exception instanceof AuthorizationException
            ) {

                $authz = $exception instanceof AccessDeniedHttpException
                    ? $exception->getPrevious()
                    : $exception;

                $msg = $authz instanceof AuthorizationException && $authz->getMessage()
                    ? $authz->getMessage()
                    : __('Action not allowed.');

                if ($request->expectsJson()) {
                    return response()->json(['message' => $msg], 403);
                }

                return !$request->isMethod('GET')
                    ? back()->withErrors(['error' => $msg])->withInput()
                    : Inertia::render('ErrorPage', ['status' => 403, 'message' => $msg])->toResponse($request)->setStatusCode(403);
            }


            // Not found / 404
            if (
                $exception instanceof NotFoundHttpException
                || $exception instanceof Illuminate\Database\Eloquent\ModelNotFoundException
            ) {

                $msg = __('Page not found.');

                if ($request->expectsJson()) {
                    return response()->json(['message' => $msg], 404);
                }

                return !$request->isMethod('GET')
                    ? back()->withErrors(['error' => $msg])
                    : Inertia::render('ErrorPage', ['status' => 404, 'message' => $msg])->toResponse($request)->setStatusCode(404);
            }


            // Rate limit / 429
            if ($exception instanceof TooManyRequestsHttpException) {
                $msg = __('Too many requests. Please slow down.');

                if ($request->expectsJson()) {
                    return response()->json(['message' => $msg], 429);
                }

                return !$request->isMethod('GET')
                    ? back()->withErrors(['error' => $msg])
                    : Inertia::render('ErrorPage', ['status' => 429, 'message' => $msg])->toResponse($request)->setStatusCode(429);
            }


            // Local/testing: keep Laravel’s default 
            // Disable this to see production response (this triggers the modal overlay instead of inertia response)
            if (app()->environment(['local', 'testing'])) {
                return $response;
            }

            // Let redirects and <400 pass
            if ($response->isRedirection() || $status < 400) {
                return $response;
            }

            // Generic fallback
            $generic = match ($status) {
                401 => __('Please log in.'),
                403 => __('You are not authorized to access this page.'),
                404 => __('Page not found.'),
                405 => __('Method not allowed.'),
                419 => __('Session expired. Please try again.'),
                429 => __('Too many requests. Please slow down.'),
                default => __('Something went wrong.'),
            };

            if ($request->expectsJson()) {
                return response()->json(['message' => $generic], $status);
            }

            return !$request->isMethod('GET')
                ? back()->withErrors(['error' => $generic])->withInput()
                : Inertia::render('ErrorPage', ['status' => $status, 'message' => $generic])->toResponse($request)->setStatusCode($status);
        });
    })->create();

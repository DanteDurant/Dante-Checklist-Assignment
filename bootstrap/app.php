<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $e, $request) {
            if (! $request->is('api/*')) {
                if ($e instanceof TokenMismatchException) {
                    return redirect()->back()->with('error', 'This page expired—refresh and try again.');
                }

                if ($e instanceof QueryException) {
                    $driverCode = (int) ($e->errorInfo[1] ?? 0);
                    $lower = strtolower($e->getMessage());
                    if ($driverCode === 1062 || str_contains($lower, 'duplicate')) {
                        Log::warning('duplicate_database_constraint', [
                            'sql_state' => $e->errorInfo[0] ?? null,
                            'driver_code' => $driverCode,
                        ]);

                        return redirect()->back()->withInput()->with(
                            'error',
                            'That conflicts with existing data. Adjust your input and try again.'
                        );
                    }
                }

                return null;
            }

            if ($e instanceof ValidationException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $e->errors(),
                ], 422);
            }

            if ($e instanceof AuthenticationException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                    'errors' => [],
                ], 401);
            }

            if ($e instanceof AuthorizationException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Forbidden.',
                    'errors' => [],
                ], 403);
            }

            if ($e instanceof ModelNotFoundException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not found.',
                    'errors' => [],
                ], 404);
            }

            if ($e instanceof HttpExceptionInterface) {
                $status = $e->getStatusCode();
                $message = match ($status) {
                    400 => 'Bad request.',
                    401 => 'Unauthenticated.',
                    403 => 'Forbidden.',
                    404 => 'Not found.',
                    405 => 'Method not allowed.',
                    409 => 'Conflict.',
                    422 => 'Validation failed.',
                    429 => 'Too many requests.',
                    default => $status >= 500 ? 'Server error.' : 'Request failed.',
                };

                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'errors' => [],
                ], $status);
            }

            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Server error.',
                'errors' => [],
            ], 500);
        });
    })->create();

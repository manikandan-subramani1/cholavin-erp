<?php

use App\Helpers\ResponseHelper;
use App\Http\Middleware\EnsureActiveUser;
use App\Http\Middleware\LogUserActivity;
use App\Http\Middleware\SyncUserAccessContext;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(fn () => route('admin.auth.index'));
        $middleware->alias([
            'active' => EnsureActiveUser::class,
            'activity' => LogUserActivity::class,
            'access.context' => SyncUserAccessContext::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (ValidationException $exception, Request $request) {
            if ($request->expectsJson()) {
                return ResponseHelper::error('Validation failed.', $exception->errors(), 422);
            }
        });

        $exceptions->render(function (HttpExceptionInterface $exception, Request $request) {
            if ($request->expectsJson()) {
                return ResponseHelper::error(
                    $exception->getMessage() ?: 'The requested operation could not be completed.',
                    [],
                    $exception->getStatusCode()
                );
            }
        });

        $exceptions->render(function (\Throwable $exception, Request $request) {
            if (! $request->expectsJson()) {
                return null;
            }

            $reference = 'ERR-'.now()->format('Ymd-His').'-'.str()->upper(str()->random(5));
            report($exception);

            return ResponseHelper::error(
                app()->hasDebugModeEnabled() ? $exception->getMessage() : 'The operation could not be completed. Please try again.',
                [],
                500,
                null,
                $reference,
            );
        });
    })->create();

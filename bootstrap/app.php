<?php

use App\Http\Middleware\ActiveUser;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\EnsureEmailIsVerified;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => CheckRole::class,
            'verified' => EnsureEmailIsVerified::class,
            'active' => ActiveUser::class,
        ]);

        // Run ActiveUser on all web routes — it checks if the user is
        // logged in AND soft-deleted (FR-013). Guest requests pass through
        // untouched.
        $middleware->appendToGroup('web', [
            ActiveUser::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();

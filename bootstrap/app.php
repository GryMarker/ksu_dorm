<?php

use App\Http\Middleware\EnsureEmployeeApproved;
use App\Http\Middleware\EnsureTenantApproved;
use App\Http\Middleware\RoleMiddleware;
use App\Providers\AuthServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'tenant.approved' => EnsureTenantApproved::class,
            'employee.approved' => EnsureEmployeeApproved::class,
            'role' => RoleMiddleware::class,
        ]);
    })
    ->withProviders([
        AuthServiceProvider::class,
    ])
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

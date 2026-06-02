<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\RolMiddleware;
use App\Http\Middleware\SuperAdmin;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'rol'        => RolMiddleware::class,
            'super-admin' => SuperAdmin::class,
            'tenant'     => InitializeTenancyBySubdomain::class,
            'central-only' => PreventAccessFromCentralDomains::class,
        ]);

        // Tenancy middleware necesita la prioridad más alta
        $middleware->priority([
            PreventAccessFromCentralDomains::class,
            InitializeTenancyBySubdomain::class,
        ]);
    })
    ->withProviders([
        App\Providers\TenancyServiceProvider::class,
    ])
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

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
        // Confiar en el proxy Nginx local para que Laravel sepa que la conexión es HTTPS
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'rol'        => RolMiddleware::class,
            'super-admin' => SuperAdmin::class,
            'tenant'     => InitializeTenancyBySubdomain::class,
            'central-only' => PreventAccessFromCentralDomains::class,
        ]);

        // Tenancy middleware debe correr ANTES que SubstituteBindings (route model binding).
        // SubstituteBindings está dentro del grupo 'web' — si corre primero, resuelve
        // los modelos en la BD central antes de que el tenant esté inicializado → 404.
        $middleware->priority([
            PreventAccessFromCentralDomains::class,
            InitializeTenancyBySubdomain::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);
    })
    ->withProviders([
        App\Providers\TenancyServiceProvider::class,
    ])
    ->withExceptions(function (Exceptions $exceptions) {

        // Sesión expirada / inválida en rutas tenant → redirigir al login en vez de 500
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {

            // Solo en rutas web (no JSON/API)
            if ($request->expectsJson()) return null;

            $esErrorSesion =
                $e instanceof \Illuminate\Session\TokenMismatchException ||
                $e instanceof \Symfony\Component\HttpKernel\Exception\HttpException && $e->getStatusCode() === 419 ||
                str_contains($e->getMessage(), 'Session store not set') ||
                str_contains($e->getMessage(), 'Undefined array key "_token"');

            if ($esErrorSesion) {
                return redirect()->route('login')
                    ->withErrors(['email' => 'Tu sesión expiró. Por favor ingresá nuevamente.']);
            }

            return null; // dejar que Laravel maneje el resto normalmente
        });

    })->create();

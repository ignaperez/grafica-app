<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /*
         * @puede('clientes', 'crear') ... @endpuede
         *
         * Para esconder botones que el usuario no puede usar. El middleware ya
         * corta el acceso por ruta; esto evita mostrarle un botón que lo va a
         * mandar a un error. Sin sesión da false.
         */
        \Illuminate\Support\Facades\Blade::if('puede', function (string $modulo, string $accion = 'ver') {
            return auth()->check() && auth()->user()->puede($modulo, $accion);
        });
    }
}

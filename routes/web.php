<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SuperAdmin\EmpresaController;

/*
|--------------------------------------------------------------------------
| Rutas centrales — dominio principal (plote.ar / grafica-app.test)
| Estas rutas NO activan tenancy.
|--------------------------------------------------------------------------
*/

// Landing pública (y raíz de subdominios tenant)
Route::get('/', function () {
    // Si el request viene de un subdominio (tenant), redirigir a login o panel
    $centralDomains = config('tenancy.central_domains', []);
    if (! in_array(request()->getHost(), $centralDomains)) {
        return auth()->check()
            ? redirect()->to('/inicio')
            : redirect()->to('/login');
    }

    // Dominio central — super-admin va directo al panel
    if (auth()->check() && auth()->user()->is_super_admin) {
        return redirect()->route('super-admin.empresas.index');
    }

    // Construir URL de login del primer tenant activo para el botón "Empleados"
    $tenant    = \App\Models\Tenant::with('domains')->first();
    $loginUrl  = $tenant ? $tenant->panelUrl() . '/login' : '#';

    return view('welcome', compact('loginUrl'));
})->name('home');

/*
|--------------------------------------------------------------------------
| Panel Super-Admin
|--------------------------------------------------------------------------
*/

Route::prefix('super-admin')->name('super-admin.')->group(function () {

    // Login del super-admin (usa el auth de Breeze sobre el DB central)
    Route::get('/login',  [App\Http\Controllers\SuperAdmin\AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [App\Http\Controllers\SuperAdmin\AuthController::class, 'login'])->name('login.post');
    Route::post('/logout',[App\Http\Controllers\SuperAdmin\AuthController::class, 'logout'])->name('logout');

    // Panel protegido
    Route::middleware(['auth', 'super-admin'])->group(function () {

        Route::get('/', fn () => redirect()->route('super-admin.empresas.index'));

        // Cambiar contraseña
        Route::get('/cambiar-clave',  [App\Http\Controllers\SuperAdmin\AuthController::class, 'showCambiarClave'])->name('cambiar-clave');
        Route::post('/cambiar-clave', [App\Http\Controllers\SuperAdmin\AuthController::class, 'cambiarClave'])->name('cambiar-clave.post');

        // AJAX — consulta CUIT en padrón ARCA
        Route::get('/consultar-cuit', [EmpresaController::class, 'consultarCuit'])->name('consultar-cuit');

        Route::get('/empresas',                    [EmpresaController::class, 'index'])->name('empresas.index');
        Route::get('/empresas/crear',              [EmpresaController::class, 'create'])->name('empresas.create');
        Route::post('/empresas',                   [EmpresaController::class, 'store'])->name('empresas.store');
        Route::get('/empresas/{tenant}',           [EmpresaController::class, 'show'])->name('empresas.show');
        Route::get('/empresas/{tenant}/editar',    [EmpresaController::class, 'edit'])->name('empresas.edit');
        Route::put('/empresas/{tenant}',           [EmpresaController::class, 'update'])->name('empresas.update');
        Route::delete('/empresas/{tenant}',        [EmpresaController::class, 'destroy'])->name('empresas.destroy');
        Route::post('/empresas/{tenant}/cert',        [EmpresaController::class, 'uploadCert'])->name('empresas.cert');
        Route::post('/empresas/{tenant}/generar-csr',[EmpresaController::class, 'generarCsr'])->name('empresas.generar-csr');
        Route::get('/empresas/{tenant}/descargar-key',[EmpresaController::class, 'descargarKey'])->name('empresas.descargar-key');
        Route::post('/empresas/{tenant}/impersonar', [EmpresaController::class, 'impersonar'])->name('empresas.impersonar');

    });

});

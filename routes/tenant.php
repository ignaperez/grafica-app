<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OrdenTrabajoController;
use App\Http\Controllers\TrabajoController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ListaPrecioController;
use App\Http\Controllers\FichadaController;
use App\Http\Controllers\EmpleadoController;
use App\Http\Controllers\TipoTrabajoController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\MaquinaController;
use App\Http\Controllers\TrabajoLibreController;
use App\Http\Controllers\TrabajoArchivoController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\VehiculoPloteoController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\PresupuestoController;
use App\Http\Controllers\FacturaController;
use App\Http\Controllers\CobroController;
use App\Http\Controllers\RemitoController;
use App\Http\Controllers\RemitoCaiController;
use App\Http\Controllers\SaImpersonateController;
use App\Http\Controllers\PapeleraController;
use App\Http\Controllers\SeguimientoController;
use App\Http\Middleware\ValidateTenantSession;

/*
|--------------------------------------------------------------------------
| Rutas de tenant — solo accesibles desde subdominios
|--------------------------------------------------------------------------
|
| Estas rutas se cargan en el contexto de tenancy.
| El DB connection se cambia automáticamente al de cada empresa.
|
*/

Route::middleware([
    'web',
    InitializeTenancyBySubdomain::class,
    PreventAccessFromCentralDomains::class,
    ValidateTenantSession::class,
])->group(function () {

    // Kiosco de fichada — tablet en recepción, sin autenticación
    Route::get('/fichar',  [FichadaController::class, 'showKiosk'])->name('fichar.form');
    Route::post('/fichar', [FichadaController::class, 'store'])->name('fichar.store');

    // Super-admin impersonation — token firmado HMAC, sin middleware de auth
    Route::get('/sa-impersonate', [SaImpersonateController::class, 'login'])->name('sa-impersonate');

    // Auth Breeze (login/logout/registro del tenant)
    require __DIR__ . '/auth.php';

    /*
    |----------------------------------------------------------------------
    | Autenticadas — Admin
    |----------------------------------------------------------------------
    */

    Route::middleware(['auth', 'rol:admin'])->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/reportes/produccion', [ReporteController::class, 'index'])->name('reportes.produccion');

        Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        Route::get('/admin', fn () => 'Bienvenido administrador.');
        Route::resource('usuarios', UserController::class)->except(['show']);

        Route::get('/configuracion', [ConfiguracionController::class, 'edit'])->name('configuracion.edit');
        Route::put('/configuracion', [ConfiguracionController::class, 'update'])->name('configuracion.update');

        // Papelera — registros eliminados (soft-delete) con opción de restaurar
        Route::get('/papelera', [PapeleraController::class, 'index'])->name('papelera.index');
        Route::post('/papelera/restaurar/{tipo}/{id}', [PapeleraController::class, 'restore'])->name('papelera.restore');

        // Seguimiento — tabla de control de facturación (solo admin)
        Route::get('/seguimiento', [SeguimientoController::class, 'index'])->name('seguimientos.index');
        Route::get('/seguimiento/print', [SeguimientoController::class, 'print'])->name('seguimientos.print');
        Route::patch('/seguimiento/{seguimiento}', [SeguimientoController::class, 'update'])->name('seguimientos.update');

    });

    /*
    |----------------------------------------------------------------------
    | RRHH — solo admin
    |----------------------------------------------------------------------
    */

    Route::middleware(['auth', 'rol:admin'])->prefix('rrhh')->name('rrhh.')->group(function () {

        Route::get('/', [FichadaController::class, 'dashboard'])->name('dashboard');

        Route::get('/fichadas',     [FichadaController::class, 'index'])->name('fichadas.index');
        Route::get('/fichadas/hoy', [FichadaController::class, 'hoy'])->name('fichadas.hoy');

        Route::get('/empleados',                  [EmpleadoController::class, 'index'])->name('empleados.index');
        Route::get('/empleados/crear',            [EmpleadoController::class, 'create'])->name('empleados.create');
        Route::post('/empleados',                 [EmpleadoController::class, 'store'])->name('empleados.store');
        Route::get('/empleados/{empleado}/edit',  [EmpleadoController::class, 'edit'])->name('empleados.edit');
        Route::put('/empleados/{empleado}',       [EmpleadoController::class, 'update'])->name('empleados.update');
        Route::delete('/empleados/{empleado}',    [EmpleadoController::class, 'destroy'])->name('empleados.destroy');

        Route::get('/empleados/{empleado}/fichadas', [FichadaController::class, 'porEmpleado'])->name('empleados.fichadas');
        Route::get('/empleados/{empleado}/liquidar', [EmpleadoController::class, 'liquidar'])->name('empleados.liquidar');
        Route::post('/empleados/{empleado}/pagos',   [EmpleadoController::class, 'registrarPago'])->name('empleados.pagos.store');

    });

    /*
    |----------------------------------------------------------------------
    | AJAX — búsquedas Select2
    |----------------------------------------------------------------------
    */

    Route::middleware('auth')->group(function () {
        Route::get('/clientes/search',         [ClienteController::class,   'search'])->name('clientes.search');
        Route::get('/clientes/consultar-cuit', [ClienteController::class,   'consultarCuit'])->name('clientes.consultar-cuit');
        Route::get('/clientes/debug-padron',   [ClienteController::class,   'debugPadron'])->name('clientes.debug-padron');
        Route::get('/productos/search',        [ProductoController::class,  'search'])->name('productos.search');
        Route::get('/listas-precios/buscar',   [ListaPrecioController::class,'search'])->name('listas-precios.search');
    });

    /*
    |----------------------------------------------------------------------
    | Operativo — Admin + Ventas + Producción
    |----------------------------------------------------------------------
    */

    Route::middleware(['auth', 'rol:admin,ventas,produccion'])->group(function () {

        Route::get('/inicio', [DashboardController::class, 'inicio'])->name('inicio');

        Route::resource('ordenes-trabajo', OrdenTrabajoController::class);
        Route::get('/ordenes-trabajo/{orden}/trabajos', [OrdenTrabajoController::class, 'trabajos'])
            ->name('ordenes.trabajos');
        Route::patch('/ordenes-trabajo/{id}/estado', [OrdenTrabajoController::class, 'cambiarEstado'])
            ->name('ordenes-trabajo.estado');
        Route::patch('/ordenes-trabajo/{id}/metadata', [OrdenTrabajoController::class, 'updateMetadata'])
            ->name('ordenes-trabajo.metadata');
        Route::get('/ordenes-trabajo/{id}/print', [OrdenTrabajoController::class, 'print'])
            ->name('ordenes-trabajo.print');

        Route::post('/trabajos/ajax-store',     [TrabajoController::class, 'ajaxStore'])->name('trabajos.ajax-store');
        Route::post('/trabajos/store-multiples', [TrabajoController::class, 'storeMultiples'])->name('trabajos.store-multiples');
        Route::post('/trabajos/{id}/terminar',   [TrabajoController::class, 'marcarTerminado'])->name('trabajos.terminar');
        Route::patch('/trabajos/{id}/estado',    [TrabajoController::class, 'cambiarEstado'])->name('trabajos.estado');
        Route::get('/trabajos/crear-para/{ordenTrabajo}', [TrabajoController::class, 'createParaOrden'])->name('trabajos.create-para-orden');
        Route::resource('trabajos', TrabajoController::class);

        Route::post('/trabajos/{trabajo}/archivos',         [TrabajoArchivoController::class, 'store'])->name('trabajo-archivos.store');
        Route::delete('/trabajo-archivos/{trabajoArchivo}', [TrabajoArchivoController::class, 'destroy'])->name('trabajo-archivos.destroy');

        Route::get('/trabajos-libres',                 [TrabajoLibreController::class, 'index'])->name('trabajos-libres.index');
        Route::get('/trabajos-libres/crear',           [TrabajoLibreController::class, 'create'])->name('trabajos-libres.create');
        Route::post('/trabajos-libres',                [TrabajoLibreController::class, 'store'])->name('trabajos-libres.store');
        Route::post('/trabajos-libres/asignar-orden',  [TrabajoLibreController::class, 'asignarOrden'])->name('trabajos-libres.asignar-orden');

        Route::resource('vehiculos-ploteo', VehiculoPloteoController::class);
        Route::delete('/vehiculos-ploteo/{vehiculosPloteo}/fotos', [VehiculoPloteoController::class, 'destroyFoto'])
            ->name('vehiculos-ploteo.destroy-foto');

        Route::get('/remitos/{remito}/print',   [RemitoController::class, 'print'])->name('remitos.print');
        Route::get('/remitos/{remito}/pdf',      [RemitoController::class, 'pdf'])->name('remitos.pdf');
        Route::patch('/remitos/{remito}/estado', [RemitoController::class, 'cambiarEstado'])->name('remitos.estado');
        Route::resource('remitos', RemitoController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);

    });

    /*
    |----------------------------------------------------------------------
    | Configuración — Admin + Ventas
    |----------------------------------------------------------------------
    */

    Route::middleware(['auth', 'rol:admin,ventas'])->group(function () {

        Route::resource('clientes', ClienteController::class);

        Route::resource('tipo-trabajos', TipoTrabajoController::class)->parameters(['tipo-trabajos' => 'tipoTrabajo']);
        Route::resource('materiales', MaterialController::class)->parameters(['materiales' => 'material']);
        Route::resource('maquinas', MaquinaController::class);

        Route::get('/catalogo',       [CatalogoController::class, 'index'])->name('catalogo.index');
        Route::get('/catalogo/print', [CatalogoController::class, 'print'])->name('catalogo.print');

        Route::get('/presupuestos/precio-servicio', [PresupuestoController::class, 'precioServicio'])->name('presupuestos.precio-servicio');
        Route::get('/presupuestos/{presupuesto}/print', [PresupuestoController::class, 'print'])->name('presupuestos.print');
        Route::patch('/presupuestos/{presupuesto}/estado', [PresupuestoController::class, 'cambiarEstado'])->name('presupuestos.estado');
        Route::post('/presupuestos/{presupuesto}/convertir-ot', [PresupuestoController::class, 'convertirAOT'])->name('presupuestos.convertir-ot');
        Route::resource('presupuestos', PresupuestoController::class)->only(['index','create','store','show','edit','update','destroy']);

        Route::resource('productos',      ProductoController::class);
        Route::resource('listas-precios', ListaPrecioController::class)->parameters(['listas-precios' => 'listaPrecio']);

        Route::get('/facturas/{factura}/print',              [FacturaController::class, 'print'])->name('facturas.print');
        Route::get('/facturas/{factura}/pdf',                [FacturaController::class, 'pdf'])->name('facturas.pdf');
        Route::post('/facturas/preview',                     [FacturaController::class, 'preview'])->name('facturas.preview');
        Route::delete('/facturas/borradores/{borrador}',     [FacturaController::class, 'destroyBorrador'])->name('facturas.borradores.destroy');
        Route::post('/presupuestos/{presupuesto}/facturar',  [FacturaController::class, 'fromPresupuesto'])->name('facturas.from-presupuesto');
        Route::post('/facturas/{factura}/cobros',            [CobroController::class, 'store'])->name('facturas.cobros.store');
        Route::delete('/cobros/{cobro}',                     [CobroController::class, 'destroy'])->name('cobros.destroy');
        Route::resource('facturas', FacturaController::class)->only(['index', 'create', 'store', 'show']);

        Route::resource('remito-cais', RemitoCaiController::class)
            ->only(['index', 'create', 'store', 'destroy'])
            ->parameters(['remito-cais' => 'remitoCai'])
            ->middleware('rol:admin');

    });

});

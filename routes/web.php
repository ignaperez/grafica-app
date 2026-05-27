<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
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

/*
|--------------------------------------------------------------------------
| Rutas públicas (sin login)
|--------------------------------------------------------------------------
*/

Route::get('/', fn () => view('welcome'));

// Kiosco de fichada — tablet en recepción, sin autenticación
Route::get('/fichar',  [FichadaController::class, 'showKiosk'])->name('fichar.form');
Route::post('/fichar', [FichadaController::class, 'store'])->name('fichar.store');

/*
|--------------------------------------------------------------------------
| Rutas autenticadas — generales
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'rol:admin'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/reportes/produccion', [ReporteController::class, 'index'])->name('reportes.produccion');

    // Preview nuevo diseño
    Route::get('/dashboard/preview', [DashboardController::class, 'preview'])->name('dashboard.preview');
    Route::get('/dashboard/apply-preview', [DashboardController::class, 'applyPreview'])->name('dashboard.apply-preview');

    // Perfil
    Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rutas de prueba — podés borrarlas en producción
    Route::get('/select2-test', fn () => view('select2-test'))->name('select2-test');
    Route::get('/ordenes-test', function () {
        $ordenes = App\Models\OrdenTrabajo::with('cliente')->limit(5)->get();
        return view('ordenes-test', compact('ordenes'));
    })->name('ordenes.test');

});

/*
|--------------------------------------------------------------------------
| RRHH — solo admin
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'rol:admin'])->prefix('rrhh')->name('rrhh.')->group(function () {

    // Dashboard RRHH
    Route::get('/', [FichadaController::class, 'dashboard'])->name('dashboard');

    // Fichadas
    Route::get('/fichadas',     [FichadaController::class, 'index'])->name('fichadas.index');
    Route::get('/fichadas/hoy', [FichadaController::class, 'hoy'])->name('fichadas.hoy');

    // Empleados ABM
    Route::get('/empleados',                  [EmpleadoController::class, 'index'])->name('empleados.index');
    Route::get('/empleados/crear',            [EmpleadoController::class, 'create'])->name('empleados.create');
    Route::post('/empleados',                 [EmpleadoController::class, 'store'])->name('empleados.store');
    Route::get('/empleados/{empleado}/edit',  [EmpleadoController::class, 'edit'])->name('empleados.edit');
    Route::put('/empleados/{empleado}',       [EmpleadoController::class, 'update'])->name('empleados.update');
    Route::delete('/empleados/{empleado}',    [EmpleadoController::class, 'destroy'])->name('empleados.destroy');

    // Reportes y liquidaciones por empleado
    Route::get('/empleados/{empleado}/fichadas', [FichadaController::class, 'porEmpleado'])->name('empleados.fichadas');
    Route::get('/empleados/{empleado}/liquidar', [EmpleadoController::class, 'liquidar'])->name('empleados.liquidar');
    Route::post('/empleados/{empleado}/pagos',   [EmpleadoController::class, 'registrarPago'])->name('empleados.pagos.store');

});

/*
|--------------------------------------------------------------------------
| AJAX — búsquedas para Select2 (autenticado)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get('/clientes/search',      [ClienteController::class,   'search'])->name('clientes.search');
    Route::get('/productos/search',     [ProductoController::class,  'search'])->name('productos.search');
    Route::get('/listas-precios/buscar',[ListaPrecioController::class,'search'])->name('listas-precios.search');

});

/*
|--------------------------------------------------------------------------
| Recursos — Admin
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'rol:admin'])->group(function () {

    Route::get('/admin', fn () => 'Bienvenido administrador.');

    Route::resource('usuarios', UserController::class)->except(['show']);

});

/*
|--------------------------------------------------------------------------
| Operativo — Admin + Ventas + Producción
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'rol:admin,ventas,produccion'])->group(function () {

    Route::get('/inicio', [DashboardController::class, 'inicio'])->name('inicio');

    // Órdenes de trabajo
    Route::resource('ordenes-trabajo', OrdenTrabajoController::class);
    Route::get('/ordenes-trabajo/{orden}/trabajos', [OrdenTrabajoController::class, 'trabajos'])
        ->name('ordenes.trabajos');
    Route::patch('/ordenes-trabajo/{id}/estado', [OrdenTrabajoController::class, 'cambiarEstado'])
        ->name('ordenes-trabajo.estado');
    Route::patch('/ordenes-trabajo/{id}/metadata', [OrdenTrabajoController::class, 'updateMetadata'])
        ->name('ordenes-trabajo.metadata');
    Route::get('/ordenes-trabajo/{id}/print', [OrdenTrabajoController::class, 'print'])
        ->name('ordenes-trabajo.print');

    // Trabajos
    Route::post('/trabajos/ajax-store',    [TrabajoController::class, 'ajaxStore'])->name('trabajos.ajax-store');
    Route::post('/trabajos/store-multiples',[TrabajoController::class, 'storeMultiples'])->name('trabajos.store-multiples');
    Route::post('/trabajos/{id}/terminar',  [TrabajoController::class, 'marcarTerminado'])->name('trabajos.terminar');
    Route::patch('/trabajos/{id}/estado',   [TrabajoController::class, 'cambiarEstado'])->name('trabajos.estado');
    Route::get('/trabajos/crear-para/{ordenTrabajo}', [TrabajoController::class, 'createParaOrden'])->name('trabajos.create-para-orden');
    Route::resource('trabajos', TrabajoController::class);

    // Archivos adjuntos de trabajos
    Route::post('/trabajos/{trabajo}/archivos',        [TrabajoArchivoController::class, 'store'])->name('trabajo-archivos.store');
    Route::delete('/trabajo-archivos/{trabajoArchivo}',[TrabajoArchivoController::class, 'destroy'])->name('trabajo-archivos.destroy');

    // Trabajos libres
    Route::get('/trabajos-libres',                [TrabajoLibreController::class, 'index'])->name('trabajos-libres.index');
    Route::get('/trabajos-libres/crear',          [TrabajoLibreController::class, 'create'])->name('trabajos-libres.create');
    Route::post('/trabajos-libres',               [TrabajoLibreController::class, 'store'])->name('trabajos-libres.store');
    Route::post('/trabajos-libres/asignar-orden', [TrabajoLibreController::class, 'asignarOrden'])->name('trabajos-libres.asignar-orden');

    // Vehículos ploteo
    Route::resource('vehiculos-ploteo', VehiculoPloteoController::class);
    Route::delete('/vehiculos-ploteo/{vehiculosPloteo}/fotos', [VehiculoPloteoController::class, 'destroyFoto'])
        ->name('vehiculos-ploteo.destroy-foto');

});

/*
|--------------------------------------------------------------------------
| Configuración — Admin + Ventas (NO producción)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'rol:admin,ventas'])->group(function () {

    // Clientes
    Route::resource('clientes', ClienteController::class);

    // ABM dinámicos
    Route::resource('tipo-trabajos', TipoTrabajoController::class)->parameters(['tipo-trabajos' => 'tipoTrabajo']);
    Route::resource('materiales', MaterialController::class)->parameters(['materiales' => 'material']);
    Route::resource('maquinas', MaquinaController::class);

    // Productos y listas de precios
    Route::resource('productos',      ProductoController::class);
    Route::resource('listas-precios', ListaPrecioController::class)
        ->parameters(['listas-precios' => 'listaPrecio']);

});


/*
|--------------------------------------------------------------------------
| Auth (Breeze)
|--------------------------------------------------------------------------
*/

require __DIR__ . '/auth.php';

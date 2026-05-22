<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OrdenTrabajoController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ClienteController;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\ListaPrecio;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Página de bienvenida
Route::get('/', function () {
    return view('welcome');
});

// Rutas accesibles solo para usuarios logueados
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Perfil de usuario
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Rutas exclusivas para rol admin
Route::middleware(['auth', 'rol:admin'])->group(function () {
    Route::get('/admin', function () {
        return 'Bienvenido administrador. Acceso permitido solo a admin.';
    });
});

// Rutas exclusivas para rol ventas
Route::middleware(['auth', 'rol:ventas'])->group(function () {
    Route::get('/ventas', function () {
        return 'Bienvenido ventas. Solo acceden los de rol ventas.';
    });
});

// Rutas exclusivas para múltiples roles (admin o producción)
Route::middleware(['auth', 'rol:admin,produccion'])->group(function () {
    Route::get('/produccion', function () {
        return 'Bienvenido producción o admin.';
    });
});

// Rutas órdenes de trabajo (admin o ventas)
Route::middleware(['auth', 'rol:admin,ventas'])->group(function () {
    Route::resource('ordenes-trabajo', OrdenTrabajoController::class);
});

// Rutas cliente (solo admin)
Route::middleware(['auth', 'rol:admin'])->group(function () {
    Route::resource('clientes', ClienteController::class);
});

// -------------------------
// RUTAS AJAX (CORRECTAMENTE FUERA DE OTRAS FUNCIONES)
// -------------------------

Route::get('/clientes/search', function (Request $request) {
    $term = $request->term;

    $clientes = Cliente::where('nombre', 'like', '%' . $term . '%')
        ->limit(10)
        ->get();

    return $clientes->map(function ($c) {
        return [
            'id' => $c->id,
            'text' => $c->nombre
        ];
    });
});

Route::get('/productos/search', function (Request $request) {
    return Producto::where('nombre', 'like', '%' . $request->term . '%')
        ->limit(10)
        ->get()
        ->map(fn($p) => [
            'id' => $p->id,
            'text' => $p->nombre . ' ($' . number_format($p->precio, 2) . ')'
        ]);
})->middleware('auth');

Route::get('/listas-precios/buscar', function (Request $request) {
    $resultados = ListaPrecio::where('nombre', 'like', '%' . $request->term . '%')
        ->orderBy('nombre')
        ->get();

    return response()->json(
        $resultados->map(fn($item) => ['id' => $item->id, 'text' => $item->nombre])
    );
})->middleware('auth');

// Rutas de login, registro, etc.
require __DIR__ . '/auth.php';

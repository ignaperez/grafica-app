<?php

namespace App\Http\Controllers;

use App\Models\OrdenTrabajo;
use App\Models\Trabajo;
use App\Models\Empleado;
use App\Models\Fichada;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        return $this->renderView('dashboard');
    }

    /** Dashboard para ventas y producción. */
    public function inicio()
    {
        $hoy = Carbon::today();
        $rol = auth()->user()->rol;

        $contadores = [
            'borrador'      => OrdenTrabajo::where('estado', 'borrador')->count(),
            'en_produccion' => OrdenTrabajo::where('estado', 'en_produccion')->count(),
            'lista'         => OrdenTrabajo::where('estado', 'lista')->count(),
            'entregada_mes' => OrdenTrabajo::where('estado', 'entregada')
                                ->whereMonth('updated_at', $hoy->month)
                                ->whereYear('updated_at', $hoy->year)->count(),
        ];

        $ordenes = OrdenTrabajo::with(['cliente', 'trabajos'])
            ->whereIn('estado', ['borrador', 'en_produccion', 'lista'])
            ->orderByRaw("CASE estado
                WHEN 'en_produccion' THEN 1
                WHEN 'lista'         THEN 2
                WHEN 'borrador'      THEN 3
                ELSE 4 END")
            ->orderByDesc('id')->get();

        $proximasEntregas = Trabajo::with(['orden.cliente', 'cliente', 'tipoTrabajo'])
            ->whereNotNull('fecha_entrega')
            ->where('estado', '!=', 'terminado')
            ->whereBetween('fecha_entrega', [$hoy, $hoy->copy()->addDays(7)])
            ->orderBy('fecha_entrega')->limit(8)->get();

        // KPIs extra para producción
        $trabajosPendientes   = Trabajo::where('estado', 'pendiente')->count();
        $trabajosEnProd       = Trabajo::where('estado', 'en_produccion')->count();
        $trabajosTermHoy      = Trabajo::where('estado', 'terminado')
                                ->whereDate('updated_at', $hoy)->count();
        $trabajosLibresPend   = Trabajo::whereNull('orden_trabajo_id')
                                ->where('estado', '!=', 'terminado')->count();

        $m2Mes = round((float) (Trabajo::whereYear('created_at', $hoy->year)
            ->whereMonth('created_at', $hoy->month)
            ->selectRaw('COALESCE(SUM(ancho * alto * COALESCE(cantidad, 1)), 0) as total')
            ->value('total') ?? 0), 1);

        $vista = $rol === 'produccion' ? 'inicio-produccion' : 'inicio-ventas';

        return view($vista, compact(
            'contadores', 'ordenes', 'proximasEntregas',
            'trabajosPendientes', 'trabajosEnProd', 'trabajosTermHoy',
            'trabajosLibresPend', 'm2Mes', 'hoy'
        ));
    }

    /** Muestra el preview del nuevo diseño con los mismos datos reales. */
    public function preview()
    {
        return $this->renderView('dashboard-preview');
    }

    /** Copia dashboard-preview sobre dashboard y redirige. */
    public function applyPreview()
    {
        $src  = resource_path('views/dashboard-preview.blade.php');
        $dest = resource_path('views/dashboard.blade.php');

        // Leer preview, quitarle el banner y el botón de aplicar, guardarlo como dashboard
        $contenido = file_get_contents($src);

        // Reemplazar el page-title
        $contenido = str_replace(
            "@section('page-title', 'Dashboard — Preview nuevo diseño')",
            "@section('page-title', 'Dashboard')",
            $contenido
        );

        // Quitar el bloque topbar-actions del preview (banner + botones)
        $contenido = preg_replace(
            "/@section\('topbar-actions'\).*?@endsection/s",
            "@section('topbar-actions')\n    <a href=\"{{ route('ordenes-trabajo.create') }}\" class=\"gbtn gbtn-primary gbtn-sm\">+ Nueva orden</a>\n@endsection",
            $contenido,
            1
        );

        file_put_contents($dest, $contenido);

        return redirect()->route('dashboard')
            ->with('success', '¡Nuevo diseño aplicado al dashboard!');
    }

    /** Helper interno: prepara los datos y renderiza la vista pedida. */
    private function renderView(string $view)
    {
        $hoy = Carbon::today();

        $m2Mes = Trabajo::whereYear('created_at', $hoy->year)
            ->whereMonth('created_at', $hoy->month)
            ->selectRaw('COALESCE(SUM(ancho * alto * COALESCE(cantidad, 1)), 0) as total')
            ->value('total') ?? 0;
        $m2Mes = round((float) $m2Mes, 1);

        $contadores = [
            'borrador'      => OrdenTrabajo::where('estado', 'borrador')->count(),
            'en_produccion' => OrdenTrabajo::where('estado', 'en_produccion')->count(),
            'lista'         => OrdenTrabajo::where('estado', 'lista')->count(),
            'entregada'     => OrdenTrabajo::where('estado', 'entregada')->count(),
        ];

        $trabajosLibresPendientes = Trabajo::whereNull('orden_trabajo_id')
            ->where('estado', '!=', 'terminado')->count();

        $ordenes = OrdenTrabajo::with(['cliente', 'trabajos'])
            ->whereIn('estado', ['borrador', 'en_produccion', 'lista'])
            ->orderByRaw("CASE estado
                WHEN 'en_produccion' THEN 1
                WHEN 'lista'         THEN 2
                WHEN 'borrador'      THEN 3
                ELSE 4 END")
            ->orderByDesc('id')->get();

        $proximasEntregas = Trabajo::with(['orden.cliente', 'cliente', 'tipoTrabajo'])
            ->whereNotNull('fecha_entrega')
            ->where('estado', '!=', 'terminado')
            ->whereBetween('fecha_entrega', [$hoy, $hoy->copy()->addDays(7)])
            ->orderBy('fecha_entrega')->limit(8)->get();

        $empleados   = Empleado::where('activo', true)->orderBy('apellido')->orderBy('nombre')->get();
        $fichadasHoy = Fichada::whereDate('momento', $hoy)->orderBy('momento')->get()->groupBy('empleado_id');

        $resumenEmpleados = $empleados->map(function ($emp) use ($fichadasHoy) {
            $ultima  = $fichadasHoy->get($emp->id, collect())->last();
            $estado  = 'ausente';
            if ($ultima) {
                $estado = match ($ultima->tipo) {
                    'entrada'      => 'presente',
                    'pausa_inicio' => 'pausa',
                    'salida'       => 'salio',
                    default        => 'ausente',
                };
            }
            return ['empleado' => $emp, 'estado' => $estado, 'ultima' => $ultima];
        });

        $fichadasStats = [
            'presentes' => $resumenEmpleados->where('estado', 'presente')->count(),
            'pausa'     => $resumenEmpleados->where('estado', 'pausa')->count(),
            'salieron'  => $resumenEmpleados->where('estado', 'salio')->count(),
            'ausentes'  => $resumenEmpleados->where('estado', 'ausente')->count(),
            'total'     => $empleados->count(),
        ];

        return view($view, compact(
            'contadores', 'ordenes', 'trabajosLibresPendientes',
            'proximasEntregas', 'resumenEmpleados', 'fichadasStats', 'm2Mes'
        ));
    }
}

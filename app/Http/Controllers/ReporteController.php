<?php

namespace App\Http\Controllers;

use App\Models\Trabajo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    public function index(Request $request)
    {
        // Rango de fechas — default: mes en curso
        $desde = $request->filled('desde')
            ? Carbon::parse($request->desde)->startOfDay()
            : Carbon::now()->startOfMonth()->startOfDay();

        $hasta = $request->filled('hasta')
            ? Carbon::parse($request->hasta)->endOfDay()
            : Carbon::now()->endOfDay();

        // Base query: trabajos TERMINADOS con medidas en el rango
        $base = Trabajo::where('estado', 'terminado')
            ->whereBetween('fecha_carga', [$desde, $hasta])
            ->whereNotNull('ancho')
            ->whereNotNull('alto')
            ->where('ancho', '>', 0)
            ->where('alto',  '>', 0);

        // ── KPIs globales ─────────────────────────────────────────
        $totalM2       = (clone $base)->sum(DB::raw('ancho * alto * cantidad'));
        $totalTrabajos = (clone $base)->count();
        $totalClientes = (clone $base)->whereNotNull('cliente_id')->distinct('cliente_id')->count('cliente_id');
        $totalOrdenes  = (clone $base)->whereNotNull('orden_trabajo_id')->distinct('orden_trabajo_id')->count('orden_trabajo_id');

        // ── m² por material ───────────────────────────────────────
        $porMaterial = (clone $base)
            ->select('material_id', DB::raw('SUM(ancho * alto * cantidad) as m2_total'), DB::raw('COUNT(*) as cant_trabajos'))
            ->groupBy('material_id')
            ->orderByDesc('m2_total')
            ->with('material')
            ->get();

        // ── Top 5 clientes por m² ─────────────────────────────────
        $topClientes = (clone $base)
            ->whereNotNull('cliente_id')
            ->select('cliente_id', DB::raw('SUM(ancho * alto * cantidad) as m2_total'), DB::raw('COUNT(*) as cant_trabajos'))
            ->groupBy('cliente_id')
            ->orderByDesc('m2_total')
            ->limit(5)
            ->with('cliente')
            ->get();

        // ── m² por día (para sparkline) ───────────────────────────
        $porDia = (clone $base)
            ->select(DB::raw('DATE(fecha_carga) as dia'), DB::raw('SUM(ancho * alto * cantidad) as m2_total'))
            ->groupBy('dia')
            ->orderBy('dia')
            ->get()
            ->keyBy('dia');

        return view('reportes.produccion', compact(
            'desde', 'hasta',
            'totalM2', 'totalTrabajos', 'totalClientes', 'totalOrdenes',
            'porMaterial', 'topClientes', 'porDia'
        ));
    }
}

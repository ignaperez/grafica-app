<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Seguimiento;

class SeguimientoController extends Controller
{
    public function index(Request $request)
    {
        $anios = $this->aniosDisponibles();
        $anio  = (int) $request->input('anio', now()->year);
        if ($anios->isNotEmpty() && ! $anios->contains($anio)) {
            $anio = $anios->first();
        }

        $seguimientos = Seguimiento::with(['presupuesto.cliente', 'factura.cobros'])
            ->join('presupuestos', 'presupuestos.id', '=', 'seguimientos.presupuesto_id')
            ->whereNull('presupuestos.deleted_at')
            ->whereYear('presupuestos.fecha', $anio)
            ->orderByDesc('presupuestos.fecha')
            ->orderByDesc('presupuestos.numero')
            ->select('seguimientos.*')
            ->paginate(30)
            ->withQueryString();

        $totales = $this->totalesAnio($anio);

        return view('seguimientos.index', compact('seguimientos', 'anios', 'anio', 'totales'));
    }

    public function print(Request $request)
    {
        $anio = (int) $request->input('anio', now()->year);

        $seguimientos = Seguimiento::with(['presupuesto.cliente', 'factura.cobros'])
            ->whereHas('presupuesto', fn ($q) => $q->whereYear('fecha', $anio))
            ->get()
            ->filter(fn ($s) => $s->presupuesto)
            ->sortByDesc(fn ($s) => $s->presupuesto->fecha)
            ->values();

        $totales = $this->totalesAnio($anio);

        return view('seguimientos.print', compact('seguimientos', 'anio', 'totales'));
    }

    /** Guarda los campos manuales de una fila (edición en línea, AJAX). */
    public function update(Request $request, Seguimiento $seguimiento)
    {
        $data = $request->validate([
            'area_oficina'  => 'nullable|string|max:255',
            'detalle'       => 'nullable|string|max:1000',
            'orden_compra'  => 'nullable|string|max:4',
            'monto_op'      => 'nullable|numeric|min:0',
            'estado'        => 'required|in:' . implode(',', array_keys(Seguimiento::ESTADOS)),
            'observaciones' => 'nullable|string|max:1000',
            'pasado_a'      => 'nullable|string|max:255',
            'fecha_pago'    => 'nullable|date',
        ]);

        $seguimiento->update($data);

        return response()->json([
            'ok'          => true,
            'estado'      => $seguimiento->estado,
            'estadoLabel' => $seguimiento->estadoLabel(),
            'estadoBg'    => $seguimiento->estadoBg(),
            'estadoText'  => $seguimiento->estadoText(),
            'mostrarCalc' => $seguimiento->mostrarCalculos(),
            'iva21'       => number_format($seguimiento->iva21(), 2, ',', '.'),
            'cinco'       => number_format($seguimiento->cinco(), 2, ',', '.'),
            'totalHernan' => number_format($seguimiento->totalHernan(), 2, ',', '.'),
        ]);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /** Años (de la fecha del presupuesto) que tienen filas, desc. */
    private function aniosDisponibles()
    {
        return Seguimiento::query()
            ->join('presupuestos', 'presupuestos.id', '=', 'seguimientos.presupuesto_id')
            ->whereNull('presupuestos.deleted_at')
            ->whereNotNull('presupuestos.fecha')
            ->selectRaw('YEAR(presupuestos.fecha) as y')
            ->distinct()->orderByDesc('y')->pluck('y')
            ->map(fn ($y) => (int) $y)
            ->values();
    }

    /** Totales del año: presupuestado, facturado, cobrado, pendiente. */
    private function totalesAnio(int $anio): array
    {
        $rows = Seguimiento::with(['presupuesto', 'factura.cobros'])
            ->whereHas('presupuesto', fn ($q) => $q->whereYear('fecha', $anio))
            ->get();

        $presupuestado = round($rows->sum(fn ($s) => $s->montoBase()), 2);
        $facturado     = round($rows->sum(fn ($s) => (float) ($s->factura?->imp_total ?? 0)), 2);
        $cobrado       = round($rows->sum(fn ($s) => (float) ($s->factura?->cobros->sum('monto') ?? 0)), 2);
        $pendiente     = round($presupuestado - $cobrado, 2);

        return compact('presupuestado', 'facturado', 'cobrado', 'pendiente');
    }
}

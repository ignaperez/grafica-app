<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Seguimiento;
use App\Models\Factura;

class SeguimientoController extends Controller
{
    public function index(Request $request)
    {
        $anios = $this->aniosDisponibles();
        $anio  = (int) $request->input('anio', now()->year);
        if ($anios->isNotEmpty() && ! $anios->contains($anio)) {
            $anio = $anios->first();
        }

        $seguimientos = $this->baseQuery($anio)
            ->with(['presupuesto.cliente', 'factura.cobros'])
            ->orderByRaw('COALESCE(presupuestos.fecha, seguimientos.fecha_manual) DESC')
            ->orderByDesc('seguimientos.id')
            ->paginate(30)
            ->withQueryString();

        $totales = $this->totalesAnio($anio);

        // Facturas para vincular a mano (las que no están ya en otra fila)
        $facturas = Factura::where('estado', '!=', 'anulada')
            ->whereNotIn('id', Seguimiento::whereNotNull('factura_id')->pluck('factura_id'))
            ->with('cliente')->orderByDesc('id')->limit(300)->get();

        return view('seguimientos.index', compact('seguimientos', 'anios', 'anio', 'totales', 'facturas'));
    }

    public function print(Request $request)
    {
        $anio = (int) $request->input('anio', now()->year);

        $seguimientos = $this->baseQuery($anio)
            ->with(['presupuesto.cliente', 'factura.cobros'])
            ->orderByRaw('COALESCE(presupuestos.fecha, seguimientos.fecha_manual) DESC')
            ->get();

        $totales = $this->totalesAnio($anio);

        return view('seguimientos.print', compact('seguimientos', 'anio', 'totales'));
    }

    /** Alta manual de un proceso (viene del sistema anterior, sin presupuesto acá). */
    public function store(Request $request)
    {
        $data = $request->validate([
            'fecha_manual'  => 'required|date',
            'numero_manual' => 'nullable|string|max:100',
            'monto_manual'  => 'required|numeric|min:0',
            'factura_id'    => 'nullable|exists:facturas,id',
            'area_oficina'  => 'nullable|string|max:255',
            'detalle'       => 'nullable|string|max:1000',
            'estado'        => 'required|in:' . implode(',', array_keys(Seguimiento::ESTADOS)),
        ]);

        $data['presupuesto_id'] = null;   // fila manual
        Seguimiento::create($data);

        return back()->with('success', 'Proceso cargado a mano.');
    }

    /** Guarda los campos editables de una fila (edición en línea, AJAX). */
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
            // Solo llegan en filas manuales
            'fecha_manual'  => 'nullable|date',
            'numero_manual' => 'nullable|string|max:100',
            'monto_manual'  => 'nullable|numeric|min:0',
            'factura_id'    => 'nullable|exists:facturas,id',
        ]);

        // No permitir pisar los datos de un presupuesto real con campos manuales
        if (! $seguimiento->esManual()) {
            unset($data['fecha_manual'], $data['numero_manual'], $data['monto_manual']);
        }

        $seguimiento->update($data);
        $seguimiento->refresh()->load('factura.cobros');

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
            'facturaFecha'=> $seguimiento->factura?->fecha?->format('d/m/y') ?? '—',
        ]);
    }

    /** Elimina una fila cargada a mano (las automáticas se manejan por presupuesto). */
    public function destroy(Seguimiento $seguimiento)
    {
        abort_unless($seguimiento->esManual(), 403, 'Solo se pueden eliminar los procesos cargados a mano.');

        $seguimiento->delete();

        return back()->with('success', 'Proceso eliminado.');
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Base: filas del año, tomando la fecha del presupuesto o la manual.
     * Incluye manuales (sin presupuesto) y excluye las de presupuestos borrados.
     */
    private function baseQuery(int $anio)
    {
        return Seguimiento::query()
            ->leftJoin('presupuestos', function ($j) {
                $j->on('presupuestos.id', '=', 'seguimientos.presupuesto_id')
                  ->whereNull('presupuestos.deleted_at');
            })
            ->where(function ($q) {
                $q->whereNull('seguimientos.presupuesto_id')      // manual
                  ->orWhereNotNull('presupuestos.id');            // presupuesto vivo
            })
            ->whereRaw('YEAR(COALESCE(presupuestos.fecha, seguimientos.fecha_manual)) = ?', [$anio])
            ->select('seguimientos.*');
    }

    /** Años con filas (por fecha de presupuesto o manual), desc. */
    private function aniosDisponibles()
    {
        return Seguimiento::query()
            ->leftJoin('presupuestos', function ($j) {
                $j->on('presupuestos.id', '=', 'seguimientos.presupuesto_id')
                  ->whereNull('presupuestos.deleted_at');
            })
            ->whereRaw('COALESCE(presupuestos.fecha, seguimientos.fecha_manual) IS NOT NULL')
            ->selectRaw('YEAR(COALESCE(presupuestos.fecha, seguimientos.fecha_manual)) as y')
            ->distinct()->orderByDesc('y')->pluck('y')
            ->map(fn ($y) => (int) $y)
            ->values();
    }

    /** Totales del año: presupuestado, facturado, cobrado, pendiente. */
    private function totalesAnio(int $anio): array
    {
        $rows = $this->baseQuery($anio)->with(['presupuesto', 'factura.cobros'])->get();

        $presupuestado = round($rows->sum(fn ($s) => $s->montoBase()), 2);
        $facturado     = round($rows->sum(fn ($s) => (float) ($s->factura?->imp_total ?? 0)), 2);
        $cobrado       = round($rows->sum(fn ($s) => (float) ($s->factura?->cobros->sum('monto') ?? 0)), 2);
        $pendiente     = round($presupuestado - $cobrado, 2);

        return compact('presupuestado', 'facturado', 'cobrado', 'pendiente');
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Remito;
use App\Models\RemitoCai;
use App\Models\RemitoItem;
use App\Models\Cliente;
use App\Models\Presupuesto;
use App\Models\Factura;
use App\Services\RemWsService;

class RemitoController extends Controller
{
    public function index()
    {
        $rol = auth()->user()->rol;

        $query = Remito::with(['cliente', 'createdBy'])->orderByDesc('id');

        // Producción solo ve remitos internos
        if ($rol === 'produccion') {
            $query->where('tipo', 'interno');
        }

        $remitos = $query->get();

        return view('remitos.index', compact('remitos'));
    }

    public function create(Request $request)
    {
        $presupuesto = null;
        $factura     = null;
        $rol         = auth()->user()->rol;

        if ($request->filled('presupuesto_id')) {
            $presupuesto = Presupuesto::with(['cliente', 'items'])->find($request->presupuesto_id);
        }

        if ($request->filled('factura_id')) {
            $factura = Factura::with(['cliente', 'items'])->find($request->factura_id);
        }

        // Producción solo puede crear remitos internos
        $puedeOficial = in_array($rol, ['admin', 'ventas']);
        $caiVigente   = $puedeOficial ? RemitoCai::vigente() : null;

        // CAIs utilizables (activos, con stock) para resolver la vigencia por
        // fecha del remito en el front (no solo contra hoy).
        $cais = $puedeOficial
            ? RemitoCai::where('activo', true)
                ->whereRaw('ultimo_numero < numero_hasta')
                ->orderByDesc('id')
                ->get(['id', 'punto_venta', 'vencimiento', 'ultimo_numero', 'numero_hasta'])
            : collect();

        // Remito electrónico disponible si hay PV REM configurado
        $pvRem         = (int) \App\Models\Configuracion::get('arca_pv_rem', 0);
        $puedeElectronico = $puedeOficial && $pvRem > 0;

        return view('remitos.create', compact(
            'presupuesto', 'factura', 'puedeOficial', 'caiVigente', 'cais', 'puedeElectronico', 'pvRem'
        ));
    }

    public function store(Request $request)
    {
        $rol = auth()->user()->rol;

        $request->validate([
            'cliente_id'           => 'required|exists:clientes,id',
            'fecha'                => 'required|date',
            'numero_manual'        => 'nullable|integer|min:1',
            'tipo'                 => 'required|in:interno,oficial,electronico',
            'observaciones'        => 'nullable|string',
            'items'                => 'required|array|min:1',
            'items.*.descripcion'  => 'required|string|max:255',
            'items.*.cantidad'     => 'required|numeric|min:0.001',
            'items.*.unidad'       => 'required|string|max:30',
        ]);

        // Producción no puede crear remitos oficiales
        $tipo = $request->tipo;
        if (!in_array($rol, ['admin', 'ventas'])) {
            $tipo = 'interno';
        }

        // Número interno correlativo: manual si vino, sino el siguiente del tipo.
        // Cada tipo (interno/oficial/electronico) tiene su PROPIA secuencia.
        $numeroFinal = $request->filled('numero_manual')
            ? (int) $request->numero_manual
            : Remito::proximoNumero($tipo);

        // Guard amistoso: evitar el choque de número dentro del mismo tipo
        // (incluye soft-deleted) ANTES de pegarle a ARCA/CAI, así no gastamos
        // un número fiscal ni tiramos un 500 por la restricción única.
        if (Remito::withTrashed()->where('tipo', $tipo)->where('numero', $numeroFinal)->exists()) {
            return back()->withInput()->with('error',
                'Ya existe un remito ' . $tipo . ' con el número R-' .
                str_pad($numeroFinal, 4, '0', STR_PAD_LEFT) . '. Probá con otro número.'
            );
        }

        // ── Remito Electrónico (WSREMV1) ────────────────────────────────────
        $remData = [];
        if ($tipo === 'electronico') {
            $cliente = Cliente::findOrFail($request->cliente_id);
            try {
                $wsrem = new RemWsService();
                $items = collect($request->items)->map(fn($it) => [
                    'descripcion' => $it['descripcion'],
                    'cantidad'    => $it['cantidad'],
                    'unidad'      => $it['unidad'],
                ])->toArray();

                $resultado = $wsrem->autorizar([
                    'DocTipo'   => $cliente->cuit ? 80 : 99,
                    'DocNro'    => preg_replace('/\D/', '', $cliente->cuit ?? '0'),
                    'Nombre'    => $cliente->nombre,
                    'Domicilio' => $cliente->direccion ?? '',
                    'Items'     => $items,
                ]);

                $remData = [
                    'numero_fiscal'        => $resultado->numero,
                    'punto_venta'          => $wsrem->getPtoVta(),
                    'cod_autorizacion'     => $resultado->cod_autorizacion,
                    'cod_autorizacion_vto' => $resultado->cod_autorizacion_vto,
                ];
            } catch (\Exception $e) {
                return back()->withInput()->with('error', 'Error ARCA: ' . $e->getMessage());
            }
        }

        // ── Asignar CAI si es oficial (papel) ────────────────────────────
        // La vigencia se evalúa contra la FECHA del remito (no contra hoy),
        // así un remito fechado el día 4 usa el CAI que vencía el día 4.
        if ($tipo === 'oficial') {
            $cai = RemitoCai::vigenteParaFecha($request->fecha);
            if (!$cai) {
                return back()->withInput()->with('error',
                    'No hay ningún CAI válido para la fecha ' .
                    \Carbon\Carbon::parse($request->fecha)->format('d/m/Y') .
                    ' con números disponibles. Revisá la fecha del remito o cargá/activá el CAI correspondiente.'
                );
            }
            $nroFiscal = $cai->reservarNumero();
            if (!$nroFiscal) {
                return back()->withInput()->with('error',
                    'El CAI ' . $cai->codigo . ' ya no tiene números disponibles (rango agotado).'
                );
            }
            $remData = [
                'remito_cai_id' => $cai->id,
                'numero_fiscal' => $nroFiscal,
                'punto_venta'   => $cai->punto_venta,
            ];
        }

        $remito = Remito::create(array_merge([
            'numero'           => $numeroFinal,
            'cliente_id'       => $request->cliente_id,
            'presupuesto_id'   => $request->presupuesto_id ?: null,
            'factura_id'       => $request->factura_id ?: null,
            'orden_trabajo_id' => $request->orden_trabajo_id ?: null,
            'created_by'       => auth()->id(),
            'fecha'            => $request->fecha,
            'estado'           => 'pendiente',
            'tipo'             => $tipo,
            'observaciones'    => $request->observaciones,
        ], $remData));

        foreach ($request->items as $i => $it) {
            RemitoItem::create([
                'remito_id'   => $remito->id,
                'descripcion' => $it['descripcion'],
                'cantidad'    => $it['cantidad'],
                'unidad'      => $it['unidad'],
                'orden'       => $i,
            ]);
        }

        return redirect()->route('remitos.show', $remito->id)
            ->with('success', 'Remito ' . $remito->numeroFormateado() . ' creado correctamente.');
    }

    public function show(Remito $remito)
    {
        $remito->load(['cliente', 'presupuesto', 'factura', 'items', 'createdBy', 'remitoCai']);
        return view('remitos.show', compact('remito'));
    }

    public function print(Remito $remito)
    {
        $remito->load(['cliente', 'items', 'remitoCai']);
        return view('remitos.print', compact('remito'));
    }

    /**
     * PDF A4 generado con mPDF (mismo formato que la factura, sin precios/total
     * ni QR/CAE — usa el código propio del remito). ?download=1 fuerza descarga.
     */
    public function pdf(Request $request, Remito $remito)
    {
        $service = new \App\Services\RemitoPdfService();
        $mpdf    = $service->generar($remito);
        $pdf     = $mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN);

        $nombre      = $service->nombreArchivo($remito) . '.pdf';
        $disposition = $request->boolean('download') ? 'attachment' : 'inline';

        return response($pdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => $disposition . '; filename="' . $nombre . '"',
        ]);
    }

    public function cambiarEstado(Request $request, Remito $remito)
    {
        $request->validate(['estado' => 'required|in:pendiente,entregado,cancelado']);
        $remito->update(['estado' => $request->estado]);

        return back()->with('success', 'Estado actualizado a: ' . $remito->fresh()->estadoLabel());
    }

    public function destroy(Remito $remito)
    {
        $remito->delete();
        return redirect()->route('remitos.index')
            ->with('success', 'Remito ' . $remito->numeroFormateado() . ' eliminado.');
    }
}

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

        // Remito electrónico disponible si hay PV REM configurado
        $pvRem         = (int) \App\Models\Configuracion::get('arca_pv_rem', 0);
        $puedeElectronico = $puedeOficial && $pvRem > 0;

        return view('remitos.create', compact(
            'presupuesto', 'factura', 'puedeOficial', 'caiVigente', 'puedeElectronico', 'pvRem'
        ));
    }

    public function store(Request $request)
    {
        $rol = auth()->user()->rol;

        $request->validate([
            'cliente_id'           => 'required|exists:clientes,id',
            'fecha'                => 'required|date',
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
        if ($tipo === 'oficial') {
            $cai = RemitoCai::vigente();
            if ($cai) {
                $nroFiscal = $cai->reservarNumero();
                if ($nroFiscal) {
                    $remData = [
                        'remito_cai_id' => $cai->id,
                        'numero_fiscal' => $nroFiscal,
                        'punto_venta'   => $cai->punto_venta,
                    ];
                }
            }
        }

        $remito = Remito::create(array_merge([
            'numero'           => in_array($tipo, ['oficial','electronico'])
                                    ? Remito::proximoNumeroOficial()
                                    : Remito::proximoNumero(),
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

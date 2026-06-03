<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Factura;
use App\Models\FacturaItem;
use App\Models\Cliente;
use App\Models\Presupuesto;
use App\Services\ArcaService;

class FacturaController extends Controller
{
    public function index()
    {
        $facturas = Factura::with(['cliente', 'presupuesto', 'createdBy'])
            ->orderByDesc('id')
            ->get();

        return view('facturas.index', compact('facturas'));
    }

    public function create(Request $request)
    {
        $presupuesto         = null;
        $clienteSeleccionado = null;
        $condIva             = \App\Models\Configuracion::get('empresa_condicion_iva', '');
        $condicionEmisor     = $condIva === 'responsable_inscripto' ? 'responsable_inscripto' : 'monotributo';
        // Tipo por defecto: RI → Factura B (6), Monotributo → Factura C (11)
        $tipoCbte            = $condicionEmisor === 'responsable_inscripto' ? 6 : 11;

        // Tipos de comprobante desde ARCA (con fallback)
        try {
            $tiposCbte = (new ArcaService())->tiposCbte();
        } catch (\Exception $e) {
            $tiposCbte = \App\Services\ArcaService::TIPOS_CBTE;
        }

        // Filtrar tipos según condición del emisor:
        // Monotributista → solo C (11) y NC-C (13)
        // RI            → A (1), B (6), NC-A (3), NC-B (8) — NO Factura C
        if ($condicionEmisor === 'monotributo') {
            $tiposCbte = array_intersect_key($tiposCbte, [11 => null, 13 => null]);
        } elseif ($condicionEmisor === 'responsable_inscripto') {
            $tiposCbte = array_intersect_key($tiposCbte, [1 => null, 6 => null, 3 => null, 8 => null]);
        }

        if ($request->filled('presupuesto_id')) {
            $presupuesto         = Presupuesto::with(['cliente', 'items'])->find($request->presupuesto_id);
            $clienteSeleccionado = $presupuesto?->cliente;
        }

        // Si hubo un error de validación y se volvió con old(), recuperar el cliente
        if (! $clienteSeleccionado && old('cliente_id')) {
            $clienteSeleccionado = Cliente::find(old('cliente_id'));
        }

        return view('facturas.create', compact('presupuesto', 'tipoCbte', 'tiposCbte', 'clienteSeleccionado', 'condicionEmisor'));
    }

    public function store(Request $request)
    {
        $isNC = in_array((int) $request->tipo, [3, 8, 13]);

        $request->validate([
            'cliente_id'     => 'required|exists:clientes,id',
            'tipo'           => 'required|in:1,3,6,8,11,13',
            'concepto'       => 'required|in:1,2,3',
            'doc_tipo'       => 'required|in:80,96,99',
            // doc_nro requerido solo cuando doc_tipo != 99 (Consumidor Final)
            'doc_nro'        => 'required_unless:doc_tipo,99|nullable|string|max:20',
            'observaciones'  => 'nullable|string',
            'items'          => 'required|array|min:1',
            'items.*.descripcion'     => 'required|string|max:255',
            'items.*.cantidad'        => 'required|numeric|min:0.001',
            'items.*.precio_unitario' => 'required|numeric|min:0',
            // Comprobante original (solo para NCs)
            'nc_tipo'    => $isNC ? 'required|in:1,6,11' : 'nullable|integer',
            'nc_pto_vta' => $isNC ? 'required|integer|min:1' : 'nullable|integer',
            'nc_nro'     => $isNC ? 'required|integer|min:1' : 'nullable|integer',
        ]);

        $cbteTipo        = (int) $request->tipo;
        $condIvaEm       = \App\Models\Configuracion::get('empresa_condicion_iva', '');
        $condicionEmisor = $condIvaEm === 'responsable_inscripto' ? 'responsable_inscripto' : 'monotributo';

        // Validar tipo según condición del EMISOR
        if ($condicionEmisor === 'monotributo' && !in_array($cbteTipo, [11, 13])) {
            return back()->withInput()->with('error',
                'Como Monotributista solo podés emitir Factura C y Nota de Crédito C.'
            );
        }

        if ($condicionEmisor === 'responsable_inscripto') {
            $cliente = Cliente::findOrFail($request->cliente_id);
            if ($cbteTipo === 1 && $cliente->condicion_iva !== 'responsable_inscripto') {
                return back()->withInput()->with('error',
                    'Factura A solo se puede emitir a Responsables Inscriptos. ' .
                    'Este cliente es ' . ($cliente->condicionIvaLabel() ?: 'sin condición IVA registrada') . '.'
                );
            }
        }

        // ARCA no permite que el DocNro del receptor sea igual al CUIT del emisor
        if ((int) $request->doc_nro === (int) \App\Models\Configuracion::get('empresa_cuit')) {
            return back()->withInput()->with('error',
                'El CUIT del receptor no puede ser igual al CUIT del emisor (ARCA error 10069).'
            );
        }

        // Calcular total desde los ítems
        $total = 0;
        foreach ($request->items as $it) {
            $total += round((float)$it['cantidad'] * (float)$it['precio_unitario'], 2);
        }

        // Solicitar CAE a ARCA
        try {
            $arca     = new ArcaService();
            $cbteTipo = (int) $request->tipo;

            $arcaData = [
                'CbteTipo' => $cbteTipo,
                'Concepto' => (int) $request->concepto,
                'DocTipo'  => (int) $request->doc_tipo,
                'DocNro'   => (int) ($request->doc_nro ?? 0),
                'ImpTotal' => $total,
            ];

            // Para Notas de Crédito: referencia al comprobante original
            if ($isNC) {
                $arcaData['NcTipo']   = (int) $request->nc_tipo;
                $arcaData['NcPtoVta'] = (int) $request->nc_pto_vta;
                $arcaData['NcNro']    = (int) $request->nc_nro;
            }

            $resultado = $arca->solicitarCAE($arcaData);
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error ARCA: ' . $e->getMessage());
        }

        // Calcular importes finales (NC-C sin IVA, igual que Factura C)
        [$impNeto, $impIva] = $this->calcularNeto($cbteTipo, $total);

        // Crear factura en DB
        $factura = Factura::create([
            'presupuesto_id'  => $request->presupuesto_id ?: null,
            'cliente_id'      => $request->cliente_id,
            'created_by'      => auth()->id(),
            'tipo'            => $cbteTipo,
            'punto_venta'     => $arca->getPtoVta(),
            'numero'          => $resultado->numero,
            'fecha'           => now()->toDateString(),
            'cae'             => $resultado->cae,
            'cae_vencimiento' => $resultado->cae_vencimiento,
            'estado'          => 'emitida',
            'doc_tipo'        => (int) $request->doc_tipo,
            'doc_nro'         => $request->doc_nro ?: null,
            'concepto'        => (int) $request->concepto,
            'imp_neto'        => $impNeto,
            'imp_iva'         => $impIva,
            'imp_total'       => $total,
            'observaciones'   => $request->observaciones,
            'nc_tipo'         => $isNC ? (int) $request->nc_tipo    : null,
            'nc_pto_vta'      => $isNC ? (int) $request->nc_pto_vta : null,
            'nc_nro'          => $isNC ? (int) $request->nc_nro     : null,
        ]);

        // Guardar ítems
        foreach ($request->items as $i => $it) {
            $subtotal = round((float)$it['cantidad'] * (float)$it['precio_unitario'], 2);
            FacturaItem::create([
                'factura_id'      => $factura->id,
                'descripcion'     => $it['descripcion'],
                'cantidad'        => $it['cantidad'],
                'precio_unitario' => $it['precio_unitario'],
                'subtotal'        => $subtotal,
                'alicuota_iva'    => $cbteTipo === 11 ? 0 : 21,
                'orden'           => $i,
            ]);
        }

        // Si venía de un presupuesto, marcarlo como facturado
        if ($request->presupuesto_id) {
            Presupuesto::find($request->presupuesto_id)?->update(['estado' => 'aprobado']);
        }

        return redirect()->route('facturas.show', $factura->id)
            ->with('success', 'Factura ' . $factura->numeroFormateado() . ' emitida. CAE: ' . $factura->cae);
    }

    public function show(Factura $factura)
    {
        $factura->load(['cliente', 'presupuesto', 'items', 'createdBy']);
        return view('facturas.show', compact('factura'));
    }

    public function print(Factura $factura)
    {
        $factura->load(['cliente', 'items']);
        return view('facturas.print', compact('factura'));
    }

    // ── Vista previa (sin llamar a ARCA) ─────────────────────────────────

    public function preview(Request $request)
    {
        $cliente  = Cliente::find($request->cliente_id);
        $tipo     = (int) $request->tipo;
        $docTipo  = (int) $request->doc_tipo;
        $docNro   = $request->doc_nro;
        $concepto = (int) $request->concepto;

        // Calcular ítems y totales
        $items = [];
        $total = 0;
        foreach ($request->items ?? [] as $item) {
            $sub     = round((float)($item['cantidad'] ?? 0) * (float)($item['precio_unitario'] ?? 0), 2);
            $total  += $sub;
            $items[] = [
                'descripcion'     => $item['descripcion'] ?? '',
                'cantidad'        => $item['cantidad']     ?? 0,
                'precio_unitario' => $item['precio_unitario'] ?? 0,
                'subtotal'        => $sub,
            ];
        }

        [$impNeto, $impIva] = $this->calcularNeto($tipo, $total);

        $cfg = \App\Models\Configuracion::all()->pluck('valor', 'clave');

        $docTipoLabel = match($docTipo) {
            80 => 'CUIT',
            96 => 'DNI',
            default => 'Consumidor Final',
        };

        $conceptoLabel = match($concepto) {
            1 => 'Productos',
            3 => 'Productos y servicios',
            default => 'Servicios',
        };

        $observaciones = $request->observaciones;

        return view('facturas.preview', compact(
            'cliente', 'tipo', 'items', 'total', 'impNeto', 'impIva',
            'cfg', 'docTipo', 'docNro', 'docTipoLabel', 'conceptoLabel', 'observaciones'
        ));
    }

    // ── Desde presupuesto ─────────────────────────────────────────────────

    /**
     * Inicia el flujo de facturación desde un presupuesto aprobado.
     */
    public function fromPresupuesto(Presupuesto $presupuesto)
    {
        $presupuesto->load(['cliente', 'items']);
        return redirect()->route('facturas.create', ['presupuesto_id' => $presupuesto->id]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    private function calcularNeto(int $tipo, float $total): array
    {
        // C (11) y NC-C (13): monotributista, sin IVA
        if (in_array($tipo, [11, 13])) {
            return [$total, 0.0];
        }
        $neto = round($total / 1.21, 2);
        $iva  = round($total - $neto, 2);
        return [$neto, $iva];
    }
}

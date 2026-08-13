<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Presupuesto;
use App\Models\PresupuestoItem;
use App\Models\Cliente;
use App\Models\Maquina;
use App\Models\Configuracion;
use App\Models\OrdenTrabajo;
use App\Models\VehiculoPloteo;

class PresupuestoController extends Controller
{
    public function index()
    {
        $presupuestos = Presupuesto::with(['cliente', 'createdBy', 'updatedBy'])
            // Cantidad de facturas NO anuladas → para bloquear el botón "Facturar"
            ->withCount(['facturas as facturado_count' => fn ($q) => $q->where('estado', '!=', 'anulada')])
            ->orderByDesc('numero')
            ->get();

        return view('presupuestos.index', compact('presupuestos'));
    }

    public function create()
    {
        $catalogo  = $this->buildCatalogo();
        $clientes  = Cliente::orderBy('nombre')->get();
        $moGlobal  = Configuracion::mo();
        $prefill   = session('presu_prefill', []);   // precarga desde vehículos (no persistida)

        return view('presupuestos.create', compact('catalogo', 'clientes', 'moGlobal', 'prefill'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'cliente_id'        => 'required|exists:clientes,id',
            'fecha'             => 'required|date',
            'fecha_vencimiento' => 'nullable|date|after_or_equal:fecha',
            'observaciones'     => 'nullable|string',
            'nota_interna'      => 'nullable|string',
            'items'             => 'required|array|min:1',
            'items.*.descripcion'     => 'required|string|max:1000',
            'items.*.unidad'          => 'required|in:m2,ml,unidad',
            'items.*.cantidad'        => 'required|integer|min:1',
            'items.*.precio_unitario' => 'required|numeric|min:0',
            'items.*.ancho'           => 'nullable|numeric|min:0',
            'items.*.alto'            => 'nullable|numeric|min:0',
            'items.*.largo'           => 'nullable|numeric|min:0',
        ]);

        $cliente  = Cliente::with('listaPrecio')->find($request->cliente_id);
        $lista    = $cliente->listaPrecio;
        $moGlobal = Configuracion::mo();

        $presupuesto = Presupuesto::create([
            'numero'            => Presupuesto::proximoNumero(),
            'cliente_id'        => $request->cliente_id,
            'lista_precio_id'   => $lista?->id,
            'multiplicador'     => $lista?->multiplicador ?? 1,
            'mo_m2'             => $lista?->mo_m2     ?? $moGlobal['m2'],
            'mo_ml'             => $lista?->mo_ml     ?? $moGlobal['ml'],
            'mo_unidad'         => $lista?->mo_unidad ?? $moGlobal['unidad'],
            'estado'            => 'borrador',
            'fecha'             => $request->fecha,
            'fecha_vencimiento' => $request->fecha_vencimiento,
            'observaciones'     => $request->observaciones,
            'nota_interna'      => $request->nota_interna,
            'total'             => 0,
            'created_by'        => auth()->id(),
            'updated_by'        => auth()->id(),
        ]);

        $this->syncItems($presupuesto, $request->items);
        $presupuesto->recalcularTotal();

        return redirect()->route('presupuestos.show', $presupuesto->id)
            ->with('success', 'Presupuesto ' . $presupuesto->numeroFormateado() . ' creado.');
    }

    public function show(Presupuesto $presupuesto)
    {
        $presupuesto->load([
            'cliente', 'listaPrecio', 'items.maquina', 'items.material',
            'ordenTrabajo', 'createdBy', 'updatedBy',
            'facturas', 'remitos',
        ]);
        return view('presupuestos.show', compact('presupuesto'));
    }

    public function edit(Presupuesto $presupuesto)
    {
        $presupuesto->load(['cliente', 'items.maquina', 'items.material']);
        $catalogo  = $this->buildCatalogo();
        $clientes  = Cliente::orderBy('nombre')->get();
        $moGlobal  = Configuracion::mo();

        return view('presupuestos.edit', compact('presupuesto', 'catalogo', 'clientes', 'moGlobal'));
    }

    public function update(Request $request, Presupuesto $presupuesto)
    {
        $request->validate([
            'cliente_id'        => 'required|exists:clientes,id',
            'fecha'             => 'required|date',
            'fecha_vencimiento' => 'nullable|date|after_or_equal:fecha',
            'observaciones'     => 'nullable|string',
            'nota_interna'      => 'nullable|string',
            'items'             => 'required|array|min:1',
            'items.*.descripcion'     => 'required|string|max:1000',
            'items.*.unidad'          => 'required|in:m2,ml,unidad',
            'items.*.cantidad'        => 'required|integer|min:1',
            'items.*.precio_unitario' => 'required|numeric|min:0',
            'items.*.ancho'           => 'nullable|numeric|min:0',
            'items.*.alto'            => 'nullable|numeric|min:0',
            'items.*.largo'           => 'nullable|numeric|min:0',
        ]);

        // Si cambió el cliente, actualizar snapshot de precios
        if ($presupuesto->cliente_id != $request->cliente_id) {
            $cliente  = Cliente::with('listaPrecio')->find($request->cliente_id);
            $lista    = $cliente->listaPrecio;
            $moGlobal = Configuracion::mo();

            $presupuesto->lista_precio_id = $lista?->id;
            $presupuesto->multiplicador   = $lista?->multiplicador ?? 1;
            $presupuesto->mo_m2           = $lista?->mo_m2     ?? $moGlobal['m2'];
            $presupuesto->mo_ml           = $lista?->mo_ml     ?? $moGlobal['ml'];
            $presupuesto->mo_unidad       = $lista?->mo_unidad ?? $moGlobal['unidad'];
        }

        $presupuesto->fill([
            'cliente_id'        => $request->cliente_id,
            'fecha'             => $request->fecha,
            'fecha_vencimiento' => $request->fecha_vencimiento,
            'observaciones'     => $request->observaciones,
            'nota_interna'      => $request->nota_interna,
            'updated_by'        => auth()->id(),
        ])->save();

        // Reemplazar ítems completamente
        $presupuesto->items()->delete();
        $this->syncItems($presupuesto, $request->items);
        $presupuesto->recalcularTotal();

        return redirect()->route('presupuestos.show', $presupuesto->id)
            ->with('success', 'Presupuesto ' . $presupuesto->numeroFormateado() . ' actualizado.');
    }

    public function destroy(Presupuesto $presupuesto)
    {
        $presupuesto->delete();
        return redirect()->route('presupuestos.index')->with('success', 'Presupuesto eliminado.');
    }

    public function cambiarEstado(Request $request, Presupuesto $presupuesto)
    {
        $request->validate(['estado' => 'required|in:borrador,enviado,aprobado,rechazado']);
        $presupuesto->update([
            'estado'     => $request->estado,
            'updated_by' => auth()->id(),
        ]);
        return back()->with('success', 'Estado actualizado a «' . $presupuesto->estadoLabel() . '».');
    }

    public function print(Presupuesto $presupuesto)
    {
        $presupuesto->load(['cliente', 'items.maquina', 'items.material']);
        return view('presupuestos.print', compact('presupuesto'));
    }

    public function convertirAOT(Presupuesto $presupuesto)
    {
        if ($presupuesto->estado !== 'aprobado') {
            return back()->with('error', 'Solo se pueden convertir presupuestos aprobados.');
        }
        if ($presupuesto->orden_trabajo_id) {
            return redirect()->route('ordenes-trabajo.show', $presupuesto->orden_trabajo_id)
                ->with('ok', 'Este presupuesto ya tiene una OT asociada.');
        }

        $ot = OrdenTrabajo::create([
            'cliente_id'    => $presupuesto->cliente_id,
            'estado'        => 'borrador',
            'observaciones' => 'Generada desde presupuesto ' . $presupuesto->numeroFormateado(),
        ]);

        $presupuesto->update(['orden_trabajo_id' => $ot->id]);

        return redirect()->route('ordenes-trabajo.show', $ot->id)
            ->with('success', 'OT #' . $ot->id . ' creada desde ' . $presupuesto->numeroFormateado() . '.');
    }

    public function precioServicio(Request $request)
    {
        $maquina  = Maquina::find($request->maquina_id);
        $material = \App\Models\Material::find($request->material_id);

        if (!$maquina || !$material) {
            return response()->json(['error' => 'No encontrado'], 404);
        }

        $cliente  = Cliente::with('listaPrecio')->find($request->cliente_id);
        $lista    = $cliente?->listaPrecio;
        $moGlobal = Configuracion::mo();

        $mult  = (float)($lista?->multiplicador ?? 1);
        $unidad = $material->unidad ?? 'm2';

        $mo = match($unidad) {
            'ml'     => $lista?->mo_ml     ?? $moGlobal['ml'],
            'unidad' => $lista?->mo_unidad ?? $moGlobal['unidad'],
            default  => $lista?->mo_m2     ?? $moGlobal['m2'],
        };

        [$costoMaq, $costoMat] = match($unidad) {
            'ml'     => [(float)$maquina->costo_ml,     (float)$material->costo_ml],
            'unidad' => [(float)$maquina->costo_unidad, (float)$material->costo_unidad],
            default  => [(float)$maquina->costo_m2,     (float)$material->costo_m2],
        };

        $precio = round(($costoMaq + $costoMat) * $mult + (float)$mo, 2);

        return response()->json([
            'precio_unitario' => $precio,
            'unidad'          => $unidad,
            'descripcion'     => $maquina->nombre . ' — ' . $material->nombre,
            'maquina_id'      => $maquina->id,
            'material_id'     => $material->id,
        ]);
    }

    /**
     * Crea un presupuesto pre-cargado desde uno o varios trabajos de producción
     * (mismo cliente), calculando el precio de cada ítem con la lista del cliente.
     * Redirige al edit para revisar y guardar.
     */
    public function desdeTrabajos(Request $request)
    {
        $ids = (array) $request->input('trabajo_ids', []);
        if ($request->filled('orden_id')) {
            $ids = \App\Models\Trabajo::where('orden_trabajo_id', $request->orden_id)->pluck('id')->all();
        }

        $trabajos = \App\Models\Trabajo::with('maquina', 'material', 'tipoTrabajo')
            ->whereIn('id', $ids)->get();

        if ($trabajos->isEmpty()) {
            return back()->with('error', 'No hay trabajos para presupuestar.');
        }

        $clienteIds = $trabajos->pluck('cliente_id')->filter()->unique();
        if ($clienteIds->count() !== 1) {
            return back()->with('error', 'Los trabajos deben ser de un mismo cliente para presupuestar.');
        }

        $cliente  = Cliente::with('listaPrecio')->find($clienteIds->first());
        $lista    = $cliente?->listaPrecio;
        $mult     = (float) ($lista?->multiplicador ?? 1);
        $moGlobal = Configuracion::mo();

        $presupuesto = Presupuesto::create([
            'numero'          => Presupuesto::proximoNumero(),
            'cliente_id'      => $cliente->id,
            'lista_precio_id' => $lista?->id,
            'multiplicador'   => $mult,
            'mo_m2'           => $lista?->mo_m2     ?? $moGlobal['m2'],
            'mo_ml'           => $lista?->mo_ml     ?? $moGlobal['ml'],
            'mo_unidad'       => $lista?->mo_unidad ?? $moGlobal['unidad'],
            'estado'          => 'borrador',
            'fecha'           => now()->toDateString(),
            'observaciones'   => Presupuesto::CONDICIONES_DEFAULT,
            'total'           => 0,
            'created_by'      => auth()->id(),
        ]);

        foreach ($trabajos as $i => $t) {
            $maquina  = $t->maquina;
            $material = $t->material;
            $unidad   = $t->unidad ?: ($material->unidad ?? 'm2');

            $mo = match ($unidad) {
                'ml'     => $lista?->mo_ml     ?? $moGlobal['ml'],
                'unidad' => $lista?->mo_unidad ?? $moGlobal['unidad'],
                default  => $lista?->mo_m2     ?? $moGlobal['m2'],
            };
            [$cMaq, $cMat] = match ($unidad) {
                'ml'     => [(float) ($maquina->costo_ml ?? 0),     (float) ($material->costo_ml ?? 0)],
                'unidad' => [(float) ($maquina->costo_unidad ?? 0), (float) ($material->costo_unidad ?? 0)],
                default  => [(float) ($maquina->costo_m2 ?? 0),     (float) ($material->costo_m2 ?? 0)],
            };
            $precio = round(($cMaq + $cMat) * $mult + (float) $mo, 2);

            $medida = match ($unidad) {
                'm2'    => (float) $t->ancho * (float) $t->alto * (int) ($t->cantidad ?: 1),
                'ml'    => (float) ($t->largo ?? 0) * (int) ($t->cantidad ?: 1),
                default => (int) ($t->cantidad ?: 1),
            };

            PresupuestoItem::create([
                'presupuesto_id'  => $presupuesto->id,
                'maquina_id'      => $maquina?->id,
                'material_id'     => $material?->id,
                'descripcion'     => $t->descripcion ?: trim(($t->tipoTrabajo->nombre ?? '') . ' ' . ($material->nombre ?? '')) ?: 'Trabajo',
                'unidad'          => $unidad,
                'ancho'           => $t->ancho,
                'alto'            => $t->alto,
                'largo'           => $t->largo,
                'cantidad'        => $t->cantidad ?: 1,
                'precio_unitario' => $precio,
                'subtotal'        => round($precio * $medida, 2),
                'orden'           => $i,
            ]);
        }

        $presupuesto->recalcularTotal();

        return redirect()->route('presupuestos.edit', $presupuesto->id)
            ->with('success', 'Presupuesto pre-cargado desde ' . $trabajos->count() . ' trabajo(s). Revisá los precios y guardá.');
    }

    /**
     * Crea un presupuesto borrador desde uno o varios vehículos ploteados.
     * Cada vehículo genera un ítem (unidad, cantidad 1, precio a completar) con
     * el modelo en la descripción y "Dominio: PATENTE" al final.
     */
    public function desdeVehiculos(Request $request)
    {
        $ids = array_filter((array) $request->input('vehiculo_ids', []));

        $vehiculos = VehiculoPloteo::whereIn('id', $ids)->orderBy('id')->get();
        if ($vehiculos->isEmpty()) {
            return back()->with('error', 'Seleccioná al menos un vehículo para presupuestar.');
        }

        $clienteIds = $vehiculos->pluck('cliente_id')->unique();
        if ($clienteIds->count() !== 1 || $clienteIds->first() === null) {
            return back()->with('error', 'Los vehículos deben ser de un mismo cliente (con cliente asignado) para presupuestar.');
        }

        $cliente  = Cliente::find($clienteIds->first());
        $sectores = VehiculoPloteo::sectores();

        // NO se crea el presupuesto todavía: se precarga el form de create y se
        // guarda recién al confirmar (así, si cancela, no queda borrador huérfano).
        $items = [];
        foreach ($vehiculos as $v) {
            $tipo = $v->tipo_ploteo === 'parcial'
                ? 'Ploteo parcial' . ($v->sector ? ' (' . ($sectores[$v->sector] ?? $v->sector) . ')' : '')
                : 'Ploteo completo';
            $veh  = trim(($v->marca ?? '') . ' ' . ($v->modelo ?? ''));

            $desc = $tipo . ($veh ? ' - ' . $veh : '');
            if ($v->observaciones) $desc .= ' - ' . $v->observaciones;
            $desc .= ' - Dominio: ' . $v->patente;

            $items[] = [
                'descripcion' => \Illuminate\Support\Str::limit($desc, 1000, ''),
                'unidad'      => 'unidad',
                'cantidad'    => 1,
                'precio'      => 0,
            ];
        }

        return redirect()->route('presupuestos.create')->with('presu_prefill', [
            'cliente_id'     => $cliente->id,
            'cliente_nombre' => $cliente->nombre,
            'items'          => $items,
        ]);
    }

    // ── Helpers privados ──────────────────────────────────────────

    private function syncItems(Presupuesto $presupuesto, array $items): void
    {
        foreach ($items as $i => $item) {
            $medida   = $this->calcularMedida($item);
            $subtotal = round((float)$item['precio_unitario'] * $medida, 2);

            PresupuestoItem::create([
                'presupuesto_id'  => $presupuesto->id,
                'maquina_id'      => $item['maquina_id']  ?? null,
                'material_id'     => $item['material_id'] ?? null,
                'descripcion'     => $item['descripcion'],
                'unidad'          => $item['unidad'],
                'ancho'           => $item['ancho']    ?? null,
                'alto'            => $item['alto']     ?? null,
                'largo'           => $item['largo']    ?? null,
                'cantidad'        => $item['cantidad'],
                'precio_unitario' => $item['precio_unitario'],
                'subtotal'        => $subtotal,
                'orden'           => $i,
            ]);
        }
    }

    private function calcularMedida(array $item): float
    {
        return match($item['unidad']) {
            'm2'    => (float)($item['ancho'] ?? 0) * (float)($item['alto'] ?? 0) * (int)($item['cantidad']),
            'ml'    => (float)($item['largo'] ?? 0) * (int)($item['cantidad']),
            default => (int)($item['cantidad']),
        };
    }

    private function buildCatalogo(): array
    {
        // Fuente única del catálogo (compartida con Producción).
        return \App\Services\CatalogoService::items();
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Presupuesto;
use App\Models\PresupuestoItem;
use App\Models\Cliente;
use App\Models\Maquina;
use App\Models\Configuracion;
use App\Models\OrdenTrabajo;

class PresupuestoController extends Controller
{
    public function index()
    {
        $presupuestos = Presupuesto::with(['cliente', 'createdBy', 'updatedBy'])
            ->orderByDesc('numero')
            ->get();

        return view('presupuestos.index', compact('presupuestos'));
    }

    public function create()
    {
        $catalogo  = $this->buildCatalogo();
        $clientes  = Cliente::orderBy('nombre')->get();
        $moGlobal  = Configuracion::mo();

        return view('presupuestos.create', compact('catalogo', 'clientes', 'moGlobal'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'cliente_id'        => 'required|exists:clientes,id',
            'fecha'             => 'required|date',
            'fecha_vencimiento' => 'nullable|date|after_or_equal:fecha',
            'observaciones'     => 'nullable|string',
            'items'             => 'required|array|min:1',
            'items.*.descripcion'     => 'required|string|max:255',
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
            'items'             => 'required|array|min:1',
            'items.*.descripcion'     => 'required|string|max:255',
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
        $catalogo = [];

        // 1) Combos Máquina × Material — precio CALCULADO (como hasta ahora)
        $maquinas = Maquina::with(['tipoTrabajo', 'materiales'])
            ->where('activo', true)->orderBy('nombre')->get();

        foreach ($maquinas as $maq) {
            foreach ($maq->materiales->where('activo', true) as $mat) {
                $catalogo[] = [
                    'fuente'      => 'combo',
                    'grupo'       => $maq->tipoTrabajo?->nombre ?? 'Sin proceso',
                    'label'       => $maq->nombre . ' — ' . $mat->nombre,
                    'descripcion' => $maq->nombre . ' — ' . $mat->nombre,
                    'unidad'      => $mat->unidad ?? 'm2',
                    'maquina_id'  => $maq->id,
                    'material_id' => $mat->id,
                    'producto_id' => null,
                    'precio'      => null,
                    // claves legacy por compatibilidad
                    'tipo'        => $maq->tipoTrabajo?->nombre ?? 'Sin proceso',
                ];
            }
        }

        // 2) Servicios / paquetes (tabla productos) — precio FIJO opcional
        $productos = \App\Models\Producto::with('tipoTrabajo')
            ->where('activo', true)->orderBy('nombre')->get();

        foreach ($productos as $p) {
            $catalogo[] = [
                'fuente'      => 'producto',
                'grupo'       => $p->tipoTrabajo?->nombre ?? 'Otros servicios',
                'label'       => $p->nombre,
                'descripcion' => $p->descripcion ?: $p->nombre,
                'unidad'      => $p->unidad ?? 'm2',
                'maquina_id'  => null,
                'material_id' => null,
                'producto_id' => $p->id,
                'precio'      => $p->precio !== null ? (float) $p->precio : null,
                'tipo'        => $p->tipoTrabajo?->nombre ?? 'Otros servicios',
            ];
        }

        return $catalogo;
    }
}

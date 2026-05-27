<?php

namespace App\Http\Controllers;

use App\Models\OrdenTrabajo;
use App\Models\Trabajo;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\TipoTrabajo;
use App\Models\Material;
use App\Models\Maquina;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrdenTrabajoController extends Controller
{
    public function index(Request $request)
    {
        $query = OrdenTrabajo::with(['cliente', 'trabajos'])
            ->orderByDesc('id');

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('desde')) {
            $query->whereDate('fecha_recibido', '>=', $request->desde);
        }

        if ($request->filled('hasta')) {
            $query->whereDate('fecha_recibido', '<=', $request->hasta);
        }

        $ordenes = $query->paginate(10);

        // OJO: elegí UNA carpeta de vistas. Si tu index está en ordenes-trabajo.index, cambialo acá.
        return view('ordenes.index', compact('ordenes'));
    }

    public function create()
    {
        // Para el create de OT normalmente necesitás clientes + (opcional) productos
        $clientes  = Cliente::orderBy('nombre')->get();
        $productos = Producto::orderBy('nombre')->get();

        // OJO: elegí UNA carpeta de vistas. Si tu create está en ordenes.create, cambialo acá.
        return view('ordenes-trabajo.create', compact('clientes', 'productos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'cliente_id'     => 'required|exists:clientes,id',
            'fecha_recibido' => 'nullable|date',
            'observaciones'  => 'nullable|string',
        ]);

        $orden = OrdenTrabajo::create([
            'cliente_id'     => $request->cliente_id,
            'fecha_recibido' => $request->fecha_recibido ?? now(),
            'estado'         => 'borrador',
            'observaciones'  => $request->observaciones,
            'activo'         => 1,
        ]);

        // Esta ruta en tu route:list existe como: ordenes.trabajos
        return redirect()->route('ordenes.trabajos', $orden->id)
            ->with('success', 'Orden creada. Ahora agregá los trabajos.');
    }

    public function show($id)
    {
        $orden = OrdenTrabajo::with([
            'cliente',
            'trabajos.tipoTrabajo',
            'trabajos.material',
            'trabajos.maquina',
            'trabajos.archivosImprimir',
            'trabajos.referencias',
        ])->findOrFail($id);

        $total      = $orden->trabajos->count();
        $terminados = $orden->trabajos->where('estado', 'terminado')->count();
        $porcentaje = $total > 0 ? round($terminados / $total * 100) : 0;

        return view('ordenes-trabajo.show', compact('orden', 'total', 'terminados', 'porcentaje'));
    }

    public function print($id)
    {
        $orden = OrdenTrabajo::with([
            'cliente',
            'trabajos.tipoTrabajo',
            'trabajos.material',
            'trabajos.maquina',
            'trabajos.archivosImprimir',
            'trabajos.referencias',
        ])->findOrFail($id);

        return view('ordenes-trabajo.print', compact('orden'));
    }

    public function edit($id)
    {
        // La edición de la orden se hace inline desde el show
        return redirect()->route('ordenes-trabajo.show', $id);
    }

    /**
     * Pantalla para cargar trabajos de una OT.
     * Ruta: GET ordenes-trabajo/{id}/trabajos  (en tu route:list aparece como ordenes.trabajos)
     */
    public function trabajos($id)
    {
        $orden      = OrdenTrabajo::with(['cliente', 'trabajos'])->findOrFail($id);
        $tipos      = TipoTrabajo::where('activo', true)->orderBy('nombre')->get();
        $materiales = Material::where('activo', true)->orderBy('nombre')->get();
        $maquinas   = Maquina::where('activo', true)->orderBy('nombre')->get();

        return view('ordenes-trabajo.trabajos', compact('orden', 'tipos', 'materiales', 'maquinas'));
    }

    /**
     * Actualiza solo los metadatos de la orden (observaciones, fecha).
     */
    public function updateMetadata(Request $request, $id)
    {
        $request->validate([
            'observaciones'  => 'nullable|string|max:1000',
            'fecha_recibido' => 'nullable|date',
        ]);

        $orden = OrdenTrabajo::findOrFail($id);
        $orden->update([
            'observaciones'  => $request->observaciones,
            'fecha_recibido' => $request->fecha_recibido,
        ]);

        return redirect()
            ->route('ordenes-trabajo.show', $id)
            ->with('success', 'Orden actualizada.');
    }

    public function update(Request $request, $id)
    {
        $orden = OrdenTrabajo::with('trabajos')->findOrFail($id);

        $request->validate([
            'cliente_id'     => 'required|exists:clientes,id',
            'fecha_recibido' => 'nullable|date',
            'observaciones'  => 'nullable|string',

            // trabajos puede venir vacío, pero si viene, validamos estructura
            'trabajos'                 => 'nullable|array',
            'trabajos.*.id'            => 'nullable|integer|exists:trabajos,id',
            'trabajos.*.producto_id'   => 'nullable|exists:productos,id',
            'trabajos.*.tipo'          => 'nullable|string',
            'trabajos.*.descripcion'   => 'nullable|string',
            'trabajos.*.ancho'         => 'nullable|numeric|min:0',
            'trabajos.*.alto'          => 'nullable|numeric|min:0',
            'trabajos.*.cantidad'      => 'nullable|integer|min:1',
            'trabajos.*.fecha_entrega' => 'nullable|date',
        ]);

        // 1) actualizar datos de la orden
        $orden->update([
            'cliente_id'     => $request->cliente_id,
            'fecha_recibido' => $request->fecha_recibido,
            'observaciones'  => $request->observaciones,
        ]);

        // 2) sincronizar trabajos (update existentes + crear nuevos + borrar los que ya no están)
        $idsRecibidos = [];

        if (is_array($request->trabajos)) {
            foreach ($request->trabajos as $trabajoData) {

                // Si viene id => editar
                if (!empty($trabajoData['id'])) {
                    $trabajo = $orden->trabajos->firstWhere('id', (int)$trabajoData['id']);
                    if ($trabajo) {
                        $trabajo->fill([
                            'producto_id'   => $trabajoData['producto_id'] ?? $trabajo->producto_id,
                            'tipo'          => $trabajoData['tipo'] ?? $trabajo->tipo,
                            'descripcion'   => $trabajoData['descripcion'] ?? $trabajo->descripcion,
                            'ancho'         => $trabajoData['ancho'] ?? $trabajo->ancho,
                            'alto'          => $trabajoData['alto'] ?? $trabajo->alto,
                            'cantidad'      => $trabajoData['cantidad'] ?? $trabajo->cantidad,
                            'fecha_entrega' => $trabajoData['fecha_entrega'] ?? $trabajo->fecha_entrega,
                        ]);
                        $trabajo->save();
                        $idsRecibidos[] = $trabajo->id;
                    }
                    continue;
                }

                // Si NO viene id => crear nuevo
                // Reglas mínimas para crear (sin pedir "todo" obligatorio)
                $tieneAlgo = !empty($trabajoData['producto_id']) || !empty($trabajoData['descripcion']);
                if (!$tieneAlgo) {
                    continue; // fila vacía en el form
                }

                // cantidad default
                $cantidad = isset($trabajoData['cantidad']) && (int)$trabajoData['cantidad'] > 0
                    ? (int)$trabajoData['cantidad']
                    : 1;

                $nuevo = $orden->trabajos()->create([
                    'producto_id'   => $trabajoData['producto_id'] ?? null,
                    'tipo'          => $trabajoData['tipo'] ?? null,
                    'descripcion'   => $trabajoData['descripcion'] ?? null,
                    'ancho'         => $trabajoData['ancho'] ?? null,
                    'alto'          => $trabajoData['alto'] ?? null,
                    'cantidad'      => $cantidad,
                    'fecha_entrega' => $trabajoData['fecha_entrega'] ?? null,
                    'estado'        => 'pendiente',
                ]);

                $idsRecibidos[] = $nuevo->id;
            }
        }

        // 3) borrar los trabajos que ya no vinieron en el request (si el form manda la lista completa)
        // Si tu UI NO manda todos, comentá este bloque.
        if (!empty($idsRecibidos)) {
            $orden->trabajos()->whereNotIn('id', $idsRecibidos)->delete();
        }

        // 4) fotos (mantengo tu lógica, pero ojo: requiere relación fotos() en OrdenTrabajo)
        if ($request->hasFile('fotos')) {
            $limite = 5;
            $cantidadActual = method_exists($orden, 'fotos') ? $orden->fotos()->count() : 0;

            $cantidadDisponible = max(0, $limite - $cantidadActual);
            $fotosNuevas = $request->file('fotos');
            $fotosPermitidas = array_slice($fotosNuevas, 0, $cantidadDisponible);

            if (method_exists($orden, 'fotos')) {
                foreach ($fotosPermitidas as $foto) {
                    $ruta = $foto->store('ordenes_fotos', 'public');
                    $orden->fotos()->create(['ruta' => $ruta]);
                }
            } else {
                Log::warning('Se intentó subir fotos pero OrdenTrabajo no tiene relación fotos()');
            }

            if (count($fotosNuevas) > $cantidadDisponible) {
                return back()->with('error', "Solo se permiten hasta {$limite} fotos por orden.");
            }
        }

        return redirect()->route('ordenes-trabajo.index')
            ->with('success', 'Orden actualizada correctamente.');
    }

    public function cambiarEstado(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required|in:borrador,en_produccion,lista,entregada,cancelada',
        ]);

        $orden = OrdenTrabajo::findOrFail($id);
        $orden->estado = $request->estado;
        $orden->save();

        return redirect()->back()->with('success', 'Estado actualizado.');
    }

    public function marcarTerminado($id)
    {
        $trabajo = Trabajo::findOrFail($id);
        $trabajo->estado = 'terminado';
        $trabajo->save();

        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        $orden = OrdenTrabajo::findOrFail($id);
        $orden->delete();

        return redirect()->route('ordenes-trabajo.index')
            ->with('success', 'Orden de trabajo eliminada.');
    }
}

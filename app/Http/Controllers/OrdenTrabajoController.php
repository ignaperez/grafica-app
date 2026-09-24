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

    /*
     * edit() y update() se eliminaron el 2026-09-23.
     *
     * edit() solo redirigia al show (la edicion de la OT es inline, via
     * ordenes-trabajo.metadata). update() era codigo muerto pero ruteado por el
     * Route::resource, y BORRABA los trabajos de la orden que no vinieran en el
     * request. Ninguna vista viva los usaba. Se sacaron tambien del resource
     * (->except(['edit','update'])) para que no queden accesibles por URL.
     */

    /**
     * Pantalla para cargar trabajos de una OT.
     * Ruta: GET ordenes-trabajo/{id}/trabajos  (en tu route:list aparece como ordenes.trabajos)
     */
    public function trabajos($id)
    {
        $orden    = OrdenTrabajo::with(['cliente', 'trabajos'])->findOrFail($id);
        $catalogo = \App\Services\CatalogoService::items();

        return view('ordenes-trabajo.trabajos', compact('orden', 'catalogo'));
    }

    /**
     * Actualiza solo los metadatos de la orden (observaciones, fecha).
     */
    public function updateMetadata(Request $request, $id)
    {
        $request->validate([
            'cliente_id'     => 'required|exists:clientes,id',
            'observaciones'  => 'nullable|string|max:1000',
            'fecha_recibido' => 'nullable|date',
        ]);

        $orden = OrdenTrabajo::findOrFail($id);
        $orden->update([
            'cliente_id'     => $request->cliente_id,
            'observaciones'  => $request->observaciones,
            // Si el campo viene vacío se conserva la fecha actual: el editor
            // inline es para corregir datos, no para borrarlos sin querer.
            'fecha_recibido' => $request->fecha_recibido ?: $orden->fecha_recibido,
        ]);

        return redirect()
            ->route('ordenes-trabajo.show', $id)
            ->with('success', 'Orden actualizada.');
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

<?php

namespace App\Http\Controllers;

use App\Models\Trabajo;
use App\Models\OrdenTrabajo;
use App\Models\Producto;
use App\Models\TipoTrabajo;
use App\Models\Material;
use App\Models\Maquina;
use App\Models\TrabajoArchivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TrabajoController extends Controller
{
    /**
     * Crea un trabajo individual para una orden.
     * Unifica la lógica: siempre usa producto_id y calcula el precio
     * según la lista de precios del cliente.
     */
    public function store(Request $request)
    {
        $request->validate([
            'orden_trabajo_id' => 'required|exists:orden_trabajos,id',
            'producto_id'      => 'required|exists:productos,id',
            'cantidad'         => 'required|integer|min:1',
            'descripcion'      => 'nullable|string',
            'fecha_entrega'    => 'nullable|date',
            'ancho'            => 'required|numeric|min:0.01',
            'alto'             => 'required|numeric|min:0.01',
        ]);

        $orden    = OrdenTrabajo::with('cliente.listaPrecio')->findOrFail($request->orden_trabajo_id);
        $producto = Producto::findOrFail($request->producto_id);

        $multiplicador  = $orden->cliente->listaPrecio->multiplicador ?? 1;
        $precio_unitario = round($producto->precio * $multiplicador, 2);

        Trabajo::create([
            'orden_trabajo_id' => $orden->id,
            'producto_id'      => $producto->id,
            'tipo'             => $producto->tipo,
            'descripcion'      => $request->descripcion,
            'cantidad'         => $request->cantidad,
            'fecha_entrega'    => $request->fecha_entrega,
            'ancho'            => $request->ancho,
            'alto'             => $request->alto,
            'precio_unitario'  => $precio_unitario,
            'estado'           => 'pendiente',
        ]);

        $productos = Producto::orderBy('nombre')->get();

        return redirect()->route('ordenes.trabajos', ['orden' => $orden->id])
            ->with('success', 'Trabajo agregado.');
    }

    public function show($id)
    {
        $trabajo = Trabajo::with('producto', 'orden.cliente')->findOrFail($id);
        return view('trabajos.show', compact('trabajo'));
    }

    public function edit($id)
    {
        $trabajo = Trabajo::with([
            'cliente', 'tipoTrabajo', 'material', 'maquina',
            'orden.cliente', 'archivosImprimir', 'referencias',
        ])->findOrFail($id);

        $tipos      = TipoTrabajo::where('activo', true)->orderBy('nombre')->get();
        $materiales = Material::where('activo', true)->orderBy('nombre')->get();
        $maquinas   = Maquina::where('activo', true)->orderBy('nombre')->get();

        return view('trabajos.edit', compact('trabajo', 'tipos', 'materiales', 'maquinas'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'cliente_id'      => 'required|exists:clientes,id',
            'tipo_trabajo_id' => 'nullable|exists:tipo_trabajos,id',
            'material_id'     => 'nullable|exists:materiales,id',
            'maquina_id'      => 'nullable|exists:maquinas,id',
            'descripcion'     => 'nullable|string',
            'ancho'           => 'nullable|numeric|min:0',
            'alto'            => 'nullable|numeric|min:0',
            'cantidad'        => 'required|integer|min:1',
            'fecha_entrega'   => 'nullable|date',
        ]);

        $trabajo = Trabajo::findOrFail($id);

        $trabajo->update([
            'cliente_id'      => $request->cliente_id,
            'tipo_trabajo_id' => $request->tipo_trabajo_id,
            'material_id'     => $request->material_id,
            'maquina_id'      => $request->maquina_id,
            'descripcion'     => $request->descripcion,
            'ancho'           => $request->ancho,
            'alto'            => $request->alto,
            'cantidad'        => $request->cantidad,
            'fecha_entrega'   => $request->fecha_entrega,
        ]);

        // Redirigir según origen
        if ($trabajo->orden_trabajo_id) {
            return redirect()
                ->route('ordenes-trabajo.show', $trabajo->orden_trabajo_id)
                ->with('success', 'Trabajo actualizado correctamente.');
        }

        return redirect()
            ->route('trabajos-libres.index')
            ->with('success', 'Trabajo actualizado correctamente.');
    }

    /**
     * Guarda múltiples trabajos de una vez para una orden (formato nuevo).
     */
    public function storeMultiples(Request $request)
    {
        $request->validate([
            'orden_trabajo_id'           => 'required|exists:orden_trabajos,id',
            'trabajos'                   => 'required|array|min:1',
            'trabajos.*.tipo_trabajo_id' => 'nullable|exists:tipo_trabajos,id',
            'trabajos.*.material_id'     => 'nullable|exists:materiales,id',
            'trabajos.*.maquina_id'      => 'nullable|exists:maquinas,id',
            'trabajos.*.descripcion'     => 'nullable|string',
            'trabajos.*.ancho'           => 'nullable|numeric|min:0',
            'trabajos.*.alto'            => 'nullable|numeric|min:0',
            'trabajos.*.cantidad'        => 'required|integer|min:1',
            'trabajos.*.fecha_entrega'   => 'nullable|date',
        ]);

        $orden         = OrdenTrabajo::findOrFail($request->orden_trabajo_id);
        $todosArchivos = $request->allFiles()['trabajos'] ?? [];

        foreach ($request->trabajos as $idx => $item) {
            $trabajo = Trabajo::create([
                'orden_trabajo_id' => $orden->id,
                'cliente_id'       => $orden->cliente_id,
                'tipo_trabajo_id'  => $item['tipo_trabajo_id'] ?? null,
                'material_id'      => $item['material_id']     ?? null,
                'maquina_id'       => $item['maquina_id']      ?? null,
                'descripcion'      => $item['descripcion']     ?? null,
                'ancho'            => $item['ancho']           ?? null,
                'alto'             => $item['alto']            ?? null,
                'cantidad'         => $item['cantidad'],
                'fecha_entrega'    => $item['fecha_entrega']   ?? null,
                'estado'           => 'pendiente',
                'fecha_carga'      => now(),
            ]);

            $this->guardarArchivos($trabajo->id, $todosArchivos[$idx]['archivos_imprimir'] ?? [], 'imprimir');
            $this->guardarArchivos($trabajo->id, $todosArchivos[$idx]['referencias']       ?? [], 'referencia');
        }

        return redirect()
            ->route('ordenes-trabajo.show', $orden->id)
            ->with('success', 'Trabajos guardados correctamente.');
    }

    /**
     * Guarda archivos adjuntos de un trabajo.
     */
    private function guardarArchivos(int $trabajoId, array $files, string $tipo): void
    {
        $permitidas = [
            'jpg','jpeg','png','gif','bmp','webp','tif','tiff',
            'pdf','ai','eps','svg','psd','cdr','indd',
        ];

        foreach ($files as $file) {
            if (!$file->isValid()) continue;
            $ext = strtolower($file->getClientOriginalExtension());
            if (!in_array($ext, $permitidas)) continue;

            $nombre = Str::uuid() . '.' . $ext;
            $ruta   = $file->storeAs("trabajo_archivos/{$trabajoId}", $nombre, 'public');

            TrabajoArchivo::create([
                'trabajo_id'      => $trabajoId,
                'tipo'            => $tipo,
                'nombre_original' => $file->getClientOriginalName(),
                'ruta'            => $ruta,
                'mime_type'       => $file->getMimeType(),
                'tamanio'         => $file->getSize(),
            ]);
        }
    }

    /**
     * Guarda un trabajo vía AJAX (desde el formulario dinámico).
     */
    public function ajaxStore(Request $request)
    {
        $request->validate([
            'orden_trabajo_id' => 'required|exists:orden_trabajos,id',
            'producto_id'      => 'required|exists:productos,id',
            'ancho'            => 'required|numeric|min:0.01',
            'alto'             => 'required|numeric|min:0.01',
            'cantidad'         => 'required|integer|min:1',
            'descripcion'      => 'nullable|string',
        ]);

        $orden    = OrdenTrabajo::with('cliente.listaPrecio')->findOrFail($request->orden_trabajo_id);
        $producto = Producto::findOrFail($request->producto_id);

        $multiplicador   = $orden->cliente->listaPrecio->multiplicador ?? 1;
        $precio_unitario = round($producto->precio * $multiplicador, 2);

        $trabajo = Trabajo::create([
            'orden_trabajo_id' => $orden->id,
            'producto_id'      => $producto->id,
            'tipo'             => $producto->tipo,
            'descripcion'      => $request->descripcion,
            'ancho'            => $request->ancho,
            'alto'             => $request->alto,
            'cantidad'         => $request->cantidad,
            'precio_unitario'  => $precio_unitario,
            'estado'           => 'pendiente',
        ]);

        return response()->json([
            'success' => true,
            'trabajo' => $trabajo,
        ]);
    }

    /**
     * Elimina un trabajo.
     */
    public function destroy($id)
    {
        $trabajo = Trabajo::findOrFail($id);
        $ordenId = $trabajo->orden_trabajo_id;
        $trabajo->delete();

        return redirect()->route('ordenes.trabajos', ['orden' => $ordenId])
            ->with('success', 'Trabajo eliminado.');
    }

    /**
     * Marca un trabajo como terminado y redirige de vuelta.
     */
    public function marcarTerminado($id)
    {
        $trabajo         = Trabajo::findOrFail($id);
        $trabajo->estado = 'terminado';
        $trabajo->save();

        if ($trabajo->orden_trabajo_id) {
            return redirect()
                ->route('ordenes-trabajo.show', $trabajo->orden_trabajo_id)
                ->with('success', 'Trabajo marcado como terminado.');
        }

        return redirect()->back()->with('success', 'Trabajo marcado como terminado.');
    }

    /**
     * Cambia el estado de un trabajo (permite revertir "terminado").
     */
    public function cambiarEstado(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required|in:pendiente,en_produccion,terminado',
        ]);

        $trabajo         = Trabajo::findOrFail($id);
        $trabajo->estado = $request->estado;
        $trabajo->save();

        if ($trabajo->orden_trabajo_id) {
            return redirect()
                ->route('ordenes-trabajo.show', $trabajo->orden_trabajo_id)
                ->with('success', 'Estado del trabajo actualizado.');
        }

        return redirect()->back()->with('success', 'Estado del trabajo actualizado.');
    }
}

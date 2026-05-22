<?php

namespace App\Http\Controllers;

use App\Models\Trabajo;
use App\Models\OrdenTrabajo;
use App\Models\Cliente;
use App\Models\TipoTrabajo;
use App\Models\Material;
use App\Models\Maquina;
use App\Models\TrabajoArchivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TrabajoLibreController extends Controller
{
    /**
     * Lista todos los trabajos sin asignar a una orden.
     */
    public function index(Request $request)
    {
        $query = Trabajo::with(['cliente', 'tipoTrabajo', 'material', 'maquina'])
            ->whereNull('orden_trabajo_id')
            ->orderByDesc('id');

        if ($request->filled('cliente_id')) {
            $query->where('cliente_id', $request->cliente_id);
        }
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $trabajos  = $query->paginate(20)->withQueryString();
        $clientes  = Cliente::orderBy('nombre')->get();

        return view('trabajos-libres.index', compact('trabajos', 'clientes'));
    }

    /**
     * Formulario para cargar uno o varios trabajos sin orden.
     */
    public function create()
    {
        $clientes    = Cliente::orderBy('nombre')->get();
        $tipos       = TipoTrabajo::where('activo', true)->orderBy('nombre')->get();
        $materiales  = Material::where('activo', true)->orderBy('nombre')->get();
        $maquinas    = Maquina::where('activo', true)->orderBy('nombre')->get();

        return view('trabajos-libres.create', compact('clientes', 'tipos', 'materiales', 'maquinas'));
    }

    /**
     * Guarda uno o varios trabajos sin orden, con sus archivos adjuntos.
     */
    public function store(Request $request)
    {
        $request->validate([
            'trabajos'                   => 'required|array|min:1',
            'trabajos.*.cliente_id'      => 'required|exists:clientes,id',
            'trabajos.*.tipo_trabajo_id' => 'nullable|exists:tipo_trabajos,id',
            'trabajos.*.material_id'     => 'nullable|exists:materiales,id',
            'trabajos.*.maquina_id'      => 'nullable|exists:maquinas,id',
            'trabajos.*.descripcion'     => 'nullable|string',
            'trabajos.*.ancho'           => 'nullable|numeric|min:0',
            'trabajos.*.alto'            => 'nullable|numeric|min:0',
            'trabajos.*.cantidad'        => 'required|integer|min:1',
            'trabajos.*.fecha_entrega'   => 'nullable|date',
            'trabajos.*.observaciones'   => 'nullable|string',
        ]);

        $todosArchivos = $request->allFiles()['trabajos'] ?? [];

        foreach ($request->trabajos as $idx => $item) {
            $trabajo = Trabajo::create([
                'orden_trabajo_id' => null,
                'cliente_id'       => $item['cliente_id'],
                'tipo_trabajo_id'  => $item['tipo_trabajo_id'] ?? null,
                'material_id'      => $item['material_id'] ?? null,
                'maquina_id'       => $item['maquina_id'] ?? null,
                'descripcion'      => $item['descripcion'] ?? null,
                'ancho'            => $item['ancho'] ?? null,
                'alto'             => $item['alto'] ?? null,
                'cantidad'         => $item['cantidad'],
                'fecha_entrega'    => $item['fecha_entrega'] ?? null,
                'estado'           => 'pendiente',
                'fecha_carga'      => now(),
            ]);

            // Guardar archivos de esta fila
            $archivosImprimir = $todosArchivos[$idx]['archivos_imprimir'] ?? [];
            $referencias      = $todosArchivos[$idx]['referencias']       ?? [];

            $this->guardarArchivos($trabajo->id, $archivosImprimir, 'imprimir');
            $this->guardarArchivos($trabajo->id, $referencias,      'referencia');
        }

        return redirect()->route('trabajos-libres.index')
            ->with('success', 'Trabajo(s) guardado(s) correctamente.');
    }

    private function guardarArchivos(int $trabajoId, array $files, string $tipo): void
    {
        $extensionesPermitidas = [
            'jpg','jpeg','png','gif','bmp','webp','tif','tiff',
            'pdf','ai','eps','svg','psd','cdr','indd',
        ];

        foreach ($files as $file) {
            if (!$file->isValid()) continue;

            $ext = strtolower($file->getClientOriginalExtension());
            if (!in_array($ext, $extensionesPermitidas)) continue;

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
     * Asigna trabajos seleccionados a una orden existente o crea una nueva.
     * Regla: todos los trabajos seleccionados deben ser del mismo cliente.
     */
    public function asignarOrden(Request $request)
    {
        $request->validate([
            'trabajo_ids'   => 'required|array|min:1',
            'trabajo_ids.*' => 'exists:trabajos,id',
            'accion'        => 'required|in:nueva,existente',
            'orden_id'      => 'required_if:accion,existente|nullable|exists:orden_trabajos,id',
            'observaciones' => 'nullable|string',
        ]);

        // Cargar los trabajos seleccionados (solo los que aún no tienen orden)
        $trabajos = Trabajo::whereIn('id', $request->trabajo_ids)
            ->whereNull('orden_trabajo_id')
            ->get();

        if ($trabajos->isEmpty()) {
            return back()->with('error', 'Ninguno de los trabajos seleccionados está disponible para asignar.');
        }

        // Validar que todos pertenezcan al mismo cliente
        $clienteIds = $trabajos->pluck('cliente_id')->unique();

        if ($clienteIds->count() > 1) {
            return back()->with('error',
                'No podés asignar trabajos de distintos clientes a la misma orden. ' .
                'Seleccioná solo trabajos del mismo cliente.'
            );
        }

        $clienteId = $clienteIds->first();

        if ($request->accion === 'nueva') {
            // La orden toma el cliente de los trabajos
            $orden = OrdenTrabajo::create([
                'cliente_id'     => $clienteId,
                'fecha_recibido' => now(),
                'estado'         => 'borrador',
                'observaciones'  => $request->observaciones,
                'activo'         => 1,
            ]);
        } else {
            $orden = OrdenTrabajo::findOrFail($request->orden_id);

            // Verificar que la orden existente sea del mismo cliente
            if ($orden->cliente_id !== $clienteId) {
                $nombreCliente = $trabajos->first()->cliente->nombre ?? "ID $clienteId";
                return back()->with('error',
                    "La orden #{$orden->id} pertenece a otro cliente. " .
                    "Los trabajos seleccionados son de «{$nombreCliente}»."
                );
            }
        }

        $trabajos->each(fn ($t) => $t->update(['orden_trabajo_id' => $orden->id]));

        return redirect()->route('ordenes-trabajo.show', $orden->id)
            ->with('success', 'Trabajos asignados a la Orden #' . $orden->id . ' correctamente.');
    }
}

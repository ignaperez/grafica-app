<?php

namespace App\Http\Controllers;

use App\Models\VehiculoPloteo;
use App\Models\VehiculoArchivo;
use App\Models\OrdenTrabajo;
use App\Models\Cliente;
use App\Models\Marca;
use App\Models\ModeloVehiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VehiculoPloteoController extends Controller
{
    private const FOTOS = [
        'foto_antes_frente', 'foto_antes_atras', 'foto_antes_izq', 'foto_antes_der',
        'foto_despues_frente', 'foto_despues_atras', 'foto_despues_izq', 'foto_despues_der',
    ];

    private const ARCHIVOS = ['refe'];

    /** Normaliza patente: sin espacios y en mayúsculas. */
    private function normalizarPatente(?string $p): string
    {
        return strtoupper(preg_replace('/\s+/', '', (string) $p));
    }

    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $vehiculos = VehiculoPloteo::with(['orden.cliente', 'cliente', 'presupuesto'])
            ->when($q !== '', function ($query) use ($q) {
                $pat = $this->normalizarPatente($q);
                $query->where(function ($sub) use ($q, $pat) {
                    $sub->where('patente', 'like', "%{$pat}%")
                        ->orWhere('marca', 'like', "%{$q}%")
                        ->orWhere('modelo', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('vehiculos-ploteo.index', compact('vehiculos', 'q'));
    }

    /** Descarga el listado de vehículos como Excel (respeta la búsqueda ?q=). */
    public function exportar(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $vehiculos = VehiculoPloteo::with(['orden.cliente', 'cliente', 'presupuesto'])
            ->when($q !== '', function ($query) use ($q) {
                $pat = $this->normalizarPatente($q);
                $query->where(function ($sub) use ($q, $pat) {
                    $sub->where('patente', 'like', "%{$pat}%")
                        ->orWhere('marca', 'like', "%{$q}%")
                        ->orWhere('modelo', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('id')
            ->get();

        $html = view('vehiculos-ploteo.export', compact('vehiculos'))->render();

        return response($html, 200, [
            'Content-Type'        => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="vehiculos_' . now()->format('Y-m-d') . '.xls"',
        ]);
    }

    /**
     * Chequea si una patente ya fue cargada (aviso "ya estuvo en la gráfica").
     * GET /vehiculos-ploteo/patente-existe?patente=XXX&ignore=ID
     */
    public function patenteExiste(Request $request)
    {
        $pat = $this->normalizarPatente($request->query('patente'));
        if (strlen($pat) < 2) {
            return response()->json(['existe' => false]);
        }

        $query = VehiculoPloteo::where('patente', $pat);
        if ($request->filled('ignore')) {
            $query->where('id', '!=', $request->query('ignore'));
        }

        $count  = (clone $query)->count();
        $ultimo = (clone $query)->orderByDesc('fecha_ploteo')->first();

        return response()->json([
            'existe' => $count > 0,
            'count'  => $count,
            'ultima' => $ultimo?->fecha_ploteo?->format('d/m/Y'),
        ]);
    }

    public function create(Request $request)
    {
        $orden = $request->orden_id
            ? OrdenTrabajo::find($request->orden_id)
            : null;

        $ordenes  = OrdenTrabajo::with('cliente')
            ->whereIn('estado', ['borrador', 'en_produccion'])
            ->orderByDesc('id')
            ->get();

        $clientes = Cliente::orderBy('nombre')->get();
        $marcas   = Marca::where('activo', true)->orderBy('nombre')->get();

        return view('vehiculos-ploteo.create', compact('orden', 'ordenes', 'clientes', 'marcas'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'patente'          => 'required|string|max:20',
            'marca_id'         => 'required|exists:marcas,id',
            'modelo_id'        => 'required|exists:modelos_vehiculo,id',
            'fecha_ploteo'     => 'nullable|date',
            'observaciones'    => 'nullable|string',
            'orden_trabajo_id' => 'nullable|exists:orden_trabajos,id',
            'cliente_id'       => 'nullable|exists:clientes,id',
            'tipo_ploteo'      => 'required|in:completo,parcial',
            'sector'           => 'nullable|string',
        ]);

        $data = $this->prepararMarcaModelo($data);

        if ($data['tipo_ploteo'] === 'completo') {
            $data['sector'] = null;
        }

        foreach (array_merge(self::FOTOS, self::ARCHIVOS) as $campo) {
            if ($request->hasFile($campo)) {
                $data[$campo] = $request->file($campo)
                    ->store('vehiculos', 'public');
            }
        }

        $vehiculo = VehiculoPloteo::create($data);

        $this->guardarReferencias($request, $vehiculo);

        return redirect()->route('vehiculos-ploteo.show', $vehiculo->id)
            ->with('success', 'Vehículo registrado correctamente.');
    }

    /**
     * Normaliza patente, valida que el modelo pertenezca a la marca y
     * completa el texto legacy (marca / modelo) desde las FKs.
     */
    private function prepararMarcaModelo(array $data): array
    {
        $data['patente'] = $this->normalizarPatente($data['patente']);

        $marca  = Marca::find($data['marca_id']);
        $modelo = ModeloVehiculo::find($data['modelo_id']);

        // El modelo debe pertenecer a la marca elegida.
        if ($modelo && (int) $modelo->marca_id !== (int) $data['marca_id']) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'modelo_id' => 'El modelo no corresponde a la marca elegida.',
            ]);
        }

        $data['marca']  = $marca?->nombre;
        $data['modelo'] = $modelo?->nombre;

        return $data;
    }

    public function show(VehiculoPloteo $vehiculosPloteo)
    {
        return view('vehiculos-ploteo.show', ['vehiculo' => $vehiculosPloteo->load(['orden.cliente', 'cliente', 'presupuesto'])]);
    }

    public function edit(VehiculoPloteo $vehiculosPloteo)
    {
        $ordenes  = OrdenTrabajo::with('cliente')
            ->whereIn('estado', ['borrador', 'en_produccion'])
            ->orderByDesc('id')
            ->get();

        $clientes = Cliente::orderBy('nombre')->get();
        $marcas   = Marca::where('activo', true)->orderBy('nombre')->get();

        return view('vehiculos-ploteo.edit', [
            'vehiculo' => $vehiculosPloteo,
            'ordenes'  => $ordenes,
            'clientes' => $clientes,
            'marcas'   => $marcas,
        ]);
    }

    public function update(Request $request, VehiculoPloteo $vehiculosPloteo)
    {
        $data = $request->validate([
            'patente'          => 'required|string|max:20',
            'marca_id'         => 'required|exists:marcas,id',
            'modelo_id'        => 'required|exists:modelos_vehiculo,id',
            'fecha_ploteo'     => 'nullable|date',
            'observaciones'    => 'nullable|string',
            'orden_trabajo_id' => 'nullable|exists:orden_trabajos,id',
            'cliente_id'       => 'nullable|exists:clientes,id',
            'tipo_ploteo'      => 'required|in:completo,parcial',
            'sector'           => 'nullable|string',
        ]);

        $data = $this->prepararMarcaModelo($data);

        if ($data['tipo_ploteo'] === 'completo') {
            $data['sector'] = null;
        }

        foreach (array_merge(self::FOTOS, self::ARCHIVOS) as $campo) {
            if ($request->hasFile($campo)) {
                if ($vehiculosPloteo->$campo) {
                    Storage::disk('public')->delete($vehiculosPloteo->$campo);
                }
                $data[$campo] = $request->file($campo)->store('vehiculos', 'public');
            }
        }

        $vehiculosPloteo->update($data);

        $this->guardarReferencias($request, $vehiculosPloteo);

        return redirect()->route('vehiculos-ploteo.show', $vehiculosPloteo->id)
            ->with('success', 'Vehículo actualizado.');
    }

    public function destroy(VehiculoPloteo $vehiculosPloteo)
    {
        $vehiculosPloteo->delete();

        return redirect()->route('vehiculos-ploteo.index')
            ->with('success', 'Vehículo eliminado.');
    }

    /**
     * Sirve una foto/archivo del vehículo desde el storage del tenant.
     * (Los archivos viven en storage/tenant{id}/... y el symlink /storage
     * central no los alcanza → se sirven por ruta de la app, detrás de login.)
     */
    /**
     * Guarda las imágenes / archivos de referencia que vengan en el request.
     * Son varios: el que plotea necesita frente, lateral, detalle del logo, etc.
     */
    private function guardarReferencias(Request $request, VehiculoPloteo $vehiculo): void
    {
        $archivos = $request->file('referencias', []);

        if (! $archivos) {
            return;
        }

        // Se valida aparte del validate() principal: si entrara ahí, 'referencias'
        // caería en $data y el create()/update() fallaría por mass assignment.
        $request->validate([
            'referencias.*' => 'file|mimes:jpg,jpeg,png,gif,webp,bmp,pdf,ai,eps,svg,psd,cdr|max:51200',
        ]);

        $orden = (int) $vehiculo->referencias()->max('orden');

        foreach ($archivos as $file) {
            if (! $file || ! $file->isValid()) {
                continue;
            }

            $vehiculo->referencias()->create([
                'nombre_original' => $file->getClientOriginalName(),
                'ruta'            => $file->store('vehiculos/referencias', 'public'),
                'mime_type'       => $file->getMimeType(),
                'tamanio'         => $file->getSize(),
                'orden'           => ++$orden,
            ]);
        }
    }

    /** Ficha A4 del vehículo para el taller: todos los datos, referencias y fotos. */
    public function print(VehiculoPloteo $vehiculosPloteo)
    {
        $vehiculosPloteo->load(['cliente', 'presupuesto', 'orden', 'referencias']);

        return view('vehiculos-ploteo.print', ['vehiculo' => $vehiculosPloteo]);
    }

    /** Sirve una referencia desde el storage del tenant (ver VehiculoArchivo::url). */
    public function archivo(VehiculoArchivo $archivo)
    {
        abort_if(! $archivo->ruta || ! Storage::disk('public')->exists($archivo->ruta), 404);

        return Storage::disk('public')->response($archivo->ruta, $archivo->nombre_original);
    }

    public function destroyArchivo(VehiculoArchivo $archivo)
    {
        $vehiculoId = $archivo->vehiculo_ploteo_id;
        $archivo->delete();

        return redirect()->route('vehiculos-ploteo.show', $vehiculoId)
            ->with('success', 'Referencia eliminada.');
    }

    public function foto(VehiculoPloteo $vehiculosPloteo, string $campo)
    {
        abort_unless(in_array($campo, array_merge(self::FOTOS, self::ARCHIVOS), true), 404);

        $ruta = $vehiculosPloteo->$campo;
        abort_if(!$ruta || !Storage::disk('public')->exists($ruta), 404);

        return Storage::disk('public')->response($ruta);
    }

    /** Marca el vehículo como presupuestado a mano (para los viejos sin vínculo). */
    public function marcarPresupuestado(VehiculoPloteo $vehiculosPloteo)
    {
        abort_unless(auth()->user()->puedeModulo('presupuestos'), 403);
        $vehiculosPloteo->update(['presupuestado_manual' => true]);

        return back()->with('success', 'Vehículo marcado como presupuestado.');
    }

    /** Quita la marca de presupuestado (manual o el vínculo con el presupuesto). */
    public function desmarcarPresupuestado(VehiculoPloteo $vehiculosPloteo)
    {
        abort_unless(auth()->user()->puedeModulo('presupuestos'), 403);
        $vehiculosPloteo->update(['presupuesto_id' => null, 'presupuestado_manual' => false]);

        return back()->with('success', 'Se quitó la marca de presupuestado.');
    }

    public function destroyFoto(Request $request, VehiculoPloteo $vehiculosPloteo)
    {
        $todos  = array_merge(self::FOTOS, self::ARCHIVOS);
        $campo  = $request->validate(['campo' => 'required|in:' . implode(',', $todos)])['campo'];

        if ($vehiculosPloteo->$campo) {
            Storage::disk('public')->delete($vehiculosPloteo->$campo);
            $vehiculosPloteo->update([$campo => null]);
        }

        return back()->with('ok', 'Foto eliminada.');
    }
}

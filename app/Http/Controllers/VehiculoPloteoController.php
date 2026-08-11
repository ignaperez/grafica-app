<?php

namespace App\Http\Controllers;

use App\Models\VehiculoPloteo;
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

        $vehiculos = VehiculoPloteo::with(['orden.cliente', 'cliente'])
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
        return view('vehiculos-ploteo.show', ['vehiculo' => $vehiculosPloteo->load(['orden.cliente', 'cliente'])]);
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
    public function foto(VehiculoPloteo $vehiculosPloteo, string $campo)
    {
        abort_unless(in_array($campo, array_merge(self::FOTOS, self::ARCHIVOS), true), 404);

        $ruta = $vehiculosPloteo->$campo;
        abort_if(!$ruta || !Storage::disk('public')->exists($ruta), 404);

        return Storage::disk('public')->response($ruta);
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

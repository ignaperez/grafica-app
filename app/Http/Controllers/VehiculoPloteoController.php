<?php

namespace App\Http\Controllers;

use App\Models\VehiculoPloteo;
use App\Models\OrdenTrabajo;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VehiculoPloteoController extends Controller
{
    private const FOTOS = [
        'foto_antes_frente', 'foto_antes_atras', 'foto_antes_izq', 'foto_antes_der',
        'foto_despues_frente', 'foto_despues_atras', 'foto_despues_izq', 'foto_despues_der',
    ];

    private const ARCHIVOS = ['refe'];

    public function index()
    {
        $vehiculos = VehiculoPloteo::with(['orden.cliente', 'cliente'])
            ->orderByDesc('id')
            ->paginate(20);

        return view('vehiculos-ploteo.index', compact('vehiculos'));
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

        return view('vehiculos-ploteo.create', compact('orden', 'ordenes', 'clientes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'patente'          => 'required|string|max:20',
            'marca'            => 'required|string|max:100',
            'modelo'           => 'required|string|max:100',
            'fecha_ploteo'     => 'nullable|date',
            'observaciones'    => 'nullable|string',
            'orden_trabajo_id' => 'nullable|exists:orden_trabajos,id',
            'cliente_id'       => 'nullable|exists:clientes,id',
            'tipo_ploteo'      => 'required|in:completo,parcial',
            'sector'           => 'nullable|string',
        ]);

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

        return view('vehiculos-ploteo.edit', [
            'vehiculo' => $vehiculosPloteo,
            'ordenes'  => $ordenes,
            'clientes' => $clientes,
        ]);
    }

    public function update(Request $request, VehiculoPloteo $vehiculosPloteo)
    {
        $data = $request->validate([
            'patente'          => 'required|string|max:20',
            'marca'            => 'required|string|max:100',
            'modelo'           => 'required|string|max:100',
            'fecha_ploteo'     => 'nullable|date',
            'observaciones'    => 'nullable|string',
            'orden_trabajo_id' => 'nullable|exists:orden_trabajos,id',
            'cliente_id'       => 'nullable|exists:clientes,id',
            'tipo_ploteo'      => 'required|in:completo,parcial',
            'sector'           => 'nullable|string',
        ]);

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

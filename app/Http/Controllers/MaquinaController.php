<?php

namespace App\Http\Controllers;

use App\Models\Maquina;
use App\Models\TipoTrabajo;
use Illuminate\Http\Request;

class MaquinaController extends Controller
{
    public function index()
    {
        $maquinas = Maquina::with('tipoTrabajo')->orderBy('nombre')->get();
        return view('maquinas.index', compact('maquinas'));
    }

    public function create()
    {
        $tipos = TipoTrabajo::where('activo', true)->orderBy('nombre')->get();
        return view('maquinas.create', compact('tipos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'          => 'required|string|max:100',
            'descripcion'     => 'nullable|string|max:255',
            'tipo_trabajo_id' => 'nullable|exists:tipo_trabajos,id',
            'costo_m2'        => 'nullable|numeric|min:0',
            'costo_ml'        => 'nullable|numeric|min:0',
            'costo_unidad'    => 'nullable|numeric|min:0',
        ]);

        Maquina::create([
            'nombre'          => $request->nombre,
            'descripcion'     => $request->descripcion,
            'tipo_trabajo_id' => $request->tipo_trabajo_id,
            'costo_m2'        => $request->costo_m2        ?? 0,
            'costo_ml'        => $request->costo_ml        ?? 0,
            'costo_unidad'    => $request->costo_unidad    ?? 0,
            'activo'          => true,
        ]);

        return redirect()->route('maquinas.index')
            ->with('success', 'Máquina creada correctamente.');
    }

    public function edit(Maquina $maquina)
    {
        $tipos = TipoTrabajo::where('activo', true)->orderBy('nombre')->get();
        return view('maquinas.edit', compact('maquina', 'tipos'));
    }

    public function update(Request $request, Maquina $maquina)
    {
        $request->validate([
            'nombre'          => 'required|string|max:100',
            'descripcion'     => 'nullable|string|max:255',
            'tipo_trabajo_id' => 'nullable|exists:tipo_trabajos,id',
            'costo_m2'        => 'nullable|numeric|min:0',
            'costo_ml'        => 'nullable|numeric|min:0',
            'costo_unidad'    => 'nullable|numeric|min:0',
        ]);

        $maquina->update([
            'nombre'          => $request->nombre,
            'descripcion'     => $request->descripcion,
            'tipo_trabajo_id' => $request->tipo_trabajo_id,
            'costo_m2'        => $request->costo_m2        ?? 0,
            'costo_ml'        => $request->costo_ml        ?? 0,
            'costo_unidad'    => $request->costo_unidad    ?? 0,
            'activo'          => $request->boolean('activo'),
        ]);

        return redirect()->route('maquinas.index')
            ->with('success', 'Máquina actualizada correctamente.');
    }

    public function destroy(Maquina $maquina)
    {
        if ($maquina->trabajos()->exists()) {
            return back()->with('error', 'No se puede eliminar: hay trabajos asociados a esta máquina.');
        }

        $maquina->delete();

        return redirect()->route('maquinas.index')
            ->with('success', 'Máquina eliminada.');
    }
}

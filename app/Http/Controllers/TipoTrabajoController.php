<?php

namespace App\Http\Controllers;

use App\Models\TipoTrabajo;
use Illuminate\Http\Request;

class TipoTrabajoController extends Controller
{
    public function index()
    {
        $tipos = TipoTrabajo::orderBy('nombre')->get();
        return view('tipo-trabajos.index', compact('tipos'));
    }

    public function create()
    {
        return view('tipo-trabajos.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'      => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:255',
        ]);

        TipoTrabajo::create([
            'nombre'      => $request->nombre,
            'descripcion' => $request->descripcion,
            'activo'      => true,
        ]);

        return redirect()->route('tipo-trabajos.index')
            ->with('success', 'Tipo de trabajo creado correctamente.');
    }

    public function edit(TipoTrabajo $tipoTrabajo)
    {
        return view('tipo-trabajos.edit', compact('tipoTrabajo'));
    }

    public function update(Request $request, TipoTrabajo $tipoTrabajo)
    {
        $request->validate([
            'nombre'      => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:255',
            'activo'      => 'boolean',
        ]);

        $tipoTrabajo->update([
            'nombre'      => $request->nombre,
            'descripcion' => $request->descripcion,
            'activo'      => $request->boolean('activo'),
        ]);

        return redirect()->route('tipo-trabajos.index')
            ->with('success', 'Tipo de trabajo actualizado correctamente.');
    }

    public function destroy(TipoTrabajo $tipoTrabajo)
    {
        if ($tipoTrabajo->trabajos()->exists()) {
            return back()->with('error', 'No se puede eliminar: hay trabajos asociados a este tipo.');
        }

        $tipoTrabajo->delete();

        return redirect()->route('tipo-trabajos.index')
            ->with('success', 'Tipo de trabajo eliminado.');
    }
}

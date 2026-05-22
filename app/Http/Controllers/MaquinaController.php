<?php

namespace App\Http\Controllers;

use App\Models\Maquina;
use Illuminate\Http\Request;

class MaquinaController extends Controller
{
    public function index()
    {
        $maquinas = Maquina::orderBy('nombre')->get();
        return view('maquinas.index', compact('maquinas'));
    }

    public function create()
    {
        return view('maquinas.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'      => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:255',
        ]);

        Maquina::create([
            'nombre'      => $request->nombre,
            'descripcion' => $request->descripcion,
            'activo'      => true,
        ]);

        return redirect()->route('maquinas.index')
            ->with('success', 'Máquina creada correctamente.');
    }

    public function edit(Maquina $maquina)
    {
        return view('maquinas.edit', compact('maquina'));
    }

    public function update(Request $request, Maquina $maquina)
    {
        $request->validate([
            'nombre'      => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:255',
            'activo'      => 'boolean',
        ]);

        $maquina->update([
            'nombre'      => $request->nombre,
            'descripcion' => $request->descripcion,
            'activo'      => $request->boolean('activo'),
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

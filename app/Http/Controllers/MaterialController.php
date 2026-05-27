<?php

namespace App\Http\Controllers;

use App\Models\Material;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    public function index()
    {
        $materiales = Material::orderBy('nombre')->get();
        return view('materiales.index', compact('materiales'));
    }

    public function create()
    {
        return view('materiales.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'       => 'required|string|max:100',
            'descripcion'  => 'nullable|string|max:255',
            'costo_m2'     => 'nullable|numeric|min:0',
            'costo_ml'     => 'nullable|numeric|min:0',
            'costo_unidad' => 'nullable|numeric|min:0',
        ]);

        Material::create([
            'nombre'       => $request->nombre,
            'descripcion'  => $request->descripcion,
            'costo_m2'     => $request->costo_m2     ?? 0,
            'costo_ml'     => $request->costo_ml     ?? 0,
            'costo_unidad' => $request->costo_unidad ?? 0,
            'activo'       => true,
        ]);

        return redirect()->route('materiales.index')
            ->with('success', 'Material creado correctamente.');
    }

    public function edit(Material $material)
    {
        return view('materiales.edit', compact('material'));
    }

    public function update(Request $request, Material $material)
    {
        $request->validate([
            'nombre'       => 'required|string|max:100',
            'descripcion'  => 'nullable|string|max:255',
            'costo_m2'     => 'nullable|numeric|min:0',
            'costo_ml'     => 'nullable|numeric|min:0',
            'costo_unidad' => 'nullable|numeric|min:0',
        ]);

        $material->update([
            'nombre'       => $request->nombre,
            'descripcion'  => $request->descripcion,
            'costo_m2'     => $request->costo_m2     ?? 0,
            'costo_ml'     => $request->costo_ml     ?? 0,
            'costo_unidad' => $request->costo_unidad ?? 0,
            'activo'       => $request->boolean('activo'),
        ]);

        return redirect()->route('materiales.index')
            ->with('success', 'Material actualizado correctamente.');
    }

    public function destroy(Material $material)
    {
        if ($material->trabajos()->exists()) {
            return back()->with('error', 'No se puede eliminar: hay trabajos asociados a este material.');
        }

        $material->delete();

        return redirect()->route('materiales.index')
            ->with('success', 'Material eliminado.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Maquina;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    public function index()
    {
        $materiales = Material::withCount('maquinas')->orderBy('nombre')->get();
        return view('materiales.index', compact('materiales'));
    }

    public function create()
    {
        $maquinas = Maquina::where('activo', true)->orderBy('nombre')->get();
        return view('materiales.create', compact('maquinas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'       => 'required|string|max:100',
            'descripcion'  => 'nullable|string|max:255',
            'costo_m2'     => 'nullable|numeric|min:0',
            'costo_ml'     => 'nullable|numeric|min:0',
            'costo_unidad' => 'nullable|numeric|min:0',
            'maquinas'     => 'nullable|array',
            'maquinas.*'   => 'exists:maquinas,id',
        ]);

        $material = Material::create([
            'nombre'       => $request->nombre,
            'descripcion'  => $request->descripcion,
            'costo_m2'     => $request->costo_m2     ?? 0,
            'costo_ml'     => $request->costo_ml     ?? 0,
            'costo_unidad' => $request->costo_unidad ?? 0,
            'activo'       => true,
        ]);

        $this->syncMaquinas($material, $request);

        return redirect()->route('materiales.index')
            ->with('success', 'Material creado correctamente.');
    }

    public function edit(Material $material)
    {
        $material->load('maquinas');
        $maquinas = Maquina::where('activo', true)->orderBy('nombre')->get();
        return view('materiales.edit', compact('material', 'maquinas'));
    }

    public function update(Request $request, Material $material)
    {
        $request->validate([
            'nombre'       => 'required|string|max:100',
            'descripcion'  => 'nullable|string|max:255',
            'costo_m2'     => 'nullable|numeric|min:0',
            'costo_ml'     => 'nullable|numeric|min:0',
            'costo_unidad' => 'nullable|numeric|min:0',
            'maquinas'     => 'nullable|array',
            'maquinas.*'   => 'exists:maquinas,id',
        ]);

        $material->update([
            'nombre'       => $request->nombre,
            'descripcion'  => $request->descripcion,
            'costo_m2'     => $request->costo_m2     ?? 0,
            'costo_ml'     => $request->costo_ml     ?? 0,
            'costo_unidad' => $request->costo_unidad ?? 0,
            'activo'       => $request->boolean('activo'),
        ]);

        $this->syncMaquinas($material, $request);

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

    // ── Helper ───────────────────────────────────────────────────

    private function syncMaquinas(Material $material, Request $request): void
    {
        $material->maquinas()->sync($request->input('maquinas', []));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\ListaPrecio;
use App\Models\Configuracion;
use Illuminate\Http\Request;

class ListaPrecioController extends Controller
{
    public function index()
    {
        $listas = ListaPrecio::orderBy('nombre')->get();
        return view('listas-precios.index', compact('listas'));
    }

    public function create()
    {
        $moGlobal = Configuracion::mo();
        return view('listas-precios.create', compact('moGlobal'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'        => 'required|string|max:255|unique:lista_precios,nombre',
            'descripcion'   => 'nullable|string',
            'multiplicador' => 'required|numeric|min:0',
            'mo_m2'         => 'nullable|numeric|min:0',
            'mo_ml'         => 'nullable|numeric|min:0',
            'mo_unidad'     => 'nullable|numeric|min:0',
        ]);

        ListaPrecio::create([
            'nombre'        => $request->nombre,
            'descripcion'   => $request->descripcion,
            'multiplicador' => $request->multiplicador,
            'mo_m2'         => $request->filled('mo_m2')     ? $request->mo_m2     : null,
            'mo_ml'         => $request->filled('mo_ml')     ? $request->mo_ml     : null,
            'mo_unidad'     => $request->filled('mo_unidad') ? $request->mo_unidad : null,
        ]);

        return redirect()->route('listas-precios.index')
            ->with('success', 'Lista de precios creada correctamente.');
    }

    public function show(ListaPrecio $listaPrecio)
    {
        $listaPrecio->load('clientes');
        return view('listas-precios.show', compact('listaPrecio'));
    }

    public function edit(ListaPrecio $listaPrecio)
    {
        $moGlobal = Configuracion::mo();
        return view('listas-precios.edit', compact('listaPrecio', 'moGlobal'));
    }

    public function update(Request $request, ListaPrecio $listaPrecio)
    {
        $request->validate([
            'nombre'        => 'required|string|max:255|unique:lista_precios,nombre,' . $listaPrecio->id,
            'descripcion'   => 'nullable|string',
            'multiplicador' => 'required|numeric|min:0',
            'mo_m2'         => 'nullable|numeric|min:0',
            'mo_ml'         => 'nullable|numeric|min:0',
            'mo_unidad'     => 'nullable|numeric|min:0',
        ]);

        $listaPrecio->update([
            'nombre'        => $request->nombre,
            'descripcion'   => $request->descripcion,
            'multiplicador' => $request->multiplicador,
            'mo_m2'         => $request->filled('mo_m2')     ? $request->mo_m2     : null,
            'mo_ml'         => $request->filled('mo_ml')     ? $request->mo_ml     : null,
            'mo_unidad'     => $request->filled('mo_unidad') ? $request->mo_unidad : null,
        ]);

        return redirect()->route('listas-precios.index')
            ->with('success', 'Lista de precios actualizada correctamente.');
    }

    public function destroy(ListaPrecio $listaPrecio)
    {
        if ($listaPrecio->clientes()->exists()) {
            return redirect()->route('listas-precios.index')
                ->with('error', 'No se puede eliminar: hay clientes asignados a esta lista.');
        }

        $listaPrecio->delete();
        return redirect()->route('listas-precios.index')
            ->with('success', 'Lista de precios eliminada.');
    }

    public function search(Request $request)
    {
        $term   = $request->get('q');
        $listas = ListaPrecio::where('nombre', 'like', "%{$term}%")
            ->orderBy('nombre')->limit(10)->get();

        return response()->json(
            $listas->map(fn ($lp) => ['id' => $lp->id, 'text' => $lp->nombre])
        );
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;

class ProductoController extends Controller
{
    public function index()
    {
        $productos = Producto::orderBy('tipo')->orderBy('nombre')->get();
        return view('productos.index', compact('productos'));
    }

    public function create()
    {
        return view('productos.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'tipo'        => 'required|string|max:255',
            'nombre'      => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio'      => 'required|numeric|min:0',
        ]);

        Producto::create($request->only(['tipo', 'nombre', 'descripcion', 'precio']));

        return redirect()->route('productos.index')->with('success', 'Producto creado correctamente.');
    }

    public function show(Producto $producto)
    {
        return view('productos.show', compact('producto'));
    }

    public function edit(Producto $producto)
    {
        return view('productos.edit', compact('producto'));
    }

    public function update(Request $request, Producto $producto)
    {
        $request->validate([
            'tipo'        => 'required|string|max:255',
            'nombre'      => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio'      => 'required|numeric|min:0',
        ]);

        $producto->update($request->only(['tipo', 'nombre', 'descripcion', 'precio']));

        return redirect()->route('productos.index')->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy(Producto $producto)
    {
        $producto->delete();
        return redirect()->route('productos.index')->with('success', 'Producto eliminado correctamente.');
    }

    public function search(Request $request)
    {
        $term = $request->get('q');

        $productos = Producto::where('nombre', 'like', "%{$term}%")
            ->orderBy('nombre')
            ->limit(10)
            ->get();

        return response()->json(
            $productos->map(fn ($p) => [
                'id'   => $p->id,
                'text' => $p->nombre . ($p->precio ? ' ($' . number_format($p->precio, 2) . ')' : ''),
            ])
        );
    }
}

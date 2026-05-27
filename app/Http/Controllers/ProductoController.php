<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\TipoTrabajo;
use App\Models\Material;

class ProductoController extends Controller
{
    private function lookups(): array
    {
        return [
            'tipos'     => TipoTrabajo::where('activo', true)->orderBy('nombre')->get(),
            'materiales'=> Material::where('activo', true)->orderBy('nombre')->get(),
        ];
    }

    public function index()
    {
        $productos = Producto::with(['tipoTrabajo', 'material'])
            ->orderBy('nombre')
            ->get();

        return view('productos.index', compact('productos'));
    }

    public function create()
    {
        return view('productos.create', $this->lookups());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'          => 'required|string|max:255',
            'descripcion'     => 'nullable|string',
            'tipo_trabajo_id' => 'nullable|exists:tipo_trabajos,id',
            'material_id'     => 'nullable|exists:materiales,id',
            'unidad'          => 'required|in:m2,ml,unidad',
            'costo_mano_obra' => 'nullable|numeric|min:0',
            'activo'          => 'sometimes|boolean',
            // legacy — ignoramos si viene vacío
            'tipo'            => 'nullable|string|max:255',
            'precio'          => 'nullable|numeric|min:0',
        ]);

        $data['activo'] = $request->boolean('activo', true);
        $data['costo_mano_obra'] = $data['costo_mano_obra'] ?? 0;

        Producto::create($data);

        return redirect()->route('productos.index')->with('success', 'Servicio creado correctamente.');
    }

    public function show(Producto $producto)
    {
        $producto->load(['tipoTrabajo', 'material']);
        return view('productos.show', compact('producto'));
    }

    public function edit(Producto $producto)
    {
        return view('productos.edit', array_merge(['producto' => $producto], $this->lookups()));
    }

    public function update(Request $request, Producto $producto)
    {
        $data = $request->validate([
            'nombre'          => 'required|string|max:255',
            'descripcion'     => 'nullable|string',
            'tipo_trabajo_id' => 'nullable|exists:tipo_trabajos,id',
            'material_id'     => 'nullable|exists:materiales,id',
            'unidad'          => 'required|in:m2,ml,unidad',
            'costo_mano_obra' => 'nullable|numeric|min:0',
            'activo'          => 'sometimes|boolean',
            'tipo'            => 'nullable|string|max:255',
            'precio'          => 'nullable|numeric|min:0',
        ]);

        $data['activo'] = $request->boolean('activo', true);
        $data['costo_mano_obra'] = $data['costo_mano_obra'] ?? 0;

        $producto->update($data);

        return redirect()->route('productos.index')->with('success', 'Servicio actualizado correctamente.');
    }

    public function destroy(Producto $producto)
    {
        if ($producto->trabajos()->exists()) {
            return redirect()->route('productos.index')
                ->with('error', 'No se puede eliminar: tiene trabajos asociados.');
        }

        $producto->delete();
        return redirect()->route('productos.index')->with('success', 'Servicio eliminado.');
    }

    public function search(Request $request)
    {
        $term = $request->get('q', '');

        $productos = Producto::where('activo', true)
            ->where('nombre', 'like', "%{$term}%")
            ->orderBy('nombre')
            ->limit(15)
            ->get();

        return response()->json(
            $productos->map(fn ($p) => [
                'id'   => $p->id,
                'text' => $p->nombre,
            ])
        );
    }
}

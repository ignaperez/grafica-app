<?php

namespace App\Http\Controllers;

use App\Models\Marca;
use App\Models\ModeloVehiculo;
use Illuminate\Http\Request;

class MarcaVehiculoController extends Controller
{
    /** Alta rápida de marca (JSON, desde el modal). */
    public function store(Request $request)
    {
        $data  = $request->validate(['nombre' => 'required|string|max:100']);
        $marca = Marca::firstOrCreate(['nombre' => trim($data['nombre'])], ['activo' => true]);

        return response()->json(['id' => $marca->id, 'nombre' => $marca->nombre]);
    }

    /** Modelos de una marca (para el select dependiente). */
    public function modelos(Marca $marca)
    {
        return response()->json(
            $marca->modelos()->where('activo', true)->orderBy('nombre')->get(['id', 'nombre'])
        );
    }

    /** Alta rápida de modelo bajo una marca (JSON, desde el modal). */
    public function modelosStore(Request $request)
    {
        $data = $request->validate([
            'marca_id' => 'required|exists:marcas,id',
            'nombre'   => 'required|string|max:100',
        ]);

        $modelo = ModeloVehiculo::firstOrCreate(
            ['marca_id' => $data['marca_id'], 'nombre' => trim($data['nombre'])],
            ['activo' => true]
        );

        return response()->json(['id' => $modelo->id, 'nombre' => $modelo->nombre]);
    }
}

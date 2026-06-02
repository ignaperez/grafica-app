<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RemitoCai;

class RemitoCaiController extends Controller
{
    public function index()
    {
        $cais = RemitoCai::withTrashed()->orderByDesc('id')->get();
        return view('remito-cais.index', compact('cais'));
    }

    public function create()
    {
        return view('remito-cais.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'codigo'        => 'required|string|max:14',
            'punto_venta'   => 'required|integer|min:1|max:9999',
            'numero_desde'  => 'required|integer|min:1',
            'numero_hasta'  => 'required|integer|min:1|gt:numero_desde',
            'vencimiento'   => 'required|date|after:today',
            'notas'         => 'nullable|string|max:500',
        ]);

        // Desactivar otros CAI activos del mismo punto de venta
        RemitoCai::where('punto_venta', $request->punto_venta)
            ->where('activo', true)
            ->update(['activo' => false]);

        RemitoCai::create([
            'codigo'        => strtoupper(trim($request->codigo)),
            'punto_venta'   => $request->punto_venta,
            'tipo_cbte'     => 91,
            'numero_desde'  => $request->numero_desde,
            'numero_hasta'  => $request->numero_hasta,
            'ultimo_numero' => $request->numero_desde - 1, // el próximo que se use será numero_desde
            'vencimiento'   => $request->vencimiento,
            'activo'        => true,
            'notas'         => $request->notas,
        ]);

        return redirect()->route('remito-cais.index')
            ->with('success', 'CAI cargado correctamente. Los próximos remitos usarán este rango.');
    }

    public function destroy(RemitoCai $remitoCai)
    {
        $remitoCai->update(['activo' => false]);
        $remitoCai->delete();
        return back()->with('ok', 'CAI desactivado y archivado.');
    }
}

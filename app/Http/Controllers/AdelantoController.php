<?php

namespace App\Http\Controllers;

use App\Models\Adelanto;
use App\Models\Empleado;
use Illuminate\Http\Request;

class AdelantoController extends Controller
{
    public function index(Empleado $empleado)
    {
        $adelantos = $empleado->adelantos()->with('pago')
            ->orderByDesc('fecha')->orderByDesc('id')->get();

        return view('rrhh.adelantos.index', compact('empleado', 'adelantos'));
    }

    public function store(Request $request, Empleado $empleado)
    {
        $data = $request->validate([
            'fecha'         => ['required', 'date'],
            'monto'         => ['required', 'numeric', 'min:0.01'],
            'observaciones' => ['nullable', 'string', 'max:500'],
        ]);

        $empleado->adelantos()->create([
            'fecha'         => $data['fecha'],
            'monto'         => $data['monto'],
            'observaciones' => $data['observaciones'] ?? null,
            'created_by'    => auth()->id(),
        ]);

        return back()->with('ok', 'Adelanto registrado.');
    }

    public function update(Request $request, Adelanto $adelanto)
    {
        abort_if($adelanto->saldado(), 403, 'El adelanto ya fue saldado en una liquidación; no se puede editar.');

        $data = $request->validate([
            'fecha'         => ['required', 'date'],
            'monto'         => ['required', 'numeric', 'min:0.01'],
            'observaciones' => ['nullable', 'string', 'max:500'],
        ]);

        $adelanto->update($data);

        return back()->with('ok', 'Adelanto actualizado.');
    }

    public function destroy(Adelanto $adelanto)
    {
        abort_if($adelanto->saldado(), 403, 'El adelanto ya fue saldado; no se puede eliminar.');

        $adelanto->delete();

        return back()->with('ok', 'Adelanto eliminado.');
    }

    /** Vale imprimible del adelanto (comprobante para firmar). */
    public function vale(Adelanto $adelanto)
    {
        $adelanto->load('empleado');
        return view('rrhh.adelantos.vale', compact('adelanto'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Presupuesto;
use App\Models\Remito;

class PapeleraController extends Controller
{
    /** Modelos soportados por la papelera: tipo => clase. */
    private const MODELOS = [
        'cliente'     => Cliente::class,
        'presupuesto' => Presupuesto::class,
        'remito'      => Remito::class,
    ];

    public function index()
    {
        $clientes = Cliente::onlyTrashed()
            ->orderByDesc('deleted_at')->get();

        $presupuestos = Presupuesto::onlyTrashed()
            ->with(['cliente' => fn ($q) => $q->withTrashed()])
            ->orderByDesc('deleted_at')->get();

        $remitos = Remito::onlyTrashed()
            ->with(['cliente' => fn ($q) => $q->withTrashed()])
            ->orderByDesc('deleted_at')->get();

        return view('papelera.index', compact('clientes', 'presupuestos', 'remitos'));
    }

    public function restore(string $tipo, int $id)
    {
        $clase = self::MODELOS[$tipo] ?? null;

        if (!$clase) {
            return back()->with('error', 'Tipo de registro no válido.');
        }

        $registro = $clase::onlyTrashed()->findOrFail($id);
        $registro->restore();

        $label = ['cliente' => 'Cliente', 'presupuesto' => 'Presupuesto', 'remito' => 'Remito'][$tipo];

        return back()->with('success', "{$label} restaurado correctamente.");
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Maquina;
use App\Models\Cliente;
use App\Models\Configuracion;

class CatalogoController extends Controller
{
    public function index()
    {
        $mo      = Configuracion::mo();
        $maquinas = Maquina::with(['tipoTrabajo', 'materiales'])
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        $grupos = $this->buildCombinaciones($maquinas, $mo)
            ->groupBy(fn ($c) => $c['tipo']);

        return view('catalogo.index', compact('grupos', 'mo'));
    }

    public function print(Request $request)
    {
        $clienteId     = $request->get('cliente_id');
        $multiplicador = 1;
        $cliente       = null;
        $moGlobal      = Configuracion::mo();

        if ($clienteId) {
            $cliente       = Cliente::with('listaPrecio')->find($clienteId);
            $multiplicador = (float) ($cliente?->listaPrecio?->multiplicador ?? 1);
        }

        $lista = $cliente?->listaPrecio;
        $mo = [
            'm2'     => $lista?->mo_m2     !== null ? (float)$lista->mo_m2     : $moGlobal['m2'],
            'ml'     => $lista?->mo_ml     !== null ? (float)$lista->mo_ml     : $moGlobal['ml'],
            'unidad' => $lista?->mo_unidad !== null ? (float)$lista->mo_unidad : $moGlobal['unidad'],
        ];

        $maquinas = Maquina::with(['tipoTrabajo', 'materiales'])
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        $grupos   = $this->buildCombinaciones($maquinas, $mo)
            ->groupBy(fn ($c) => $c['tipo']);

        $clientes = Cliente::orderBy('nombre')->get();

        return view('catalogo.print', compact(
            'grupos', 'cliente', 'multiplicador', 'clientes', 'mo', 'moGlobal'
        ));
    }

    // ── Helper ───────────────────────────────────────────────────

    private function buildCombinaciones($maquinas, array $mo): \Illuminate\Support\Collection
    {
        $lista = collect();

        foreach ($maquinas as $maquina) {
            foreach ($maquina->materiales as $material) {
                // La unidad la define el material
                $unidad = $material->unidad ?? 'm2';

                // Costo base según unidad
                [$costoMaq, $costoMat, $costoMO] = match ($unidad) {
                    'ml'     => [(float)$maquina->costo_ml,     (float)$material->costo_ml,     $mo['ml']],
                    'unidad' => [(float)$maquina->costo_unidad, (float)$material->costo_unidad, $mo['unidad']],
                    default  => [(float)$maquina->costo_m2,     (float)$material->costo_m2,     $mo['m2']],
                };

                $costo = round($costoMaq + $costoMat + $costoMO, 2);

                $lista->push([
                    'maquina'      => $maquina,
                    'material'     => $material,
                    'tipo'         => $maquina->tipoTrabajo?->nombre ?? 'Sin proceso',
                    'unidad'       => $unidad,
                    'costo'        => $costo,
                    // Desglose para tooltip
                    'maq'          => round($costoMaq, 2),
                    'mat'          => round($costoMat, 2),
                    'mo'           => round($costoMO,  2),
                ]);
            }
        }

        return $lista;
    }
}

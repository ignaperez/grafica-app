<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use Illuminate\Http\Request;

class ParametroSueldoController extends Controller
{
    /** Claves + valores por defecto. */
    public const DEFAULTS = [
        'sueldo_coef_normal'    => 1,
        'sueldo_coef_sabado'    => 1,
        'sueldo_coef_domingo'   => 1,
        'sueldo_coef_extra'     => 1,
        'sueldo_jornada_sabado' => 4,
        'sueldo_tolerancia_min' => 10,
    ];

    /** Lee los parámetros actuales (con defaults). */
    public static function actuales(): array
    {
        $out = [];
        foreach (self::DEFAULTS as $k => $def) {
            $out[$k] = (float) Configuracion::get($k, $def);
        }
        return $out;
    }

    public function edit()
    {
        $params = self::actuales();
        return view('rrhh.parametros-sueldo', compact('params'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'sueldo_coef_normal'    => ['required', 'numeric', 'min:0'],
            'sueldo_coef_sabado'    => ['required', 'numeric', 'min:0'],
            'sueldo_coef_domingo'   => ['required', 'numeric', 'min:0'],
            'sueldo_coef_extra'     => ['required', 'numeric', 'min:0'],
            'sueldo_jornada_sabado' => ['required', 'numeric', 'min:0', 'max:24'],
            'sueldo_tolerancia_min' => ['required', 'integer', 'min:0', 'max:120'],
        ]);

        foreach ($data as $clave => $valor) {
            Configuracion::set($clave, $valor);
        }

        return back()->with('ok', 'Parámetros de sueldo guardados.');
    }
}

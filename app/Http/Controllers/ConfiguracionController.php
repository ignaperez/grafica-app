<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Configuracion;

class ConfiguracionController extends Controller
{
    /**
     * Pantalla de configuración global del sistema.
     */
    public function edit()
    {
        $mo      = Configuracion::mo();
        $empresa = Configuracion::empresa();
        return view('configuracion.edit', compact('mo', 'empresa'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'mo_m2'              => 'required|numeric|min:0',
            'mo_ml'              => 'required|numeric|min:0',
            'mo_unidad'          => 'required|numeric|min:0',
            'empresa_nombre'     => 'nullable|string|max:120',
            'empresa_cuit'       => 'nullable|string|max:30',
            'empresa_direccion'  => 'nullable|string|max:200',
            'empresa_telefono'   => 'nullable|string|max:60',
            'empresa_propietario'=> 'nullable|string|max:120',
            'empresa_email'      => 'nullable|email|max:120',
        ]);

        // Mano de obra
        Configuracion::set('mo_m2',     $request->mo_m2);
        Configuracion::set('mo_ml',     $request->mo_ml);
        Configuracion::set('mo_unidad', $request->mo_unidad);

        // Datos de empresa
        $campos = ['empresa_nombre', 'empresa_cuit', 'empresa_direccion',
                   'empresa_telefono', 'empresa_propietario', 'empresa_email'];

        foreach ($campos as $clave) {
            Configuracion::set($clave, $request->input($clave, ''));
        }

        return back()->with('success', 'Configuración guardada correctamente.');
    }
}

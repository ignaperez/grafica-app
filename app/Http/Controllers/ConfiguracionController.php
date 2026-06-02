<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
            'mo_m2'                        => 'required|numeric|min:0',
            'mo_ml'                        => 'required|numeric|min:0',
            'mo_unidad'                    => 'required|numeric|min:0',
            'empresa_nombre'               => 'nullable|string|max:120',
            'empresa_cuit'                 => 'nullable|string|max:30',
            'empresa_direccion'            => 'nullable|string|max:200',
            'empresa_telefono'             => 'nullable|string|max:60',
            'empresa_propietario'          => 'nullable|string|max:120',
            'empresa_email'                => 'nullable|email|max:120',
            'empresa_condicion_iva'        => 'nullable|string|max:80',
            'empresa_iibb'                 => 'nullable|string|max:60',
            'empresa_inicio_actividades'   => 'nullable|string|max:20',
            'empresa_logo'                 => 'nullable|image|mimes:jpeg,png,gif,webp,svg|max:2048',
        ]);

        // Mano de obra
        Configuracion::set('mo_m2',     $request->mo_m2);
        Configuracion::set('mo_ml',     $request->mo_ml);
        Configuracion::set('mo_unidad', $request->mo_unidad);

        // Datos de empresa
        $campos = [
            'empresa_nombre', 'empresa_cuit', 'empresa_direccion',
            'empresa_telefono', 'empresa_propietario', 'empresa_email',
            'empresa_condicion_iva', 'empresa_iibb', 'empresa_inicio_actividades',
        ];

        foreach ($campos as $clave) {
            Configuracion::set($clave, $request->input($clave, ''));
        }

        // Logo
        if ($request->hasFile('empresa_logo')) {
            $old = Configuracion::get('empresa_logo');
            if ($old) Storage::disk('public')->delete($old);
            $path = $request->file('empresa_logo')->store('logos', 'public');
            Configuracion::set('empresa_logo', $path);
        }

        return back()->with('success', 'Configuración guardada correctamente.');
    }
}

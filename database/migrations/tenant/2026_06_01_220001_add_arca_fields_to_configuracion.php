<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Insertar filas de configuración para datos ARCA en la factura
        // insertOrIgnore para no pisar valores existentes
        DB::table('configuracion')->insertOrIgnore([
            [
                'clave'       => 'empresa_condicion_iva',
                'valor'       => '',
                'descripcion' => 'Condición frente al IVA del emisor (aparece en facturas)',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'clave'       => 'empresa_iibb',
                'valor'       => '',
                'descripcion' => 'Número de Ingresos Brutos (aparece en facturas)',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'clave'       => 'empresa_inicio_actividades',
                'valor'       => '',
                'descripcion' => 'Fecha de inicio de actividades (ej: 01/01/2020)',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);
    }

    public function down(): void
    {
        DB::table('configuracion')->whereIn('clave', [
            'empresa_condicion_iva',
            'empresa_iibb',
            'empresa_inicio_actividades',
        ])->delete();
    }
};

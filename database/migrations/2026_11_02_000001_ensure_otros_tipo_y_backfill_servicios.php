<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Catálogo = Servicios agrupados por Tipo de Trabajo (obligatorio).
 * Se asegura un tipo "OTROS" como última opción y se reasignan los servicios
 * que hoy no tienen tipo a ese "OTROS", para que el campo pueda pasar a
 * obligatorio sin dejar servicios huérfanos.
 */
return new class extends Migration
{
    public function up(): void
    {
        $id = DB::table('tipo_trabajos')
            ->whereNull('deleted_at')
            ->whereRaw('UPPER(nombre) = ?', ['OTROS'])
            ->value('id');

        if (! $id) {
            $id = DB::table('tipo_trabajos')->insertGetId([
                'nombre'     => 'OTROS',
                'descripcion'=> 'Servicios sin un tipo específico',
                'activo'     => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Servicios sin tipo → OTROS
        DB::table('productos')->whereNull('tipo_trabajo_id')->update(['tipo_trabajo_id' => $id]);
    }

    public function down(): void
    {
        // No revertimos: no borramos OTROS ni volvemos a dejar servicios sin tipo.
    }
};

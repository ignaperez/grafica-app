<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * No todo lo que se plotea es un auto: una lancha, un trailer, un contenedor.
 * Con `marca_id` y `modelo_id` obligatorios contra el catálogo, no se podía
 * cargar nada que no estuviera ahí sin inventar una marca y un modelo.
 *
 * `marcas.modelo_libre` marca a una marca como "sin catálogo de modelos": al
 * elegirla, el formulario cambia el select de Modelo por un campo de texto y
 * `vehiculo_ploteos.modelo_id` queda en NULL (ya era nullable), guardando la
 * descripción en la columna de texto `modelo`.
 *
 * Se crea la marca OTRA con ese flag. Se puede marcar cualquier otra igual
 * (por ejemplo una "EMBARCACIONES") editando el flag.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('marcas', 'modelo_libre')) {
            Schema::table('marcas', function (Blueprint $table) {
                $table->boolean('modelo_libre')->default(false)->after('activo');
            });
        }

        $otra = DB::table('marcas')->where('nombre', 'OTRA')->first();

        if ($otra) {
            DB::table('marcas')->where('id', $otra->id)->update(['modelo_libre' => true]);
        } else {
            DB::table('marcas')->insert([
                'nombre'       => 'OTRA',
                'activo'       => true,
                'modelo_libre' => true,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('marcas', 'modelo_libre')) {
            Schema::table('marcas', function (Blueprint $table) {
                $table->dropColumn('modelo_libre');
            });
        }
    }
};

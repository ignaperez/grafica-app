<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Estados válidos:
     *
     * orden_trabajos: borrador | en_produccion | lista | entregada | cancelada
     * trabajos:       pendiente | en_proceso | terminado | entregado
     */
    public function up(): void
    {
        // Actualizamos el default de orden_trabajos
        Schema::table('orden_trabajos', function (Blueprint $table) {
            $table->string('estado')->default('borrador')->change();
        });

        // Actualizamos el default de trabajos (ya era pendiente, lo dejamos igual)
        Schema::table('trabajos', function (Blueprint $table) {
            $table->string('estado')->default('pendiente')->change();
        });

        // Normalizamos datos existentes en orden_trabajos
        // Cualquier valor que no sea uno de los nuevos estados lo mapeamos
        DB::table('orden_trabajos')->where('estado', 'pendiente')->update(['estado' => 'en_produccion']);
        DB::table('orden_trabajos')->whereNotIn('estado', [
            'borrador', 'en_produccion', 'lista', 'entregada', 'cancelada'
        ])->update(['estado' => 'borrador']);

        // Normalizamos datos existentes en trabajos
        DB::table('trabajos')->whereNotIn('estado', [
            'pendiente', 'en_proceso', 'terminado', 'entregado'
        ])->update(['estado' => 'pendiente']);
    }

    public function down(): void
    {
        Schema::table('orden_trabajos', function (Blueprint $table) {
            $table->string('estado')->default('pendiente')->change();
        });

        Schema::table('trabajos', function (Blueprint $table) {
            $table->string('estado')->default('pendiente')->change();
        });
    }
};

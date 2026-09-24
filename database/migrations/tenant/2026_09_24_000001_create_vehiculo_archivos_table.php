<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Un vehículo podía tener UNA sola referencia (columna `refe`, un archivo), pero
 * el que plotea necesita ver varias: frente, lateral, detalle del logo, etc.
 *
 * Se pasa a una tabla propia, con el mismo patrón que `trabajo_archivos`. La
 * columna `refe` se conserva con su valor (no se toca) y se backfillea acá: a
 * partir de ahora la app lee esta tabla.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('vehiculo_archivos')) {
            Schema::create('vehiculo_archivos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('vehiculo_ploteo_id')->constrained('vehiculo_ploteos')->cascadeOnDelete();
                $table->string('nombre_original');
                $table->string('ruta');
                $table->string('mime_type')->nullable();
                $table->unsignedBigInteger('tamanio')->nullable();
                $table->unsignedSmallInteger('orden')->default(0);
                $table->softDeletes();
                $table->timestamps();

                $table->index('vehiculo_ploteo_id');
            });
        }

        // Backfill de la referencia única que ya estaba cargada.
        if (Schema::hasColumn('vehiculo_ploteos', 'refe')) {
            $pendientes = DB::table('vehiculo_ploteos')
                ->whereNotNull('refe')
                ->where('refe', '!=', '')
                ->whereNotExists(function ($q) {
                    $q->select(DB::raw(1))
                        ->from('vehiculo_archivos')
                        ->whereColumn('vehiculo_archivos.vehiculo_ploteo_id', 'vehiculo_ploteos.id');
                })
                ->get(['id', 'refe']);

            foreach ($pendientes as $v) {
                DB::table('vehiculo_archivos')->insert([
                    'vehiculo_ploteo_id' => $v->id,
                    'nombre_original'    => basename($v->refe),
                    'ruta'               => $v->refe,
                    'mime_type'          => null,
                    'tamanio'            => null,
                    'orden'              => 0,
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('vehiculo_archivos');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * "Terminado" pasa a ser explícito: lo marca el colocador cuando entrega el
 * vehículo. Antes se deducía de tener alguna foto del después, que servía como
 * aproximación pero no es lo mismo: se pueden subir fotos a mitad del trabajo,
 * o terminar uno sin llegar a sacar las cuatro.
 *
 * Se guarda la FECHA en vez de un booleano: además de saber si está terminado,
 * queda cuándo, que es lo que después sirve para medir.
 *
 * Backfill: lo que hoy figura como terminado (tiene alguna foto del después)
 * queda marcado con su última modificación, para no "despendientar" nada.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('vehiculo_ploteos', 'terminado_at')) {
            Schema::table('vehiculo_ploteos', function (Blueprint $table) {
                $table->timestamp('terminado_at')->nullable()->after('fecha_ploteo');
            });
        }

        DB::table('vehiculo_ploteos')
            ->whereNull('terminado_at')
            ->where(function ($q) {
                foreach (['foto_despues_frente', 'foto_despues_atras', 'foto_despues_izq', 'foto_despues_der'] as $campo) {
                    $q->orWhereNotNull($campo);
                }
            })
            ->update(['terminado_at' => DB::raw('updated_at')]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('vehiculo_ploteos', 'terminado_at')) {
            Schema::table('vehiculo_ploteos', function (Blueprint $table) {
                $table->dropColumn('terminado_at');
            });
        }
    }
};

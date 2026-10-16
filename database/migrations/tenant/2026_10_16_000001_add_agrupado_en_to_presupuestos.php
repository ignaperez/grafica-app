<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Vincula un presupuesto con el presupuesto NUEVO en el que se agrupó.
 *
 * Al juntar varios presupuestos en uno, los originales quedan vivos. Sin esta
 * marca su botón "⚡ Factura" sigue activo y se puede facturar dos veces el
 * mismo trabajo — algo que ya pasó en producción con dos CAE reales.
 *
 * El nuevo presupuesto es INDEPENDIENTE: se copian los datos y después se
 * edita libremente. Esto es solo trazabilidad, no una sincronización.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('presupuestos', function (Blueprint $table) {
            if (! Schema::hasColumn('presupuestos', 'agrupado_en_id')) {
                $table->foreignId('agrupado_en_id')
                    ->nullable()
                    ->after('orden_trabajo_id')
                    ->constrained('presupuestos')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('presupuestos', function (Blueprint $table) {
            if (Schema::hasColumn('presupuestos', 'agrupado_en_id')) {
                $table->dropConstrainedForeignId('agrupado_en_id');
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Permite cargar filas de Seguimiento a mano (procesos que vienen del sistema
     * anterior): el presupuesto pasa a ser opcional y se agregan los datos
     * manuales que antes se leían del presupuesto.
     */
    public function up(): void
    {
        // 1) Soltar la FK para poder cambiar la columna a nullable
        Schema::table('seguimientos', function (Blueprint $table) {
            $table->dropForeign(['presupuesto_id']);
        });

        Schema::table('seguimientos', function (Blueprint $table) {
            $table->unsignedBigInteger('presupuesto_id')->nullable()->change();
            $table->foreign('presupuesto_id')->references('id')->on('presupuestos')->cascadeOnDelete();
        });

        // 2) Datos manuales (se usan cuando no hay presupuesto en el sistema)
        Schema::table('seguimientos', function (Blueprint $table) {
            if (!Schema::hasColumn('seguimientos', 'fecha_manual')) {
                $table->date('fecha_manual')->nullable()->after('factura_id');
            }
            if (!Schema::hasColumn('seguimientos', 'numero_manual')) {
                $table->string('numero_manual')->nullable()->after('fecha_manual');
            }
            if (!Schema::hasColumn('seguimientos', 'monto_manual')) {
                $table->decimal('monto_manual', 14, 2)->nullable()->after('numero_manual');
            }
        });
    }

    public function down(): void
    {
        Schema::table('seguimientos', function (Blueprint $table) {
            foreach (['fecha_manual', 'numero_manual', 'monto_manual'] as $col) {
                if (Schema::hasColumn('seguimientos', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

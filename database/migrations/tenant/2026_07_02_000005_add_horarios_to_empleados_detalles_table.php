<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Horario habitual del empleado (para calcular llegadas tarde / horas — etapa 2).
    public function up(): void
    {
        Schema::table('empleados_detalles', function (Blueprint $table) {
            if (!Schema::hasColumn('empleados_detalles', 'horario_ingreso')) {
                $table->time('horario_ingreso')->nullable()->after('horas_jornada');
            }
            if (!Schema::hasColumn('empleados_detalles', 'horario_egreso')) {
                $table->time('horario_egreso')->nullable()->after('horario_ingreso');
            }
        });
    }

    public function down(): void
    {
        Schema::table('empleados_detalles', function (Blueprint $table) {
            foreach (['horario_ingreso', 'horario_egreso'] as $col) {
                if (Schema::hasColumn('empleados_detalles', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

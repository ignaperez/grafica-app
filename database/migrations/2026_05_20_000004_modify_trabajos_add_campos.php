<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trabajos', function (Blueprint $table) {
            // Hacer orden_trabajo_id nullable (trabajos pueden existir sin orden)
            $table->unsignedBigInteger('orden_trabajo_id')->nullable()->change();

            // Cliente directo en el trabajo (obligatorio cuando no hay orden)
            $table->foreignId('cliente_id')->nullable()->after('orden_trabajo_id')
                ->constrained('clientes')->nullOnDelete();

            // Tipo de trabajo dinámico
            $table->foreignId('tipo_trabajo_id')->nullable()->after('cliente_id')
                ->constrained('tipo_trabajos')->nullOnDelete();

            // Material dinámico
            $table->foreignId('material_id')->nullable()->after('tipo_trabajo_id')
                ->constrained('materiales')->nullOnDelete();

            // Máquina dinámica
            $table->foreignId('maquina_id')->nullable()->after('material_id')
                ->constrained('maquinas')->nullOnDelete();

            // Fecha de carga automática
            $table->timestamp('fecha_carga')->nullable()->after('maquina_id');
        });
    }

    public function down(): void
    {
        Schema::table('trabajos', function (Blueprint $table) {
            $table->dropForeign(['cliente_id']);
            $table->dropForeign(['tipo_trabajo_id']);
            $table->dropForeign(['material_id']);
            $table->dropForeign(['maquina_id']);
            $table->dropColumn(['cliente_id', 'tipo_trabajo_id', 'material_id', 'maquina_id', 'fecha_carga']);
            $table->unsignedBigInteger('orden_trabajo_id')->nullable(false)->change();
        });
    }
};

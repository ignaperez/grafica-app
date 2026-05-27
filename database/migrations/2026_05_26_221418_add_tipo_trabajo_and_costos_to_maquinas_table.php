<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('maquinas', function (Blueprint $table) {
            $table->foreignId('tipo_trabajo_id')->nullable()->constrained('tipo_trabajos')->nullOnDelete()->after('nombre');
            $table->decimal('costo_m2',      10, 2)->default(0)->after('descripcion');
            $table->decimal('costo_ml',      10, 2)->default(0)->after('costo_m2');
            $table->decimal('costo_unidad',  10, 2)->default(0)->after('costo_ml');
        });
    }

    public function down(): void
    {
        Schema::table('maquinas', function (Blueprint $table) {
            $table->dropForeign(['tipo_trabajo_id']);
            $table->dropColumn(['tipo_trabajo_id', 'costo_m2', 'costo_ml', 'costo_unidad']);
        });
    }
};

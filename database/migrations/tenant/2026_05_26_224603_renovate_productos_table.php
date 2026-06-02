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
        Schema::table('productos', function (Blueprint $table) {
            if (!Schema::hasColumn('productos', 'tipo_trabajo_id')) {
                $table->foreignId('tipo_trabajo_id')->nullable()->constrained('tipo_trabajos')->nullOnDelete()->after('nombre');
            }
            if (!Schema::hasColumn('productos', 'material_id')) {
                $table->foreignId('material_id')->nullable()->constrained('materiales')->nullOnDelete()->after('tipo_trabajo_id');
            }
            if (!Schema::hasColumn('productos', 'unidad')) {
                $table->enum('unidad', ['m2', 'ml', 'unidad'])->default('m2')->after('material_id');
            }
            if (!Schema::hasColumn('productos', 'costo_mano_obra')) {
                $table->decimal('costo_mano_obra', 10, 2)->default(0)->after('unidad');
            }
            if (!Schema::hasColumn('productos', 'activo')) {
                $table->boolean('activo')->default(true)->after('costo_mano_obra');
            }
            if (!Schema::hasColumn('productos', 'deleted_at')) {
                $table->softDeletes();
            }

            // Campos legacy: hacerlos nullable
            $table->string('tipo')->nullable()->change();
            $table->decimal('precio', 10, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropForeign(['tipo_trabajo_id']);
            $table->dropForeign(['material_id']);
            $table->dropColumn(['tipo_trabajo_id', 'material_id', 'unidad', 'costo_mano_obra', 'activo', 'deleted_at']);
            $table->string('tipo')->nullable(false)->change();
            $table->decimal('precio', 10, 2)->nullable(false)->change();
        });
    }
};

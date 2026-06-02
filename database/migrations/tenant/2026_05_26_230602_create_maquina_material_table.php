<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maquina_material', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maquina_id')->constrained('maquinas')->cascadeOnDelete();
            $table->foreignId('material_id')->constrained('materiales')->cascadeOnDelete();
            // Mano de obra adicional por esta combinación (instalación, acabado, etc.)
            $table->decimal('costo_mo_m2',     10, 2)->default(0);
            $table->decimal('costo_mo_ml',     10, 2)->default(0);
            $table->decimal('costo_mo_unidad', 10, 2)->default(0);
            $table->unique(['maquina_id', 'material_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maquina_material');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presupuesto_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('presupuesto_id')->constrained('presupuestos')->cascadeOnDelete();

            // Referencia al catálogo (nullable → ítem manual)
            $table->foreignId('maquina_id')->nullable()->constrained('maquinas')->nullOnDelete();
            $table->foreignId('material_id')->nullable()->constrained('materiales')->nullOnDelete();

            $table->string('descripcion');             // snapshot del nombre del servicio
            $table->enum('unidad', ['m2', 'ml', 'unidad']);

            // Dimensiones (según unidad)
            $table->decimal('ancho',    8, 4)->nullable(); // m2
            $table->decimal('alto',     8, 4)->nullable(); // m2
            $table->decimal('largo',    8, 4)->nullable(); // ml
            $table->unsignedInteger('cantidad')->default(1);

            // Precios snapshot (ya con multiplicador y MO aplicados)
            $table->decimal('precio_unitario', 10, 2); // precio por m²/ml/u
            $table->decimal('subtotal',        12, 2); // precio × medida

            $table->unsignedSmallInteger('orden')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presupuesto_items');
    }
};

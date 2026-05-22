<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('trabajos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_trabajo_id')->constrained()->onDelete('cascade');
            $table->string('tipo'); // lona, vinilo, letras, etc.
            $table->text('descripcion')->nullable();
            $table->string('medidas')->nullable(); // Ej: "2x1.5"
            $table->integer('cantidad')->default(1);
            $table->string('estado')->default('pendiente'); // estado por trabajo
            $table->date('fecha_entrega')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trabajos');
    }
};

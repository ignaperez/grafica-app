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
        Schema::create('vehiculo_ploteos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_trabajo_id')->nullable()->constrained('orden_trabajos')->nullOnDelete();
            $table->string('patente', 20);
            $table->string('marca', 100);
            $table->string('modelo', 100);
            $table->date('fecha_ploteo')->nullable();
            $table->text('observaciones')->nullable();

            $table->string('foto_antes_frente')->nullable();
            $table->string('foto_antes_atras')->nullable();
            $table->string('foto_antes_izq')->nullable();
            $table->string('foto_antes_der')->nullable();

            $table->string('foto_despues_frente')->nullable();
            $table->string('foto_despues_atras')->nullable();
            $table->string('foto_despues_izq')->nullable();
            $table->string('foto_despues_der')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehiculo_ploteos');
    }
};

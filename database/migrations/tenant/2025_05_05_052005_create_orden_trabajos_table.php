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
    
    Schema::create('orden_trabajos', function (Blueprint $table) {
        $table->id();
        $table->string('cliente')->nullable();
        $table->date('fecha_recibido'); // fecha en que se recibe o crea la orden
        $table->text('observaciones')->nullable();
        $table->string('estado')->default('pendiente'); // estado general
        $table->timestamps();

    });
    
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orden_trabajos');
    }
};

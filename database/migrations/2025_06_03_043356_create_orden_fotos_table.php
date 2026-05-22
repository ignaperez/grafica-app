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
        Schema::create('orden_fotos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_trabajo_id')->constrained('orden_trabajos')->onDelete('cascade');
            $table->string('ruta');
            $table->timestamps();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('orden_fotos');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fichadas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empleado_id')->constrained()->onDelete('cascade');
            $table->enum('tipo', ['entrada', 'salida', 'pausa_inicio', 'pausa_fin']);
            $table->timestamp('momento');
            $table->string('origen')->nullable(); // ej: 'tablet-recepcion'
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fichadas');
    }
};

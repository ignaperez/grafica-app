<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lista_precios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre'); // Ej: Consumidor Final, Gremio, Estado
            $table->text('descripcion')->nullable();
            $table->timestamps();
            $table->decimal('multiplicador', 5, 2)->default(1.00);

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lista_precios');
    }
};

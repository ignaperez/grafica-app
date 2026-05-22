<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empleado_pagos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empleado_id')
                ->constrained('empleados')
                ->onDelete('cascade');

            // Período que estoy pagando
            $table->date('desde')->nullable();
            $table->date('hasta')->nullable();

            // Totales en HORAS (no minutos)
            $table->decimal('horas_normales', 8, 2)->default(0);
            $table->decimal('horas_extras', 8, 2)->default(0);

            // Tarifas usadas en este pago
            $table->decimal('monto_hora_normal', 12, 2)->default(0);
            $table->decimal('monto_hora_extra', 12, 2)->default(0);

            // Monto final pagado
            $table->decimal('monto_total', 12, 2)->default(0);

            $table->text('observaciones')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empleado_pagos');
    }
};

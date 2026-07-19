<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adelantos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empleado_id')->constrained('empleados')->cascadeOnDelete();
            // Se setea cuando el adelanto queda saldado en una liquidación.
            $table->foreignId('empleado_pago_id')->nullable()->constrained('empleado_pagos')->nullOnDelete();
            $table->date('fecha');
            $table->decimal('monto', 12, 2);
            $table->text('observaciones')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adelantos');
    }
};

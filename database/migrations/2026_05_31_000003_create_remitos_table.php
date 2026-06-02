<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('remitos', function (Blueprint $table) {
            $table->id();

            // Número correlativo interno (R-0001)
            $table->unsignedInteger('numero')->unique();

            // Relaciones (todas opcionales salvo cliente)
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->foreignId('presupuesto_id')->nullable()->constrained('presupuestos')->nullOnDelete();
            $table->foreignId('factura_id')->nullable()->constrained('facturas')->nullOnDelete();
            $table->foreignId('orden_trabajo_id')->nullable()->constrained('orden_trabajos')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            // Datos del remito
            $table->date('fecha');
            $table->string('estado')->default('pendiente'); // pendiente, entregado, cancelado
            $table->text('observaciones')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('remitos');
    }
};

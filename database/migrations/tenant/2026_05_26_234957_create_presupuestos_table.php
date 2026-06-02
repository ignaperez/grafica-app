<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presupuestos', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('numero')->unique(); // P-0001, P-0002…
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('lista_precio_id')->nullable()->constrained('lista_precios')->nullOnDelete();

            // Snapshot del multiplicador y MO en el momento de crear
            $table->decimal('multiplicador', 8, 4)->default(1);
            $table->decimal('mo_m2',         10, 2)->default(0);
            $table->decimal('mo_ml',         10, 2)->default(0);
            $table->decimal('mo_unidad',     10, 2)->default(0);

            $table->enum('estado', ['borrador', 'enviado', 'aprobado', 'rechazado'])->default('borrador');

            $table->date('fecha');
            $table->date('fecha_vencimiento')->nullable();
            $table->text('observaciones')->nullable();

            $table->decimal('total', 12, 2)->default(0); // suma de subtotales

            // Referencia si se convirtió en OT
            $table->foreignId('orden_trabajo_id')->nullable()->constrained('orden_trabajos')->nullOnDelete();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presupuestos');
    }
};

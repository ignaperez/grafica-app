<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('factura_borradores', function (Blueprint $table) {
            $table->id();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();

            // Snapshot completo del formulario (cliente_id, tipo, fecha, concepto,
            // doc_tipo, doc_nro, observaciones, items[], nc_*). Se restaura tal cual.
            $table->json('datos');

            // Para mostrar en la lista sin tener que decodificar el JSON
            $table->decimal('total', 12, 2)->default(0);

            // Último error (ARCA u otro) que dejó la carga como borrador
            $table->text('error')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factura_borradores');
    }
};

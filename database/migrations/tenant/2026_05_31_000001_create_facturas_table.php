<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facturas', function (Blueprint $table) {
            $table->id();

            // Relaciones
            $table->foreignId('presupuesto_id')->nullable()->constrained('presupuestos')->nullOnDelete();
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            // Identificación fiscal del comprobante
            $table->unsignedTinyInteger('tipo');          // 1=A, 6=B, 11=C
            $table->unsignedSmallInteger('punto_venta');
            $table->unsignedInteger('numero');
            $table->date('fecha');

            // CAE
            $table->string('cae', 20)->nullable();
            $table->date('cae_vencimiento')->nullable();

            // Estado
            $table->string('estado')->default('pendiente'); // pendiente, emitida, anulada

            // Receptor
            $table->unsignedTinyInteger('doc_tipo')->default(99); // 80=CUIT, 96=DNI, 99=Consumidor Final
            $table->string('doc_nro', 20)->nullable();
            $table->unsignedTinyInteger('concepto')->default(2);  // 1=Prod, 2=Serv, 3=P+S

            // Importes
            $table->decimal('imp_neto',  12, 2)->default(0);
            $table->decimal('imp_iva',   12, 2)->default(0);
            $table->decimal('imp_total', 12, 2)->default(0);

            $table->text('observaciones')->nullable();

            // Referencia al comprobante original (solo para Notas de Crédito)
            $table->unsignedTinyInteger('nc_tipo')->nullable();     // tipo del cbte original (1=A, 6=B, 11=C)
            $table->unsignedSmallInteger('nc_pto_vta')->nullable(); // punto de venta del cbte original
            $table->unsignedInteger('nc_nro')->nullable();          // número del cbte original

            $table->softDeletes();
            $table->timestamps();

            $table->unique(['tipo', 'punto_venta', 'numero']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facturas');
    }
};

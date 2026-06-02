<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('factura_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('factura_id')->constrained('facturas')->cascadeOnDelete();
            $table->string('descripcion');
            $table->decimal('cantidad',        10, 3)->default(1);
            $table->decimal('precio_unitario', 12, 2);
            $table->decimal('subtotal',        12, 2);
            $table->unsignedTinyInteger('alicuota_iva')->default(21); // 21, 10, 0
            $table->unsignedSmallInteger('orden')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factura_items');
    }
};

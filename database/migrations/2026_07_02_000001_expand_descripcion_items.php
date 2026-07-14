<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Descripciones de ítems de presupuesto, factura y remito pasan de VARCHAR(255)
    // a TEXT para poder cargar descripciones largas.
    public function up(): void
    {
        foreach (['presupuesto_items', 'factura_items', 'remito_items'] as $tabla) {
            Schema::table($tabla, function (Blueprint $table) {
                $table->text('descripcion')->change();
            });
        }
    }

    public function down(): void
    {
        foreach (['presupuesto_items', 'factura_items', 'remito_items'] as $tabla) {
            Schema::table($tabla, function (Blueprint $table) {
                $table->string('descripcion')->change();
            });
        }
    }
};

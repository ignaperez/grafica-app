<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * El número de remito es una secuencia correlativa SEPARADA por tipo
     * (interno / oficial / electronico). El unique global sobre `numero`
     * provocaba "Duplicate entry" al chocar interno#1 con oficial#1, etc.
     * Lo cambiamos por un unique compuesto (tipo, numero).
     */
    public function up(): void
    {
        Schema::table('remitos', function (Blueprint $table) {
            $table->dropUnique('remitos_numero_unique'); // unique global sobre numero
            $table->unique(['tipo', 'numero']);          // unique por tipo + numero
        });
    }

    public function down(): void
    {
        Schema::table('remitos', function (Blueprint $table) {
            $table->dropUnique(['tipo', 'numero']);
            $table->unique('numero');
        });
    }
};

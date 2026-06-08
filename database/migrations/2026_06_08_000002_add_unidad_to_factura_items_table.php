<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('factura_items', function (Blueprint $table) {
            if (!Schema::hasColumn('factura_items', 'unidad')) {
                // Unidad de medida del ítem: unidad / m2 / ml (para arrancar).
                $table->string('unidad', 20)->default('unidad')->after('cantidad');
            }
        });
    }

    public function down(): void
    {
        Schema::table('factura_items', function (Blueprint $table) {
            if (Schema::hasColumn('factura_items', 'unidad')) {
                $table->dropColumn('unidad');
            }
        });
    }
};

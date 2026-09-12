<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tratamiento de IVA por ítem: gravado (usa alicuota_iva), exento (→ ImpOpEx),
     * no_gravado (→ ImpTotConc). El precio del ítem es SIEMPRE final (IVA contenido).
     */
    public function up(): void
    {
        Schema::table('factura_items', function (Blueprint $table) {
            if (! Schema::hasColumn('factura_items', 'iva_tipo')) {
                $table->string('iva_tipo', 12)->default('gravado')->after('alicuota_iva');
            }
        });
    }

    public function down(): void
    {
        Schema::table('factura_items', function (Blueprint $table) {
            if (Schema::hasColumn('factura_items', 'iva_tipo')) {
                $table->dropColumn('iva_tipo');
            }
        });
    }
};

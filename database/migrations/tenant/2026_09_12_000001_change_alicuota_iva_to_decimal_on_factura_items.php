<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * alicuota_iva pasa de tinyint (21/10/0) a decimal(4,1) para poder
     * guardar las alícuotas con medio punto: 10,5 y 2,5.
     */
    public function up(): void
    {
        Schema::table('factura_items', function (Blueprint $table) {
            $table->decimal('alicuota_iva', 4, 1)->default(21)->change();
        });
    }

    public function down(): void
    {
        Schema::table('factura_items', function (Blueprint $table) {
            $table->unsignedTinyInteger('alicuota_iva')->default(21)->change();
        });
    }
};

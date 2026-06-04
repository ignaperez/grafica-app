<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('remitos', function (Blueprint $table) {
            // Campos para remito electrónico (WSREMV1)
            $table->string('cod_autorizacion')->nullable()->after('punto_venta');
            $table->date('cod_autorizacion_vto')->nullable()->after('cod_autorizacion');
            // El tipo ahora puede ser: interno | oficial | electronico
        });
    }

    public function down(): void
    {
        Schema::table('remitos', function (Blueprint $table) {
            $table->dropColumn(['cod_autorizacion', 'cod_autorizacion_vto']);
        });
    }
};

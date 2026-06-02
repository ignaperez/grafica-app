<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('remitos', function (Blueprint $table) {
            $table->foreignId('remito_cai_id')->nullable()->after('id')
                  ->constrained('remito_cais')->nullOnDelete();
            $table->unsignedInteger('numero_fiscal')->nullable()->after('remito_cai_id')
                  ->comment('Número dentro del rango CAI');
            $table->unsignedSmallInteger('punto_venta')->nullable()->after('numero_fiscal');
        });
    }

    public function down(): void
    {
        Schema::table('remitos', function (Blueprint $table) {
            $table->dropForeign(['remito_cai_id']);
            $table->dropColumn(['remito_cai_id', 'numero_fiscal', 'punto_venta']);
        });
    }
};

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
        Schema::table('vehiculo_ploteos', function (Blueprint $table) {
            $table->string('refe')->nullable()->after('observaciones');
            $table->string('tipo_ploteo')->default('completo')->after('refe'); // completo | parcial
            $table->string('sector')->nullable()->after('tipo_ploteo');
        });
    }

    public function down(): void
    {
        Schema::table('vehiculo_ploteos', function (Blueprint $table) {
            $table->dropColumn(['refe', 'tipo_ploteo', 'sector']);
        });
    }
};

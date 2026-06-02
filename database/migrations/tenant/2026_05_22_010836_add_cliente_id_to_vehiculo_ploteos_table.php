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
            $table->foreignId('cliente_id')->nullable()->after('orden_trabajo_id')
                  ->constrained('clientes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('vehiculo_ploteos', function (Blueprint $table) {
            $table->dropForeign(['cliente_id']);
            $table->dropColumn('cliente_id');
        });
    }
};

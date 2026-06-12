<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('presupuestos', function (Blueprint $table) {
            if (!Schema::hasColumn('presupuestos', 'nota_interna')) {
                // Nota privada/interna del presupuesto. NO se muestra en el PDF al cliente.
                $table->text('nota_interna')->nullable()->after('observaciones');
            }
        });
    }

    public function down(): void
    {
        Schema::table('presupuestos', function (Blueprint $table) {
            if (Schema::hasColumn('presupuestos', 'nota_interna')) {
                $table->dropColumn('nota_interna');
            }
        });
    }
};

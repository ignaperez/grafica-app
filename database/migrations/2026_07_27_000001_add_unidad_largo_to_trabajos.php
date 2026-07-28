<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // El trabajo pasa a describirse igual que un ítem de presupuesto (catálogo):
    // unidad (m2/ml/unidad) + largo (para ml). Ya tenía ancho/alto/cantidad.
    public function up(): void
    {
        Schema::table('trabajos', function (Blueprint $table) {
            if (!Schema::hasColumn('trabajos', 'unidad')) {
                $table->string('unidad', 10)->default('m2')->after('cantidad');
            }
            if (!Schema::hasColumn('trabajos', 'largo')) {
                $table->decimal('largo', 10, 2)->nullable()->after('alto');
            }
        });
    }

    public function down(): void
    {
        Schema::table('trabajos', function (Blueprint $table) {
            foreach (['unidad', 'largo'] as $col) {
                if (Schema::hasColumn('trabajos', $col)) $table->dropColumn($col);
            }
        });
    }
};

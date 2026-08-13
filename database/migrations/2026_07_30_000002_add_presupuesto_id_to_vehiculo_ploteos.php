<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('vehiculo_ploteos') && !Schema::hasColumn('vehiculo_ploteos', 'presupuesto_id')) {
            Schema::table('vehiculo_ploteos', function (Blueprint $t) {
                $t->foreignId('presupuesto_id')->nullable()->after('cliente_id')
                    ->constrained('presupuestos')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('vehiculo_ploteos') && Schema::hasColumn('vehiculo_ploteos', 'presupuesto_id')) {
            Schema::table('vehiculo_ploteos', function (Blueprint $t) {
                $t->dropConstrainedForeignId('presupuesto_id');
            });
        }
    }
};

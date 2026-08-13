<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('vehiculo_ploteos') && !Schema::hasColumn('vehiculo_ploteos', 'presupuestado_manual')) {
            Schema::table('vehiculo_ploteos', function (Blueprint $t) {
                $t->boolean('presupuestado_manual')->default(false)->after('presupuesto_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('vehiculo_ploteos') && Schema::hasColumn('vehiculo_ploteos', 'presupuestado_manual')) {
            Schema::table('vehiculo_ploteos', function (Blueprint $t) {
                $t->dropColumn('presupuestado_manual');
            });
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empleado_pagos', function (Blueprint $table) {
            foreach (['bonificaciones', 'descuentos', 'adelantos', 'neto'] as $col) {
                if (!Schema::hasColumn('empleado_pagos', $col)) {
                    $table->decimal($col, 12, 2)->default(0);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('empleado_pagos', function (Blueprint $table) {
            foreach (['bonificaciones', 'descuentos', 'adelantos', 'neto'] as $col) {
                if (Schema::hasColumn('empleado_pagos', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Sacar MO del pivot (MO no es por máquina, es por operario de colocación)
        Schema::table('maquina_material', function (Blueprint $table) {
            $table->dropColumn(['costo_mo_m2', 'costo_mo_ml', 'costo_mo_unidad']);
        });

        // Agregar MO override a lista_precios (null = usa el valor global de configuracion)
        Schema::table('lista_precios', function (Blueprint $table) {
            $table->decimal('mo_m2',     10, 2)->nullable()->after('multiplicador')
                  ->comment('Pisa el MO global. Null = usa configuracion global.');
            $table->decimal('mo_ml',     10, 2)->nullable()->after('mo_m2');
            $table->decimal('mo_unidad', 10, 2)->nullable()->after('mo_ml');
        });
    }

    public function down(): void
    {
        Schema::table('maquina_material', function (Blueprint $table) {
            $table->decimal('costo_mo_m2',     10, 2)->default(0);
            $table->decimal('costo_mo_ml',     10, 2)->default(0);
            $table->decimal('costo_mo_unidad', 10, 2)->default(0);
        });

        Schema::table('lista_precios', function (Blueprint $table) {
            $table->dropColumn(['mo_m2', 'mo_ml', 'mo_unidad']);
        });
    }
};

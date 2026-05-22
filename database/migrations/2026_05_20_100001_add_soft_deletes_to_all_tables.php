<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $tables = [
        'trabajos',
        'clientes',
        'productos',
        'lista_precios',
        'tipo_trabajos',
        'materiales',
        'maquinas',
        'orden_trabajos',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->softDeletes();
            });
        }

        // Migrar los registros de orden_trabajos que ya estaban "eliminados" con activo=0
        DB::table('orden_trabajos')
            ->where('activo', 0)
            ->whereNull('deleted_at')
            ->update(['deleted_at' => now()]);
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropSoftDeletes();
            });
        }
    }
};

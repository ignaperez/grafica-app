<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `orden_trabajos` arrastraba una columna de texto `cliente` (legacy) al lado de
 * la FK `cliente_id`. En Eloquent los ATRIBUTOS le ganan a las relaciones, así
 * que `$orden->cliente` devolvía esa columna —siempre vacía— en vez del Cliente
 * relacionado, incluso con eager loading. Resultado: la OT no mostraba el
 * cliente en NINGUNA vista (show, index, print, trabajos): salía "-".
 *
 * La columna está vacía en todas las filas de todos los tenants, no figura en
 * $fillable y ningún código la escribe, así que se elimina.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('orden_trabajos', 'cliente')) {
            Schema::table('orden_trabajos', function (Blueprint $table) {
                $table->dropColumn('cliente');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('orden_trabajos', 'cliente')) {
            Schema::table('orden_trabajos', function (Blueprint $table) {
                $table->string('cliente')->nullable()->after('cliente_id');
            });
        }
    }
};

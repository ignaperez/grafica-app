<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Solo agregar la columna si NO existe
        if (!Schema::hasColumn('users', 'rol')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('rol')->default('usuario');
            });
        }
    }

    public function down(): void
    {
        // Solo intentar borrar si existe
        if (Schema::hasColumn('users', 'rol')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('rol');
            });
        }
    }
};

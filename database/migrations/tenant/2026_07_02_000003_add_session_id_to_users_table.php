<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Sesión única: guarda el id de la sesión activa del usuario. Si otra PC
    // inicia sesión, este valor cambia y la sesión anterior queda invalidada.
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'session_id')) {
                $table->string('session_id')->nullable()->after('modulos');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'session_id')) {
                $table->dropColumn('session_id');
            }
        });
    }
};

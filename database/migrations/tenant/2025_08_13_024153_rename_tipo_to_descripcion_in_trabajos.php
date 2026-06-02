<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trabajos', function (Blueprint $table) {
            // Solo renombrar si existe "tipo" y no existe "descripcion"
            if (Schema::hasColumn('trabajos', 'tipo') && !Schema::hasColumn('trabajos', 'descripcion')) {
                $table->renameColumn('tipo', 'descripcion');
            }
        });
    }

    public function down(): void
    {
        Schema::table('trabajos', function (Blueprint $table) {
            // Volver atrás el cambio
            if (Schema::hasColumn('trabajos', 'descripcion') && !Schema::hasColumn('trabajos', 'tipo')) {
                $table->renameColumn('descripcion', 'tipo');
            }
        });
    }
};

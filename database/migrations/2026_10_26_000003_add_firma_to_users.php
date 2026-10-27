<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Firma ológrafa escaneada (PNG con fondo transparente) de cada usuario, para
 * estampar sobre órdenes de compra y descargarlas firmadas. Se sube una vez y
 * se reusa. Guarda solo la ruta; el archivo vive en storage/app/public/firmas.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'firma')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('firma')->nullable()->after('session_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'firma')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('firma');
            });
        }
    }
};

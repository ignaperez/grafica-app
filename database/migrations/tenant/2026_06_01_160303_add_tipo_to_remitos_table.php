<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('remitos', function (Blueprint $table) {
            if (!Schema::hasColumn('remitos', 'tipo')) {
                $table->string('tipo', 20)->default('interno')->after('estado');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('remitos', function (Blueprint $table) {
            if (Schema::hasColumn('remitos', 'tipo')) {
                $table->dropColumn('tipo');
            }
        });
    }
};

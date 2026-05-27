<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('materiales', function (Blueprint $table) {
            // El material define cómo se vende: m², ml o unidad
            $table->enum('unidad', ['m2', 'ml', 'unidad'])->default('m2')->after('nombre');
        });
    }

    public function down(): void
    {
        Schema::table('materiales', function (Blueprint $table) {
            $table->dropColumn('unidad');
        });
    }
};

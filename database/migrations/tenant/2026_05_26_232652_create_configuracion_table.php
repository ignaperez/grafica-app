<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracion', function (Blueprint $table) {
            $table->string('clave')->primary();
            $table->string('valor')->nullable();
            $table->string('descripcion')->nullable();
            $table->timestamps();
        });

        // Valores por defecto de mano de obra
        DB::table('configuracion')->insert([
            ['clave' => 'mo_m2',     'valor' => '0', 'descripcion' => 'Mano de obra por m² (colocación/instalación)', 'created_at' => now(), 'updated_at' => now()],
            ['clave' => 'mo_ml',     'valor' => '0', 'descripcion' => 'Mano de obra por metro lineal',                'created_at' => now(), 'updated_at' => now()],
            ['clave' => 'mo_unidad', 'valor' => '0', 'descripcion' => 'Mano de obra por unidad',                      'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracion');
    }
};

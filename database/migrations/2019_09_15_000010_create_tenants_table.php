<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->string('id')->primary();         // slug único: "plote", "graficaXYZ"
            $table->string('nombre');                // nombre visible de la empresa
            $table->string('tenancy_db_name')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->json('data')->nullable();        // CUIT, dirección, ARCA config, etc.
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
}

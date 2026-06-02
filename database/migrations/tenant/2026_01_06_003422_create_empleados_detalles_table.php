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
    Schema::create('empleados_detalles', function (Blueprint $table) {
        $table->id();

        $table->foreignId('empleado_id')
            ->constrained('empleados')
            ->onDelete('cascade');

        $table->string('dni', 20)->nullable();
        $table->string('cuil', 20)->nullable();
        $table->date('fecha_nacimiento')->nullable();
        $table->date('fecha_ingreso')->nullable();

        $table->string('direccion')->nullable();
        $table->string('telefono', 50)->nullable();
        $table->string('email')->nullable();

        $table->string('categoria')->nullable();
        $table->decimal('valor_hora', 12, 2)->nullable();
        $table->unsignedInteger('horas_jornada')->default(8);

        $table->text('observaciones')->nullable();

        $table->timestamps();
        $table->softDeletes();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empleados_detalles');
    }
};

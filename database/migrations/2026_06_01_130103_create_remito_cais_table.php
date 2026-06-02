<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('remito_cais', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 14)->comment('Código CAI otorgado por ARCA');
            $table->unsignedSmallInteger('punto_venta')->default(1);
            $table->unsignedTinyInteger('tipo_cbte')->default(91)->comment('91 = Remito R');
            $table->unsignedInteger('numero_desde');
            $table->unsignedInteger('numero_hasta');
            $table->unsignedInteger('ultimo_numero')->default(0)->comment('Último nro impreso con este CAI');
            $table->date('vencimiento');
            $table->boolean('activo')->default(true);
            $table->text('notas')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('remito_cais');
    }
};

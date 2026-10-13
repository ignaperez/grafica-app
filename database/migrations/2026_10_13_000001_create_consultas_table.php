<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Consultas del formulario de contacto del landing.
 *
 * Va en la base CENTRAL (no per-tenant): el formulario vive en plote.ar, que
 * es el dominio central y no inicializa tenancy. Por eso esta migración NO se
 * duplica en database/migrations/tenant/.
 *
 * Se guarda la consulta ANTES de intentar el mail: si el envío falla, rebota o
 * cae en spam, la consulta igual queda. Es el respaldo de lo que antes se
 * perdía entero (el formulario descartaba todo en el navegador).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultas', function (Blueprint $table) {
            $table->id();

            $table->string('nombre');
            $table->string('email');
            $table->string('telefono')->nullable();
            $table->string('servicio')->nullable();
            $table->text('mensaje');

            // Para rastrear origen y abuso
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 512)->nullable();

            // Resultado del envío de mail (null = todavía no se intentó)
            $table->boolean('mail_enviado')->default(false);
            $table->text('mail_error')->nullable();

            // Marca de leída, para la pantalla del super-admin
            $table->timestamp('leida_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultas');
    }
};

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
        Schema::table('orden_trabajos', function (Blueprint $table) {
            $table->foreignId('cliente_id')
                  ->after('id') // o al final si preferís
                  ->nullable()
                  ->constrained()
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orden_trabajos', function (Blueprint $table) {
            //
        });
    }
};

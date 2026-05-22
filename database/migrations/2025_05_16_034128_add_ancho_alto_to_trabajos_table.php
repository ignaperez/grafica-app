<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('trabajos', function (Blueprint $table) {
            $table->decimal('ancho', 8, 2)->nullable();
            $table->decimal('alto', 8, 2)->nullable();
        });
    }

    public function down()
    {
        Schema::table('trabajos', function (Blueprint $table) {
            $table->dropColumn(['ancho', 'alto']);
        });
    }

};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('trabajos', function (Blueprint $table) {
            $table->foreignId('producto_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('precio_unitario', 10, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('trabajos', function (Blueprint $table) {
            $table->dropForeign(['producto_id']);
            $table->dropColumn(['producto_id', 'precio_unitario']);
        });
    }
};

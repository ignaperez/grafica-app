<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('remito_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('remito_id')->constrained('remitos')->cascadeOnDelete();
            $table->string('descripcion');
            $table->decimal('cantidad', 10, 3)->default(1);
            $table->string('unidad', 30)->default('unidades'); // unidades, m², ml, kg, etc.
            $table->unsignedSmallInteger('orden')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('remito_items');
    }
};

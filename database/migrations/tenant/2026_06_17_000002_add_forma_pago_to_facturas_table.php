<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facturas', function (Blueprint $table) {
            if (!Schema::hasColumn('facturas', 'forma_pago')) {
                // Forma de pago acordada al emitir (control interno, no fiscal).
                $table->string('forma_pago')->nullable()->after('observaciones');
            }
        });
    }

    public function down(): void
    {
        Schema::table('facturas', function (Blueprint $table) {
            if (Schema::hasColumn('facturas', 'forma_pago')) {
                $table->dropColumn('forma_pago');
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Foto tomada por la tablet al fichar (evidencia anti-fraude).
    public function up(): void
    {
        Schema::table('fichadas', function (Blueprint $table) {
            if (!Schema::hasColumn('fichadas', 'foto')) {
                $table->string('foto')->nullable()->after('origen');
            }
        });
    }

    public function down(): void
    {
        Schema::table('fichadas', function (Blueprint $table) {
            if (Schema::hasColumn('fichadas', 'foto')) {
                $table->dropColumn('foto');
            }
        });
    }
};

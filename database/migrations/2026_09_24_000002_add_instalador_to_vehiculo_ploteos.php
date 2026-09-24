<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Rol "instalador": la colocación vehicular se terceriza. Producción carga el
 * vehículo y se lo asigna a un colocador; el colocador entra y ve SOLO los que
 * tiene asignados (o los que cargó él), para subir las fotos del antes/después.
 *
 * Además se separa el módulo `vehiculos` del módulo `ordenes`: hasta ahora las
 * rutas de vehículos pedían `ordenes`, y darle eso a un instalador le abriría
 * también órdenes de trabajo y trabajos. Se backfillea a los usuarios que ya
 * tenían `ordenes` para que no pierdan el acceso que ya tenían.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehiculo_ploteos', function (Blueprint $table) {
            if (! Schema::hasColumn('vehiculo_ploteos', 'instalador_id')) {
                $table->foreignId('instalador_id')->nullable()->after('cliente_id')
                    ->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('vehiculo_ploteos', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('instalador_id')
                    ->constrained('users')->nullOnDelete();
            }
        });

        // Quien ya podía ver vehículos (vía `ordenes`) tiene que seguir pudiendo.
        foreach (DB::table('users')->whereNotNull('modulos')->get(['id', 'modulos']) as $u) {
            $modulos = json_decode($u->modulos, true);

            if (! is_array($modulos)) {
                continue;
            }

            if (in_array('ordenes', $modulos, true) && ! in_array('vehiculos', $modulos, true)) {
                $modulos[] = 'vehiculos';
                DB::table('users')->where('id', $u->id)->update(['modulos' => json_encode(array_values($modulos))]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('vehiculo_ploteos', function (Blueprint $table) {
            if (Schema::hasColumn('vehiculo_ploteos', 'created_by')) {
                $table->dropConstrainedForeignId('created_by');
            }
            if (Schema::hasColumn('vehiculo_ploteos', 'instalador_id')) {
                $table->dropConstrainedForeignId('instalador_id');
            }
        });
    }
};

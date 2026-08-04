<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('marcas')) {
            Schema::create('marcas', function (Blueprint $t) {
                $t->id();
                $t->string('nombre');
                $t->boolean('activo')->default(true);
                $t->timestamps();
                $t->softDeletes();
                $t->unique('nombre');
            });
        }

        if (!Schema::hasTable('modelos_vehiculo')) {
            Schema::create('modelos_vehiculo', function (Blueprint $t) {
                $t->id();
                $t->foreignId('marca_id')->constrained('marcas')->cascadeOnDelete();
                $t->string('nombre');
                $t->boolean('activo')->default(true);
                $t->timestamps();
                $t->softDeletes();
                $t->unique(['marca_id', 'nombre']);
            });
        }

        if (Schema::hasTable('vehiculo_ploteos')) {
            Schema::table('vehiculo_ploteos', function (Blueprint $t) {
                if (!Schema::hasColumn('vehiculo_ploteos', 'marca_id')) {
                    $t->foreignId('marca_id')->nullable()->after('patente')->constrained('marcas')->nullOnDelete();
                }
                if (!Schema::hasColumn('vehiculo_ploteos', 'modelo_id')) {
                    $t->foreignId('modelo_id')->nullable()->after('marca_id')->constrained('modelos_vehiculo')->nullOnDelete();
                }
            });
        }

        // Seed base de marcas comunes (idempotente)
        $base = [
            'Ford', 'Chevrolet', 'Volkswagen', 'Toyota', 'Fiat', 'Renault', 'Peugeot',
            'Citroën', 'Mercedes-Benz', 'Iveco', 'Scania', 'Volvo', 'RAM', 'Nissan',
            'Honda', 'Jeep', 'Hyundai', 'Kia', 'Suzuki', 'Isuzu', 'JAC', 'DFSK', 'Agrale', 'MAN',
        ];
        foreach ($base as $n) {
            if (!DB::table('marcas')->where('nombre', $n)->exists()) {
                DB::table('marcas')->insert(['nombre' => $n, 'activo' => 1, 'created_at' => now(), 'updated_at' => now()]);
            }
        }

        // Backfill desde el texto existente de vehiculo_ploteos
        if (Schema::hasTable('vehiculo_ploteos')) {
            foreach (DB::table('vehiculo_ploteos')->select('id', 'marca', 'modelo')->get() as $v) {
                $marcaNombre = trim((string) $v->marca);
                if ($marcaNombre === '') continue;

                $marca = DB::table('marcas')->where('nombre', $marcaNombre)->first();
                $mid   = $marca->id ?? DB::table('marcas')->insertGetId([
                    'nombre' => $marcaNombre, 'activo' => 1, 'created_at' => now(), 'updated_at' => now(),
                ]);

                $modeloId     = null;
                $modeloNombre = trim((string) $v->modelo);
                if ($modeloNombre !== '') {
                    $modelo   = DB::table('modelos_vehiculo')->where('marca_id', $mid)->where('nombre', $modeloNombre)->first();
                    $modeloId = $modelo->id ?? DB::table('modelos_vehiculo')->insertGetId([
                        'marca_id' => $mid, 'nombre' => $modeloNombre, 'activo' => 1, 'created_at' => now(), 'updated_at' => now(),
                    ]);
                }

                DB::table('vehiculo_ploteos')->where('id', $v->id)->update(['marca_id' => $mid, 'modelo_id' => $modeloId]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('vehiculo_ploteos')) {
            Schema::table('vehiculo_ploteos', function (Blueprint $t) {
                if (Schema::hasColumn('vehiculo_ploteos', 'modelo_id')) $t->dropConstrainedForeignId('modelo_id');
                if (Schema::hasColumn('vehiculo_ploteos', 'marca_id'))  $t->dropConstrainedForeignId('marca_id');
            });
        }
        Schema::dropIfExists('modelos_vehiculo');
        Schema::dropIfExists('marcas');
    }
};

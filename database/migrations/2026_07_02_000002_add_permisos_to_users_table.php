<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // Módulos por defecto según el rol (plantilla inicial).
    private const TODOS = [
        'ordenes', 'clientes', 'presupuestos', 'facturas', 'remitos',
        'seguimiento', 'servicios', 'configuracion', 'rrhh', 'papelera',
    ];

    private function modulosPorRol(string $rol): array
    {
        return match ($rol) {
            'admin'      => self::TODOS,
            'ventas'     => ['ordenes', 'clientes', 'presupuestos', 'facturas', 'remitos', 'servicios'],
            'produccion' => ['ordenes'],
            default      => [],
        };
    }

    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'es_super')) {
                $table->boolean('es_super')->default(false)->after('rol');
            }
            if (!Schema::hasColumn('users', 'modulos')) {
                $table->json('modulos')->nullable()->after('es_super');
            }
        });

        // El primer admin (menor id) queda como Administrador principal (único).
        $primerAdmin = DB::table('users')->where('rol', 'admin')->orderBy('id')->first();

        foreach (DB::table('users')->get() as $u) {
            DB::table('users')->where('id', $u->id)->update([
                'es_super' => $primerAdmin && $u->id === $primerAdmin->id,
                'modulos'  => json_encode($this->modulosPorRol($u->rol)),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'modulos'))  $table->dropColumn('modulos');
            if (Schema::hasColumn('users', 'es_super')) $table->dropColumn('es_super');
        });
    }
};

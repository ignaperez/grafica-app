<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Módulo `precios`: controla ver IMPORTES (precio de los servicios, costos del
 * catálogo, mano de obra) y la sección Listas de precios.
 *
 * Se separa de `servicios` porque producción necesita el catálogo —para saber
 * qué combinaciones de máquina y material existen— pero no tiene por qué ver
 * cuánto cuestan.
 *
 * Reparto: lo recibe quien hoy tiene `servicios`, EXCEPTO producción e
 * instaladores. Es el único punto donde esta migración quita algo, y es a
 * propósito: es justo lo que se pidió. Si alguien de producción debe ver los
 * precios, se le tilda "Precios" en su pantalla de permisos.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (DB::table('users')->select('id', 'rol', 'modulos')->get() as $u) {
            $perm = json_decode((string) $u->modulos, true);

            if (! is_array($perm) || $perm === [] || array_is_list($perm)) {
                continue;   // sin permisos o formato viejo (no debería quedar ninguno)
            }

            if (array_key_exists('precios', $perm)) {
                continue;   // ya lo tiene: idempotente
            }

            $tieneServicios = ! empty($perm['servicios']);
            $esOperativo    = in_array($u->rol, ['produccion', 'instalador'], true);

            if (! $tieneServicios || $esOperativo) {
                continue;
            }

            $perm['precios'] = $perm['servicios'];   // mismas acciones que servicios

            DB::table('users')->where('id', $u->id)->update([
                'modulos' => json_encode($perm, JSON_UNESCAPED_UNICODE),
            ]);
        }
    }

    public function down(): void
    {
        foreach (DB::table('users')->select('id', 'modulos')->get() as $u) {
            $perm = json_decode((string) $u->modulos, true);

            if (! is_array($perm) || ! array_key_exists('precios', $perm)) {
                continue;
            }

            unset($perm['precios']);

            DB::table('users')->where('id', $u->id)->update([
                'modulos' => json_encode($perm, JSON_UNESCAPED_UNICODE),
            ]);
        }
    }
};

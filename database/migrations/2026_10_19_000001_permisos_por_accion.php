<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Pasa `users.modulos` del array plano de módulos al mapa módulo → acciones.
 *
 *   antes:  ["ordenes", "vehiculos"]
 *   ahora:  {"ordenes": ["ver","crear","editar","borrar"], "vehiculos": [...]}
 *
 * **Nadie pierde acceso**: a cada módulo que ya tenía se le dan TODAS las
 * acciones, que es exactamente lo que podía hacer antes (el módulo era
 * todo-o-nada). A partir de acá se puede recortar por acción desde la UI.
 *
 * Idempotente: si la fila ya está en el formato nuevo, no la toca.
 */
return new class extends Migration
{
    private const ACCIONES = ['ver', 'crear', 'editar', 'borrar'];

    public function up(): void
    {
        foreach (DB::table('users')->select('id', 'modulos')->get() as $u) {
            $actual = json_decode((string) $u->modulos, true);

            if (! is_array($actual) || $actual === []) {
                continue;
            }

            // ¿Ya está migrada? Las claves del formato nuevo son strings.
            if (! array_is_list($actual)) {
                continue;
            }

            $nuevo = [];
            foreach ($actual as $modulo) {
                if (is_string($modulo) && $modulo !== '') {
                    $nuevo[$modulo] = self::ACCIONES;
                }
            }

            DB::table('users')->where('id', $u->id)->update([
                'modulos' => json_encode($nuevo, JSON_UNESCAPED_UNICODE),
            ]);
        }
    }

    public function down(): void
    {
        // Vuelve al array plano: se pierde el detalle por acción, pero cada
        // módulo con algún permiso se conserva.
        foreach (DB::table('users')->select('id', 'modulos')->get() as $u) {
            $actual = json_decode((string) $u->modulos, true);

            if (! is_array($actual) || $actual === [] || array_is_list($actual)) {
                continue;
            }

            $plano = array_keys(array_filter($actual, fn ($a) => ! empty($a)));

            DB::table('users')->where('id', $u->id)->update([
                'modulos' => json_encode(array_values($plano), JSON_UNESCAPED_UNICODE),
            ]);
        }
    }
};

<?php

namespace App\Services;

use App\Models\Producto;

class CatalogoService
{
    /**
     * Catálogo Grupo → Ítem, donde Grupo = Tipo de Trabajo e Ítem = Servicio.
     * Fuente ÚNICA usada por Presupuestos y por Producción (trabajos/OT).
     * Los combos Máquina×Material se quitaron: el catálogo son los Servicios
     * (tabla productos), agrupados por su Tipo de Trabajo (obligatorio; los que
     * no encajan quedan en el tipo "OTROS"). Si algo no está en la lista, se
     * escribe a mano en la descripción del ítem.
     *
     * Cada ítem: fuente, grupo, label, descripcion, unidad, tipo_trabajo_id,
     * maquina_id, material_id, producto_id, precio, tipo.
     */
    public static function items(): array
    {
        $catalogo = [];

        $productos = Producto::with('tipoTrabajo')
            ->where('activo', true)->orderBy('nombre')->get();

        foreach ($productos as $p) {
            $grupo = $p->tipoTrabajo?->nombre ?? 'OTROS';

            $catalogo[] = [
                'fuente'          => 'producto',
                'grupo'           => $grupo,
                'label'           => $p->nombre,
                'descripcion'     => $p->descripcion ?: $p->nombre,
                'unidad'          => $p->unidad ?? 'm2',
                'tipo_trabajo_id' => $p->tipo_trabajo_id,
                'maquina_id'      => null,
                'material_id'     => null,
                'producto_id'     => $p->id,
                'precio'          => $p->precio !== null ? (float) $p->precio : null,
                'tipo'            => $grupo,
            ];
        }

        return $catalogo;
    }
}

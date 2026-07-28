<?php

namespace App\Services;

use App\Models\Maquina;
use App\Models\Producto;

class CatalogoService
{
    /**
     * Catálogo unificado Grupo → Ítem (combos Máquina×Material + Servicios/paquetes).
     * Fuente ÚNICA usada por Presupuestos y por Producción (trabajos/OT).
     * Cada ítem: fuente, grupo, label, descripcion, unidad, tipo_trabajo_id,
     * maquina_id, material_id, producto_id, precio, tipo.
     */
    public static function items(): array
    {
        $catalogo = [];

        // 1) Combos Máquina × Material
        $maquinas = Maquina::with(['tipoTrabajo', 'materiales'])
            ->where('activo', true)->orderBy('nombre')->get();

        foreach ($maquinas as $maq) {
            foreach ($maq->materiales->where('activo', true) as $mat) {
                $catalogo[] = [
                    'fuente'          => 'combo',
                    'grupo'           => $maq->tipoTrabajo?->nombre ?? 'Sin proceso',
                    'label'           => $maq->nombre . ' — ' . $mat->nombre,
                    'descripcion'     => $maq->nombre . ' — ' . $mat->nombre,
                    'unidad'          => $mat->unidad ?? 'm2',
                    'tipo_trabajo_id' => $maq->tipo_trabajo_id,
                    'maquina_id'      => $maq->id,
                    'material_id'     => $mat->id,
                    'producto_id'     => null,
                    'precio'          => null,
                    'tipo'            => $maq->tipoTrabajo?->nombre ?? 'Sin proceso',
                ];
            }
        }

        // 2) Servicios / paquetes (tabla productos)
        $productos = Producto::with('tipoTrabajo')
            ->where('activo', true)->orderBy('nombre')->get();

        foreach ($productos as $p) {
            $catalogo[] = [
                'fuente'          => 'producto',
                'grupo'           => $p->tipoTrabajo?->nombre ?? 'Otros servicios',
                'label'           => $p->nombre,
                'descripcion'     => $p->descripcion ?: $p->nombre,
                'unidad'          => $p->unidad ?? 'm2',
                'tipo_trabajo_id' => $p->tipo_trabajo_id,
                'maquina_id'      => null,
                'material_id'     => null,
                'producto_id'     => $p->id,
                'precio'          => $p->precio !== null ? (float) $p->precio : null,
                'tipo'            => $p->tipoTrabajo?->nombre ?? 'Otros servicios',
            ];
        }

        return $catalogo;
    }
}

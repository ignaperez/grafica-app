<?php

namespace App\Support;

/**
 * Mapa de ruta → (módulo, acción). Fuente única para el middleware y la UI.
 *
 * Reemplaza el esquema viejo donde el ROL era el techo y el módulo solo podía
 * recortar: ahí no se podía, por ejemplo, darle Clientes a producción por más
 * que se tildara el checkbox. Ahora el módulo manda y el rol queda como la
 * plantilla que se propone al crear el usuario.
 */
class Permisos
{
    public const ACCIONES = [
        'ver'    => 'Ver',
        'crear'  => 'Crear',
        'editar' => 'Editar',
        'borrar' => 'Borrar',
    ];

    /** Prefijo del nombre de ruta → módulo. */
    public const MAPA = [
        'ordenes-trabajo'  => 'ordenes',
        'ordenes.'         => 'ordenes',
        'trabajos-libres'  => 'ordenes',
        'trabajos.'        => 'ordenes',
        'trabajo-archivos' => 'ordenes',
        'vehiculos-ploteo' => 'vehiculos',
        'clientes'         => 'clientes',
        'presupuestos'     => 'presupuestos',
        'facturas'         => 'facturas',
        'cobros'           => 'facturas',
        'remitos'          => 'remitos',
        'remito-cais'      => 'remitos',
        'seguimientos'     => 'seguimiento',
        'productos'        => 'servicios',
        'catalogo'         => 'servicios',
        'listas-precios'   => 'servicios',
        'tipo-trabajos'    => 'configuracion',
        'materiales'       => 'configuracion',
        'maquinas'         => 'configuracion',
        'configuracion'    => 'configuracion',
        'rrhh.'            => 'rrhh',
        'papelera'         => 'papelera',
        'usuarios'         => '__super__',   // solo el Administrador principal
    ];

    /**
     * Último segmento del nombre de ruta → acción.
     * Lo que no esté acá cae al verbo HTTP (ver `accionDe`).
     */
    private const POR_NOMBRE = [
        'ver' => [
            'index', 'show', 'print', 'pdf', 'export', 'search', 'buscar', 'datos', 'ver',
            'foto', 'archivo', 'vale', 'liquidar', 'fichadas', 'hoy', 'dashboard',
            'precio-servicio', 'patente-existe', 'modelos-por-marca', 'instaladores',
            'consultar-cuit', 'debug-padron', 'parametros',
            // POST pero son lecturas: arman un PDF en memoria, no guardan nada
            'preview',
        ],
        'crear' => [
            'create', 'store', 'quick', 'marcas-store', 'modelos-store', 'desde-trabajos',
            'desde-vehiculos', 'agrupar', 'convertir-ot', 'from-presupuesto',
            'asignar-orden', 'store-multiples', 'ajax-store', 'pagos',
        ],
        'editar' => [
            'edit', 'update', 'estado', 'metadata', 'terminar', 'terminado', 'leida',
            'reenviar', 'cerrar-sesiones', 'marcar-presupuestado', 'cambiar-clave',
            // Restaurar de la papelera modifica un registro existente
            'restore', 'restaurar',
        ],
        'borrar' => [
            'destroy', 'delete', 'archivo-destroy', 'destroy-foto', 'desmarcar-presupuestado',
        ],
    ];

    /** Rutas que NO exigen el módulo Clientes completo: crear/buscar al vuelo. */
    public const CLIENTE_COMPARTIDAS = [
        'clientes.search',
        'clientes.quick',
        'clientes.consultar-cuit',
    ];

    public static function moduloDe(string $nombreRuta): ?string
    {
        foreach (self::MAPA as $prefijo => $modulo) {
            if (str_starts_with($nombreRuta, $prefijo)) {
                return $modulo;
            }
        }

        return null;
    }

    /**
     * Acción que representa la ruta. Primero por el último segmento del nombre
     * (explícito), y si no está, por el verbo HTTP.
     */
    public static function accionDe(string $nombreRuta, array $metodos = []): string
    {
        $ultimo = str_contains($nombreRuta, '.')
            ? substr(strrchr($nombreRuta, '.'), 1)
            : $nombreRuta;

        foreach (self::POR_NOMBRE as $accion => $nombres) {
            if (in_array($ultimo, $nombres, true)) {
                return $accion;
            }
        }

        if (in_array('DELETE', $metodos, true))                                   return 'borrar';
        if (in_array('PUT', $metodos, true) || in_array('PATCH', $metodos, true)) return 'editar';
        if (in_array('POST', $metodos, true))                                     return 'crear';

        return 'ver';
    }

    /** Todas las acciones, para dar permiso completo sobre un módulo. */
    public static function todas(): array
    {
        return array_keys(self::ACCIONES);
    }
}

<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /** Módulos habilitables por usuario (key => etiqueta). */
    public const MODULOS = [
        'ordenes'       => 'Órdenes / Trabajos',
        'vehiculos'     => 'Vehículos',
        'clientes'      => 'Clientes',
        'presupuestos'  => 'Presupuestos',
        'facturas'      => 'Facturas',
        'remitos'       => 'Remitos',
        'seguimiento'   => 'Seguimiento',
        'servicios'     => 'Servicios / Catálogo',
        'precios'       => 'Precios (ver importes)',
        'configuracion' => 'Configuración',
        'rrhh'          => 'RRHH',
        'papelera'      => 'Papelera',
    ];

    /** Etiqueta corta, para listar roles dentro de un texto separado por " / ". */
    private const ROLES_CORTO = [
        'admin'      => 'Admin',
        'ventas'     => 'Ventas',
        'produccion' => 'Producción',
        'instalador' => 'Instalador',
    ];

    /** Etiquetas de rol (mismo orden que los selects de usuarios). */
    public const ROLES = [
        'admin'      => 'Admin',
        'ventas'     => 'Ventas',
        'produccion' => 'Producción',
        'instalador' => 'Instalador / Colocador',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'rol',
        'es_super',
        'modulos',
        'session_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'es_super' => 'boolean',
            'modulos'  => 'array',
        ];
    }

    // ── Permisos por módulo ─────────────────────────────────────────────────

    /** ¿Es el Administrador principal (único por empresa, acceso total)? */
    public function esSuper(): bool
    {
        return (bool) $this->es_super;
    }

    /**
     * Permisos por módulo y acción.
     *
     * Formato en `users.modulos`:  {"ordenes": ["ver","crear"], "clientes": ["ver"]}
     *
     * Antes era un array plano de módulos y el ROL era el techo: el módulo solo
     * podía recortar, así que no se podía darle Clientes a producción por más
     * que se tildara el checkbox. Ahora **el módulo manda** y el rol quedó como
     * la plantilla que se propone al crear el usuario.
     *
     * Se acepta el formato viejo (array plano) por si queda algún registro sin
     * migrar: ahí se asumen TODAS las acciones, para no quitarle acceso a nadie.
     */
    public function permisos(): array
    {
        $raw = $this->modulos ?? [];
        $out = [];

        foreach ($raw as $clave => $valor) {
            if (is_int($clave)) {
                $out[$valor] = \App\Support\Permisos::todas();   // formato viejo
            } else {
                $out[$clave] = array_values(array_intersect(
                    (array) $valor,
                    \App\Support\Permisos::todas()
                ));
            }
        }

        return $out;
    }

    /** Acciones que tiene sobre un módulo. El Administrador principal, todas. */
    public function accionesDe(string $modulo): array
    {
        if ($this->esSuper()) {
            return \App\Support\Permisos::todas();
        }

        return $this->permisos()[$modulo] ?? [];
    }

    /** ¿Puede hacer esta acción en este módulo? */
    public function puede(string $modulo, string $accion = 'ver'): bool
    {
        return in_array($accion, $this->accionesDe($modulo), true);
    }

    /**
     * ¿Tiene algún acceso al módulo? Lo usan el sidebar y los botones: si no
     * puede ni verlo, el módulo no existe para él.
     */
    public function puedeModulo(string $modulo): bool
    {
        return $this->accionesDe($modulo) !== [];
    }

    /**
     * ¿Puede dar de alta / buscar clientes al vuelo? Cualquiera que cargue
     * trabajos, remitos, presupuestos o facturas necesita poder crear un
     * cliente nuevo aunque no tenga el módulo Clientes completo.
     */
    public function puedeCrearClientes(): bool
    {
        if ($this->puede('clientes', 'crear')) {
            return true;
        }

        foreach (['ordenes', 'remitos', 'presupuestos', 'facturas', 'servicios'] as $m) {
            if ($this->puede($m, 'crear')) return true;
        }

        return false;
    }

    /** Módulos por defecto según el rol (plantilla inicial). */
    /** El colocador tercerizado: solo ve los vehículos que le asignaron. */
    public function esInstalador(): bool
    {
        return $this->rol === 'instalador';
    }

    /**
     * Módulos con algún permiso. Antes se filtraba por rol (el rol era el
     * techo); ahora el módulo manda, así que son los que tienen al menos una
     * acción marcada.
     */
    public function modulosEfectivos(): array
    {
        return array_keys(array_filter($this->permisos(), fn ($acc) => $acc !== []));
    }

    /** Resumen de los permisos, para el listado de usuarios. */
    public function resumenPermisos(): string
    {
        $perm = array_filter($this->permisos(), fn ($a) => $a !== []);
        if ($perm === []) {
            return 'sin acceso';
        }

        $todas     = count(\App\Support\Permisos::todas());
        $completos = count(array_filter($perm, fn ($a) => count($a) === $todas));
        $n         = count($perm);
        $txt       = $n . ' ' . ($n === 1 ? 'módulo' : 'módulos');

        if ($completos === $n) return $txt . ' · acceso completo';
        if ($completos === 0)  return $txt . ' · solo algunas acciones';

        return $txt . ' · ' . $completos . ' completo' . ($completos === 1 ? '' : 's');
    }

    /**
     * Plantilla de permisos que se PROPONE al elegir un rol en el alta. Es solo
     * una sugerencia: después se marca lo que se quiera, módulo por módulo y
     * acción por acción. **El rol ya no limita nada.**
     */
    public static function modulosPorRol(string $rol): array
    {
        $modulos = match ($rol) {
            'admin'      => array_keys(self::MODULOS),
            'ventas'     => ['ordenes', 'vehiculos', 'clientes', 'presupuestos', 'facturas', 'remitos', 'servicios', 'precios'],
            'produccion' => ['ordenes', 'vehiculos', 'remitos'],
            // El colocador tercerizado entra solo a los vehículos que le
            // asignaron (scopeVisiblesPara) y a subir fotos → sin borrar.
            'instalador' => ['vehiculos'],
            default      => [],
        };

        $out = [];
        foreach ($modulos as $m) {
            $out[$m] = $rol === 'instalador'
                ? ['ver', 'editar']
                : \App\Support\Permisos::todas();
        }

        return $out;
    }
}

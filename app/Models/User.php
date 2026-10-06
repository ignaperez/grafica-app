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
        'configuracion' => 'Configuración',
        'rrhh'          => 'RRHH',
        'papelera'      => 'Papelera',
    ];

    /**
     * Qué roles pueden usar REALMENTE cada módulo. El módulo recorta dentro de
     * lo que el rol ya abrió (`rol:` en routes/tenant.php): nunca suma. Por eso
     * tildarle "RRHH" a un vendedor no hace nada — sus rutas son `rol:admin`.
     * Esta tabla espeja esos grupos de rutas; si se mueve un grupo, actualizarla.
     */
    public const MODULO_ROLES = [
        'ordenes'       => ['admin', 'ventas', 'produccion', 'instalador'],
        'vehiculos'     => ['admin', 'ventas', 'produccion', 'instalador'],
        'remitos'       => ['admin', 'ventas', 'produccion', 'instalador'],
        'clientes'      => ['admin', 'ventas'],
        'presupuestos'  => ['admin', 'ventas'],
        'facturas'      => ['admin', 'ventas'],
        'servicios'     => ['admin', 'ventas'],
        'configuracion' => ['admin', 'ventas'],
        'seguimiento'   => ['admin'],
        'rrhh'          => ['admin'],
        'papelera'      => ['admin'],
    ];

    /** Aclaraciones de alcance parcial, para mostrar en el formulario. */
    public const MODULO_NOTAS = [
        'configuracion' => [
            'ventas' => 'Solo tipos, materiales y máquinas (no la configuración de la empresa).',
        ],
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

    /** ¿Tiene habilitado este módulo? (el admin principal siempre). */
    public function puedeModulo(string $modulo): bool
    {
        return $this->esSuper() || in_array($modulo, $this->modulos ?? [], true);
    }

    /**
     * ¿Puede dar de alta / buscar clientes al vuelo? Cualquiera que cargue
     * trabajos, remitos, presupuestos o facturas necesita poder crear un
     * cliente nuevo aunque no tenga el módulo Clientes completo.
     */
    public function puedeCrearClientes(): bool
    {
        foreach (['ordenes', 'remitos', 'presupuestos', 'facturas', 'servicios'] as $m) {
            if ($this->puedeModulo($m)) return true;
        }
        return false;
    }

    /** Módulos por defecto según el rol (plantilla inicial). */
    /** El colocador tercerizado: solo ve los vehículos que le asignaron. */
    public function esInstalador(): bool
    {
        return $this->rol === 'instalador';
    }

    /** Módulos que ese rol puede usar (los demás no hacen nada). */
    public static function modulosDisponibles(string $rol): array
    {
        return array_values(array_filter(
            array_keys(self::MODULOS),
            fn ($m) => self::moduloAplicaA($m, $rol)
        ));
    }

    /**
     * Módulos tildados que ADEMÁS sirven con su rol. Se conservan los que no
     * aplican (por si se le cambia el rol más adelante), así que el conteo
     * crudo de `modulos` puede ser mayor que el acceso real.
     */
    public function modulosEfectivos(): array
    {
        return array_values(array_filter(
            $this->modulos ?? [],
            fn ($m) => self::moduloAplicaA($m, (string) $this->rol)
        ));
    }

    /** ¿Este módulo sirve para algo con ese rol? (ver MODULO_ROLES). */
    public static function moduloAplicaA(string $modulo, string $rol): bool
    {
        return in_array($rol, self::MODULO_ROLES[$modulo] ?? [], true);
    }

    /** Roles que pueden usar el módulo, ya etiquetados para mostrar. */
    public static function rolesDeModuloLabel(string $modulo): string
    {
        $roles = self::MODULO_ROLES[$modulo] ?? [];

        return implode(' / ', array_map(
            fn ($r) => self::ROLES_CORTO[$r] ?? ucfirst($r),
            $roles
        ));
    }

    /** Nota de alcance parcial del módulo para ese rol, si hay. */
    public static function moduloNota(string $modulo, string $rol): ?string
    {
        return self::MODULO_NOTAS[$modulo][$rol] ?? null;
    }

    public static function modulosPorRol(string $rol): array
    {
        return match ($rol) {
            'admin'      => array_keys(self::MODULOS),
            'ventas'     => ['ordenes', 'vehiculos', 'clientes', 'presupuestos', 'facturas', 'remitos', 'servicios'],
            'produccion' => ['ordenes', 'vehiculos'],
            // El instalador terceriza la colocación: solo entra a los vehículos
            // que tiene asignados, a subir fotos.
            'instalador' => ['vehiculos'],
            default      => [],
        };
    }
}

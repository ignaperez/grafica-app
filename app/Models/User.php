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
    public static function modulosPorRol(string $rol): array
    {
        return match ($rol) {
            'admin'      => array_keys(self::MODULOS),
            'ventas'     => ['ordenes', 'clientes', 'presupuestos', 'facturas', 'remitos', 'servicios'],
            'produccion' => ['ordenes'],
            default      => [],
        };
    }
}

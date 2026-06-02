<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains, SoftDeletes;

    // El ID es un string (slug), no un entero autoincremental
    public $incrementing = false;
    protected $keyType   = 'string';

    public function getIncrementing(): bool  { return false; }
    public function getKeyType(): string     { return 'string'; }
    public function shouldGenerateId(): bool { return false; } // nosotros lo pasamos manualmente

    /**
     * Columnas reales en la tabla tenants (no van al JSON data).
     */
    public static function getCustomColumns(): array
    {
        return ['id', 'nombre', 'tenancy_db_name'];
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    /** Subdominio principal de este tenant. */
    public function subdomain(): ?string
    {
        return $this->domains->first()?->domain;
    }

    /** URL del panel de este tenant. */
    public function panelUrl(): string
    {
        $sub = $this->subdomain() ?? $this->id;
        $base = config('app.url'); // http://grafica-app.test

        // Insertar el subdominio: http://grafica-app.test → http://sub.grafica-app.test
        return preg_replace('#(https?://)#', "$1{$sub}.", $base);
    }
}

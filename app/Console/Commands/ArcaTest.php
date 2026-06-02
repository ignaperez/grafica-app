<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ArcaService;

class ArcaTest extends Command
{
    protected $signature   = 'arca:test';
    protected $description = 'Prueba la conexión con ARCA (WSAA + WSFE)';

    public function handle(): int
    {
        $this->newLine();
        $this->line('  <fg=yellow>ARCA — Test de conexión</>');
        $this->line('  ' . str_repeat('─', 46));
        $this->newLine();

        // ── Configuración ─────────────────────────────────────────
        $this->line('  <fg=gray>Configuración</>');
        $this->line("  CUIT          : <fg=white>" . config('arca.cuit') . "</>");
        $this->line("  Punto de venta: <fg=white>" . config('arca.punto_venta') . "</>");
        $this->line("  Entorno       : <fg=white>" . (config('arca.production') ? '🟢 PRODUCCIÓN' : '🟡 Homologación') . "</>");
        $this->newLine();

        // ── Archivos ──────────────────────────────────────────────
        $this->line('  <fg=gray>Archivos</>');
        $cert = storage_path(config('arca.cert'));
        $key  = storage_path(config('arca.key'));

        $this->line('  Cert : ' . (file_exists($cert) ? '<fg=green>✓ existe</>' : '<fg=red>✗ NO encontrado — ' . $cert . '</>'));
        $this->line('  Key  : ' . (file_exists($key)  ? '<fg=green>✓ existe</>' : '<fg=red>✗ NO encontrado — ' . $key  . '</>'));

        if (!file_exists($cert) || !file_exists($key)) {
            $this->newLine();
            $this->error('  Faltan archivos de certificado. Abortando.');
            return self::FAILURE;
        }
        $this->newLine();

        $arca = new ArcaService();

        // ── WSAA + WSFE (tipos) ───────────────────────────────────
        $this->line('  <fg=gray>WSAA — Autenticación</>');
        $tipos = [];
        try {
            $tipos = $arca->tiposCbte();
            $this->line('  <fg=green>✓ Token WSAA obtenido correctamente</>');
        } catch (\Exception $e) {
            $this->line('  <fg=red>✗ ' . $e->getMessage() . '</>');
            $this->newLine();
            $this->error('  WSAA falló. Verificá certificado, clave y conexión a ARCA.');
            return self::FAILURE;
        }
        $this->newLine();

        // ── Tipos de comprobante ──────────────────────────────────
        $this->line('  <fg=gray>WSFE — Tipos de comprobante activos</>');
        foreach ($tipos as $id => $label) {
            $this->line("  <fg=white>  {$id}</>\t{$label}");
        }
        $this->newLine();

        // ── Último número por tipo ────────────────────────────────
        $this->line('  <fg=gray>WSFE — Último número emitido (PV ' . config('arca.punto_venta') . ')</>');
        foreach (array_keys($tipos) as $tipo) {
            try {
                $ultimo  = $arca->ultimoComprobante($tipo);
                $proximo = $ultimo + 1;
                $this->line(sprintf(
                    '  Tipo <fg=white>%2d</> %-22s último: <fg=yellow>%d</>  →  próximo: <fg=green>%d</>',
                    $tipo, $tipos[$tipo], $ultimo, $proximo
                ));
            } catch (\Exception $e) {
                $this->line("  <fg=red>Tipo {$tipo}: " . $e->getMessage() . "</>");
            }
        }

        $this->newLine();
        $this->line('  <fg=green>✓ Todo OK — ARCA responde correctamente.</>');
        $this->newLine();

        return self::SUCCESS;
    }
}

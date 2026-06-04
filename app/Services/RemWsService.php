<?php

namespace App\Services;

use Carbon\Carbon;
use Multinexo\Afip\WSAA\Wsaa;

/**
 * Servicio de Remito Electrónico ARCA/AFIP — WSREMV1.
 *
 * WSAA: mismo mecanismo que ArcaService (cert + clave privada).
 * WSREMV1: SoapClient directo con SSL SECLEVEL=1.
 *
 * Operación principal: remAutorizarRem
 */
class RemWsService
{
    protected string $certPath;
    protected string $keyPath;
    protected string $xmlDir;
    protected int    $cuit;
    protected int    $ptoVta;
    protected bool   $production;
    protected string $wsaaUrl;
    protected string $wsremUrl;
    protected string $wsremWsdlUrl;

    // URLs producción / homologación
    const WSREM_PROD = 'https://servicios1.afip.gov.ar/wsremv1/service.asmx';
    const WSREM_HOMO = 'https://wswhomo.afip.gov.ar/wsremv1/service.asmx';
    const WSAA_PROD  = 'https://wsaa.afip.gov.ar/ws/services/LoginCms';
    const WSAA_HOMO  = 'https://wsaahomo.afip.gov.ar/ws/services/LoginCms';

    // Tipos de unidad ARCA
    const UOM = [
        'unidad'    => 7,
        'unidades'  => 7,
        'un'        => 7,
        'und'       => 7,
        'u'         => 7,
        'kg'        => 1,
        'kilogramo' => 1,
        'gr'        => 40,
        'gramo'     => 40,
        'lt'        => 5,
        'litro'     => 5,
        'm'         => 2,
        'metro'     => 2,
        'm2'        => 17,
        'ml'        => 4,
        'metro_lineal' => 4,
        'par'       => 11,
        'docena'    => 25,
    ];

    public function __construct()
    {
        $t = tenant();

        $this->production = $t?->arca_production !== null
            ? (bool) $t->arca_production
            : (bool) config('arca.production');

        // CUIT
        $cuitRaw    = $t?->cuit ?: config('arca.cuit');
        $this->cuit = (int) preg_replace('/\D/', '', $cuitRaw);

        // Punto de venta para remitos electrónicos (arca_pv_rem en configuracion)
        $this->ptoVta = (int) (
            \App\Models\Configuracion::get('arca_pv_rem')
            ?: config('arca.pv_rem', 9)
        );

        // Cert y key (mismas rutas que ArcaService)
        $tenantId      = $t?->id ?? 'app';
        $basePath      = base_path('storage/app/private/arca/' . $tenantId);
        $this->certPath = file_exists("{$basePath}/cert.crt")
            ? "{$basePath}/cert.crt"
            : base_path('storage/' . config('arca.cert'));
        if (file_exists("{$basePath}/private.key")) {
            $this->keyPath = "{$basePath}/private.key";
        } elseif (file_exists(base_path("storage/app/arca/{$tenantId}/private.key"))) {
            $this->keyPath = base_path("storage/app/arca/{$tenantId}/private.key");
        } else {
            $this->keyPath = base_path('storage/' . config('arca.key'));
        }

        $this->xmlDir = base_path('storage/app/arca/xml/');
        if (!is_dir($this->xmlDir)) mkdir($this->xmlDir, 0755, true);

        $this->wsaaUrl   = $this->production ? self::WSAA_PROD  : self::WSAA_HOMO;
        $this->wsremUrl  = $this->production ? self::WSREM_PROD : self::WSREM_HOMO;
        $this->wsremWsdlUrl = $this->wsremUrl . '?WSDL';
    }

    // ── SSL workaround (DH 1024-bit — igual que ArcaService) ─────────────

    protected function sslCtx(): mixed
    {
        return stream_context_create(['ssl' => ['ciphers' => 'DEFAULT:@SECLEVEL=1']]);
    }

    protected function withAfipSsl(callable $fn): mixed
    {
        stream_context_set_default(['ssl' => ['ciphers' => 'DEFAULT:@SECLEVEL=1']]);
        try {
            return $fn();
        } finally {
            stream_context_set_default(['ssl' => ['ciphers' => 'DEFAULT:@SECLEVEL=2']]);
        }
    }

    // ── Autenticación WSAA ────────────────────────────────────────────────

    protected function getAuth(): array
    {
        $wsaa = new Wsaa();
        $wsaa->setearConfiguracion([
            'cuit'    => $this->cuit,
            'archivos' => [
                'certificado'  => $this->certPath,
                'clavePrivada' => $this->keyPath,
            ],
            'dir'        => ['xml_generados' => $this->xmlDir],
            'proxyHost'  => '',
            'proxyPort'  => '',
            'url'        => ['wsaa' => $this->wsaaUrl],
        ]);

        $taFile = $this->xmlDir . 'TA-' . $this->cuit . '-wsremv1.xml';

        $this->withAfipSsl(function () use ($wsaa, $taFile) {
            $wsaa->checkTARenovation('wsremv1');
        });

        $ta = simplexml_load_file($taFile);

        return [
            'Token' => (string) $ta->credentials->token,
            'Sign'  => (string) $ta->credentials->sign,
            'Cuit'  => $this->cuit,
        ];
    }

    // ── Cliente SOAP WSREMV1 ─────────────────────────────────────────────

    protected function wsremClient(): \SoapClient
    {
        return $this->withAfipSsl(function () {
            return new \SoapClient($this->wsremWsdlUrl, [
                'location'       => $this->wsremUrl,
                'soap_version'   => SOAP_1_2,
                'trace'          => true,
                'stream_context' => $this->sslCtx(),
                'cache_wsdl'     => WSDL_CACHE_BOTH,
            ]);
        });
    }

    // ── Último número autorizado ─────────────────────────────────────────

    public function ultimoNumero(): int
    {
        $auth   = $this->getAuth();
        $client = $this->wsremClient();

        $result = $client->remUltimoComprobante([
            'authRequest' => $auth,
            'ptoVta'      => $this->ptoVta,
            'cbteTipo'    => 91, // Remito R
        ]);

        return (int) ($result->remUltimoComprobanteReturn->cbteNro ?? 0);
    }

    // ── Autorizar remito electrónico ─────────────────────────────────────

    /**
     * @param array $datos {
     *   DocTipo: int (80=CUIT, 96=DNI, 99=CF),
     *   DocNro:  string,
     *   Nombre:  string,
     *   Domicilio: string,
     *   Items: [['descripcion'=>, 'cantidad'=>, 'unidad'=>], ...]
     * }
     */
    public function autorizar(array $datos): object
    {
        $auth   = $this->getAuth();
        $client = $this->wsremClient();
        $nro    = $this->ultimoNumero() + 1;
        $fecha  = Carbon::now()->format('Ymd');

        // Mapear ítems
        $items = [];
        foreach ($datos['Items'] as $i => $it) {
            $uomClave = mb_strtolower(trim($it['unidad'] ?? 'unidades'));
            $uomCod   = self::UOM[$uomClave] ?? 7;
            $items[]  = [
                'Pro_codigo_ncm' => '',
                'Pro_ds'         => substr($it['descripcion'], 0, 80),
                'Pro_qty'        => (float) $it['cantidad'],
                'Pro_uom'        => $uomCod,
                'Pro_precio_uni' => 0,
                'Pro_bonificacion' => 0,
                'Pro_total_item' => 0,
            ];
        }

        $payload = [
            'authRequest' => $auth,
            'remDetReq'   => [
                'arrayComprobantes' => [
                    'remDetalleReqCmp' => [
                        'PtoVta'    => $this->ptoVta,
                        'CbteTipo'  => 91,
                        'CbteDesde' => $nro,
                        'CbteHasta' => $nro,
                        'CbteFch'   => $fecha,
                        'Receptor'  => [
                            'DocTipo'   => (int) $datos['DocTipo'],
                            'DocNro'    => (int) $datos['DocNro'],
                            'Nombre'    => substr($datos['Nombre'], 0, 100),
                            'Domicilio' => substr($datos['Domicilio'] ?? '', 0, 200),
                        ],
                        'arrayItems' => [
                            'remItem' => count($items) === 1 ? $items[0] : $items,
                        ],
                    ],
                ],
            ],
        ];

        \Illuminate\Support\Facades\Log::debug('WSREMV1 request', ['payload' => json_decode(json_encode($payload), true)]);

        $result = $client->remAutorizarRem($payload);

        \Illuminate\Support\Facades\Log::debug('WSREMV1 response', ['raw' => json_decode(json_encode($result), true)]);

        $resp = $result->remAutorizarRemReturn->arrayComprobantes->remDetalleRespCmp
              ?? $result->remAutorizarRemReturn ?? null;

        if (!$resp) {
            throw new \Exception('Respuesta inesperada de WSREMV1 (ver log)');
        }

        $resultado = $resp->Resultado ?? $resp->resultado ?? 'R';

        if ($resultado === 'R' || $resultado === 'E') {
            $obs = $resp->arrayObservaciones->remObsResp ?? null;
            if ($obs) {
                $items2 = is_array($obs) ? $obs : [$obs];
                $msgs   = array_map(fn($o) => "[{$o->Code}] {$o->Msg}", $items2);
                $msg    = implode(' | ', $msgs);
            } else {
                $msg = 'Error desconocido de WSREMV1';
            }
            throw new \Exception("ARCA rechazó el remito: {$msg}");
        }

        $codAut = $resp->CodAutorizacion ?? $resp->codAutorizacion ?? null;
        $fchVto = $resp->FchVencimiento  ?? $resp->fchVencimiento  ?? null;

        return (object) [
            'numero'    => $nro,
            'cod_autorizacion'     => (string) $codAut,
            'cod_autorizacion_vto' => $fchVto
                ? Carbon::createFromFormat('Ymd', (string) $fchVto)->toDateString()
                : null,
        ];
    }

    public function getPtoVta(): int { return $this->ptoVta; }
    public function getCuit(): int   { return $this->cuit; }
}

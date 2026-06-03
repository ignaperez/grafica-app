<?php

namespace App\Services;

use Carbon\Carbon;
use Multinexo\Afip\WSFE\Wsfe;

/**
 * Servicio de facturación electrónica ARCA/AFIP.
 *
 * WSAA (autenticación): via paquete multinexo (maneja firma XML y cache de TA 12hs)
 * WSFE (facturación):   SoapClient directo (el paquete tiene bugs en PHP 8.3)
 */
class ArcaService
{
    protected string $xmlDir;
    protected string $certPath;
    protected string $keyPath;
    protected int    $cuit;
    protected int    $ptoVta;
    protected string $wsdl;
    protected string $wsfeUrl;
    protected string $wsaaUrl;
    protected bool   $production;

    public function __construct()
    {
        $this->production = (bool) config('arca.production');

        // CUIT: fuente única = configuracion (empresa_cuit), fallback al env.
        $cuitDb     = \App\Models\Configuracion::get('empresa_cuit');
        $this->cuit = (int) preg_replace('/\D/', '', $cuitDb ?: config('arca.cuit'));

        // Punto de venta: per-tenant desde configuracion, fallback al env.
        $ptoDB      = \App\Models\Configuracion::get('arca_punto_venta');
        $this->ptoVta = (int) ($ptoDB ?: config('arca.punto_venta'));

        // Cert y key: per-tenant en storage/app/private/arca/{tenant_id}/
        // Fallback a rutas legacy si no existen.
        $tenantId      = tenant()?->id ?? 'app';
        $basePath      = base_path('storage/app/private/arca/' . $tenantId);
        $this->certPath = file_exists("{$basePath}/cert.crt")
            ? "{$basePath}/cert.crt"
            : base_path('storage/' . config('arca.cert'));
        // La key puede estar en private/ (nuevos tenants) o en app/arca/{id}/ (legacy)
        if (file_exists("{$basePath}/private.key")) {
            $this->keyPath = "{$basePath}/private.key";
        } elseif (file_exists(base_path("storage/app/arca/{$tenantId}/private.key"))) {
            $this->keyPath = base_path("storage/app/arca/{$tenantId}/private.key");
        } else {
            $this->keyPath = base_path('storage/' . config('arca.key'));
        }

        $this->xmlDir = base_path('storage/app/arca/xml/');
        $this->wsdl   = base_path('vendor/multinexo/php-afip-ws/src/Multinexo/Afip/WSFE/wsfe.wsdl');

        $this->wsaaUrl = $this->production
            ? config('arca.url.wsaa_prod')
            : config('arca.url.wsaa_homo');

        $this->wsfeUrl = $this->production
            ? config('arca.url.wsfe_prod')
            : config('arca.url.wsfe_homo');

        if (! is_dir($this->xmlDir)) {
            mkdir($this->xmlDir, 0755, true);
        }
    }

    // ── SSL: SECLEVEL=1 para AFIP (DH 1024-bit workaround en OpenSSL 3.x) ──

    protected function sslCtx(): mixed
    {
        return stream_context_create(['ssl' => ['ciphers' => 'DEFAULT:@SECLEVEL=1']]);
    }

    protected function withAfipSsl(callable $fn): mixed
    {
        // El paquete multinexo crea sus propios SoapClients internamente.
        // Bajamos el SECLEVEL globalmente para esa llamada y lo restauramos.
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
        // El paquete multinexo maneja la renovación automática del TA (12hs cache)
        $wsfe = new Wsfe();
        $wsfe->setearConfiguracion([
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

        $this->withAfipSsl(fn() => $wsfe->getAutenticacion());

        $ta = simplexml_load_file($this->xmlDir . 'TA-' . $this->cuit . '-wsfe.xml');

        return [
            'Token' => (string) $ta->credentials->token,
            'Sign'  => (string) $ta->credentials->sign,
            'Cuit'  => $this->cuit,
        ];
    }

    protected function wsfeClient(): \SoapClient
    {
        return new \SoapClient($this->wsdl, [
            'location'       => $this->wsfeUrl,
            'soap_version'   => SOAP_1_2,
            'trace'          => true,
            'stream_context' => $this->sslCtx(),
        ]);
    }

    // ── Consultas WSFE ───────────────────────────────────────────────────

    public function ultimoComprobante(int $cbteTipo): int
    {
        $auth   = $this->getAuth();
        $client = $this->wsfeClient();

        $result = $client->FECompUltimoAutorizado([
            'Auth'     => $auth,
            'PtoVta'   => $this->ptoVta,
            'CbteTipo' => $cbteTipo,
        ]);

        return (int) $result->FECompUltimoAutorizadoResult->CbteNro;
    }

    // ── Emisión de comprobante ────────────────────────────────────────────

    /**
     * Solicita CAE para un comprobante.
     */
    public function solicitarCAE(array $datos): object
    {
        $auth     = $this->getAuth();
        $client   = $this->wsfeClient();
        $cbteTipo = (int) $datos['CbteTipo'];
        $nro      = $this->ultimoComprobante($cbteTipo) + 1;
        $fecha    = Carbon::now()->format('Ymd');
        $total    = round((float) $datos['ImpTotal'], 2);

        [$impNeto, $impIva, $ivaArray] = $this->calcularImpuestos($cbteTipo, $total);

        $concepto = (int) $datos['Concepto'];

        $det = [
            'Concepto'   => $concepto,
            'DocTipo'    => $datos['DocTipo'],
            'DocNro'     => $datos['DocNro'],
            'CbteDesde'  => $nro,
            'CbteHasta'  => $nro,
            'CbteFch'    => $fecha,
            'ImpTotal'   => $total,
            'ImpTotConc' => 0,
            'ImpNeto'    => $impNeto,
            'ImpOpEx'    => 0,
            'ImpIVA'     => $impIva,
            'ImpTrib'    => 0,
            'MonId'      => 'PES',
            'MonCotiz'   => 1,
        ];

        // Concepto 2 (Servicios) o 3 (Productos y Servicios): ARCA exige fechas de servicio
        if ($concepto !== 1) {
            $det['FchServDesde'] = $fecha;
            $det['FchServHasta'] = $fecha;
            $det['FchVtoPago']   = $fecha;
        }

        if ($ivaArray) {
            $det['Iva'] = $ivaArray;
        }

        // Notas de Crédito: referencia al comprobante original (obligatorio en ARCA)
        if (in_array($cbteTipo, [3, 8, 13]) && !empty($datos['NcTipo'])) {
            $det['CbtesAsoc'] = [
                'CbteAsoc' => [
                    'Tipo'   => (int) $datos['NcTipo'],
                    'PtoVta' => (int) $datos['NcPtoVta'],
                    'Nro'    => (int) $datos['NcNro'],
                    'Cuit'   => $this->cuit,
                ],
            ];
        }

        $result = $client->FECAESolicitar([
            'Auth' => $auth,
            'FeCAEReq' => [
                'FeCabReq' => [
                    'CantReg'  => 1,
                    'PtoVta'   => $this->ptoVta,
                    'CbteTipo' => $cbteTipo,
                ],
                'FeDetReq' => [
                    'FECAEDetRequest' => $det,
                ],
            ],
        ]);

        $raw  = $result->FECAESolicitarResult;
        $resp = $raw->FeDetResp->FECAEDetResponse ?? null;

        // Loguear respuesta completa para diagnóstico
        \Illuminate\Support\Facades\Log::debug('ARCA FECAESolicitar', [
            'raw' => json_decode(json_encode($raw), true),
        ]);

        if (!$resp || $resp->Resultado === 'R') {
            // Errores a nivel de ítem (Observaciones)
            $obs = $resp->Observaciones->Obs ?? null;
            if ($obs !== null) {
                $items = is_array($obs) ? $obs : [$obs];
                $msgs  = array_map(fn($o) => "[{$o->Code}] {$o->Msg}", $items);
                $msg   = implode(' | ', $msgs);
            } else {
                // Errores a nivel de request (Errors.Err)
                $err = $raw->Errors->Err ?? null;
                if ($err !== null) {
                    $items = is_array($err) ? $err : [$err];
                    $msgs  = array_map(fn($e) => "[{$e->Code}] {$e->Msg}", $items);
                    $msg   = implode(' | ', $msgs);
                } else {
                    $msg = 'Respuesta inesperada de ARCA (ver log)';
                }
            }
            throw new \Exception("ARCA rechazó el comprobante: {$msg}");
        }

        return (object) [
            'numero'          => $nro,
            'cae'             => $resp->CAE,
            'cae_vencimiento' => Carbon::createFromFormat('Ymd', $resp->CAEFchVto)->format('Y-m-d'),
        ];
    }

    // ── Helpers WSFE ─────────────────────────────────────────────────────

    protected function calcularImpuestos(int $cbteTipo, float $total): array
    {
        if (in_array($cbteTipo, [11, 13])) {
            return [$total, 0, null];
        }

        $neto = round($total / 1.21, 2);
        $iva  = round($total - $neto, 2);

        $ivaArray = [
            'AlicIva' => [
                'Id'      => 5,
                'BaseImp' => $neto,
                'Importe' => $iva,
            ],
        ];

        return [$neto, $iva, $ivaArray];
    }

    public const TIPOS_CBTE = [
        1  => 'Factura A',
        3  => 'Nota de Crédito A',
        6  => 'Factura B',
        8  => 'Nota de Crédito B',
        11 => 'Factura C',
        13 => 'Nota de Crédito C',
    ];

    public function tiposCbte(): array
    {
        try {
            $auth   = $this->getAuth();
            $client = $this->wsfeClient();

            $res   = $client->FEParamGetTiposCbte(['Auth' => $auth]);
            $items = $res->FEParamGetTiposCbteResult->ResultGet->CbteTipo ?? [];

            if (!is_array($items)) {
                $items = [$items];
            }

            $relevantes = array_keys(self::TIPOS_CBTE);
            $tipos = [];
            foreach ($items as $t) {
                $id = (int) $t->Id;
                if (in_array($id, $relevantes) && trim((string)$t->FchHasta) === 'NULL') {
                    $tipos[$id] = trim((string)$t->Desc);
                }
            }

            ksort($tipos);
            return $tipos ?: self::TIPOS_CBTE;

        } catch (\Exception $e) {
            return self::TIPOS_CBTE;
        }
    }

    public function tipoCbteLabel(int $tipo): string
    {
        return self::TIPOS_CBTE[$tipo] ?? "Comprobante {$tipo}";
    }

    public function getPtoVta(): int   { return $this->ptoVta; }
    public function getCuit(): int     { return $this->cuit; }
    /** Expone getAuthForService para debug/testing */
    public function getAuthPublic(string $service): array { return $this->getAuthForService($service); }

    // ── Autenticación genérica (para cualquier servicio ARCA) ────────────

    /**
     * Autentica con WSAA para cualquier servicio y devuelve token/sign.
     * El TA se cachea 12hs en storage/app/arca/xml/TA-{cuit}-{service}.xml
     */
    protected function getAuthForService(string $service): array
    {
        $taFile = $this->xmlDir . 'TA-' . $this->cuit . '-' . $service . '.xml';

        // Forzar eliminación del TA si está expirado (workaround para bug de checkTARenovation en PHP 8.3)
        $this->eliminarTaExpirado($taFile);

        $wsaa = new \Multinexo\Afip\WSAA\Wsaa();

        $defConf = include base_path('vendor/multinexo/php-afip-ws/src/config/config.php');
        $conf = array_replace_recursive($defConf, [
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
        $wsaa->configuracion = json_decode(json_encode($conf));

        $this->withAfipSsl(fn() => $wsaa->checkTARenovation($service));

        $ta = simplexml_load_file($taFile);

        return [
            'token' => (string) $ta->credentials->token,
            'sign'  => (string) $ta->credentials->sign,
            'cuit'  => (string) $this->cuit,
        ];
    }

    /**
     * Elimina el archivo TA si el token ya expiró.
     * El paquete multinexo/php-afip-ws tiene un bug en PHP 8.3 donde
     * checkTARenovation no detecta correctamente la expiración del token.
     */
    protected function eliminarTaExpirado(string $taFile): void
    {
        if (!file_exists($taFile)) return;

        try {
            $xml = simplexml_load_file($taFile);
            $expStr = (string) ($xml->header->expirationTime ?? '');
            if (!$expStr) return;

            // expirationTime viene en formato ISO 8601: "2024-01-01T12:00:00-03:00"
            $expTime = \Carbon\Carbon::parse($expStr);

            // Si expiró (con 5 min de margen), borramos para forzar renovación
            if ($expTime->isPast() || $expTime->diffInMinutes(now(), false) > -5) {
                @unlink($taFile);
            }
        } catch (\Throwable $e) {
            // Si el XML está corrupto, borrarlo también
            @unlink($taFile);
        }
    }

    // ── Padrón ARCA ───────────────────────────────────────────────────────

    /**
     * Consulta el Padrón ARCA para un CUIT dado.
     *
     * Estrategia (en orden de prioridad):
     *   Constancia (ws_sr_constancia_inscripcion): nombre + domicilio + condición IVA [preferido]
     *     → es el nuevo nombre de ws_sr_padron_a5. WSDL: personaServiceA5?WSDL
     *   A13 (ws_sr_padron_a13): nombre + domicilio — fallback si Constancia no está autorizado
     *
     * Se autorizan en: auth.afip.gob.ar → Adm. Relaciones → Nueva relación.
     *
     * @return array{nombre:string, direccion:string, condicion_iva:string|null, estado:string}
     * @throws \Exception
     */
    public function consultarPadron(string $cuitConsulta): array
    {
        $cuitConsulta = preg_replace('/\D/', '', $cuitConsulta);

        // ── Intento 1: Constancia de Inscripción (A5 renombrado) ─────
        try {
            return $this->consultarPadronConstancia($cuitConsulta);
        } catch (\Exception $e) {
            // Si no está autorizado, caemos a A13. Cualquier otro error lo propagamos.
            if (!$this->esErrorAutorizacion($e->getMessage())) {
                throw $e;
            }
        }

        // ── Fallback: A13 — nombre + domicilio (sin condición IVA) ───
        return $this->consultarPadronA13($cuitConsulta);
    }

    /**
     * Constancia de Inscripción — nombre + domicilio + condición IVA en un solo llamado.
     *
     * En el portal ARCA el servicio se llama "Consulta de constancia de inscripción"
     * (ws_sr_constancia_inscripcion). Es el nuevo nombre de ws_sr_padron_a5.
     * El WSDL sigue siendo personaServiceA5?WSDL.
     *
     * La respuesta incluye:
     *   datosGenerales      → nombre, domicilioFiscal, tipoPersona, estadoClave
     *   datosMonotributo    → presente si es Monotributista
     *   datosRegimenGeneral → presente si es RI / Exento
     */
    protected function consultarPadronConstancia(string $cuitConsulta): array
    {
        $auth = $this->getAuthForService('ws_sr_constancia_inscripcion');

        try {
            $client = new \SoapClient(
                'https://aws.afip.gov.ar/sr-padron/webservices/personaServiceA5?WSDL',
                ['soap_version' => SOAP_1_1, 'trace' => true, 'exceptions' => true, 'cache_wsdl' => WSDL_CACHE_BOTH, 'stream_context' => $this->sslCtx()]
            );
            $result = $client->getPersona([
                'token'            => $auth['token'],
                'sign'             => $auth['sign'],
                'cuitRepresentada' => (int) $this->cuit,
                'idPersona'        => (int) $cuitConsulta,
            ]);
        } catch (\SoapFault $e) {
            $this->throwPadronException($e, 'ws_sr_constancia_inscripcion', 'Consulta de constancia de inscripción');
        }

        $pr = $result->personaReturn ?? null;
        $dg = $pr->datosGenerales   ?? null;

        if (!$dg) {
            throw new \Exception("CUIT {$cuitConsulta} no encontrado en el padrón ARCA.");
        }

        // ── Nombre ───────────────────────────────────────────────
        // razonSocial presente = persona jurídica (independientemente del valor de tipoPersona)
        $razonSocial = trim($dg->razonSocial ?? $dg->denominacion ?? '');
        if ($razonSocial !== '') {
            $nombre = $razonSocial;
        } else {
            $apellido = $dg->apellido ?? '';
            $nombres  = $dg->nombre   ?? '';
            $nombre   = trim($apellido ? "{$apellido}, {$nombres}" : $nombres);
        }

        // ── Domicilio fiscal ──────────────────────────────────────
        $dom = $dg->domicilioFiscal ?? null;
        $direccion = '';
        if ($dom) {
            $partes = array_filter([
                $dom->direccion            ?? null,
                $dom->localidad            ?? null,
                $dom->descripcionProvincia ?? null,
                !empty($dom->codigoPostal) ? 'CP ' . $dom->codigoPostal : null,
            ]);
            $direccion = implode(', ', $partes);
        }

        // ── Condición IVA ─────────────────────────────────────────
        // datosMonotributo presente → Monotributista
        // datosRegimenGeneral presente → RI o Exento (chequear impuesto id=30)
        // ninguno → Consumidor Final
        if (!empty($pr->datosMonotributo)) {
            $condicion = 'monotributo';
        } elseif (!empty($pr->datosRegimenGeneral)) {
            $condicion = 'responsable_inscripto';
            $impuestos = $pr->datosRegimenGeneral->impuesto ?? [];
            if (!is_array($impuestos)) $impuestos = [$impuestos];
            foreach ($impuestos as $imp) {
                if ($imp === null) continue;
                if ((int)($imp->idImpuesto ?? 0) === 30) {
                    $estado = strtoupper(trim($imp->estadoImpuesto ?? ''));
                    if (in_array($estado, ['EX', 'EXENTO'])) {
                        $condicion = 'exento';
                        break;
                    }
                }
            }
        } else {
            $condicion = 'consumidor_final';
        }

        return [
            'nombre'        => $nombre ?: $cuitConsulta,
            'direccion'     => $direccion,
            'condicion_iva' => $condicion,
            'estado'        => $dg->estadoClave ?? 'DESCONOCIDO',
        ];
    }

    /**
     * Padrón A13 — nombre + domicilio (sin condición IVA).
     * WSDL: https://aws.afip.gov.ar/sr-padron/webservices/personaServiceA13?WSDL
     */
    protected function consultarPadronA13(string $cuitConsulta): array
    {
        $auth = $this->getAuthForService('ws_sr_padron_a13');

        try {
            $client = new \SoapClient(
                'https://aws.afip.gov.ar/sr-padron/webservices/personaServiceA13?WSDL',
                ['soap_version' => SOAP_1_1, 'trace' => true, 'exceptions' => true, 'cache_wsdl' => WSDL_CACHE_BOTH, 'stream_context' => $this->sslCtx()]
            );
            $result = $client->getPersona([
                'token'            => $auth['token'],
                'sign'             => $auth['sign'],
                'cuitRepresentada' => (int) $this->cuit,
                'idPersona'        => (int) $cuitConsulta,
            ]);
        } catch (\SoapFault $e) {
            $this->throwPadronException($e, 'ws_sr_padron_a13', 'Padrón Alcance 13');
        }

        $persona = $result->personaReturn->persona ?? null;
        if (!$persona) {
            throw new \Exception("CUIT {$cuitConsulta} no encontrado en el padrón ARCA.");
        }

        $razonSocial = trim($persona->razonSocial ?? $persona->denominacion ?? '');
        $tipoPersona = strtoupper($persona->tipoPersona ?? 'F');
        if ($razonSocial !== '') {
            $nombre = $razonSocial;
        } else {
            $nombre = trim(($persona->apellido ?? '') ? ($persona->apellido . ', ' . ($persona->nombre ?? '')) : ($persona->nombre ?? ''));
        }

        $domicilios = $persona->domicilio ?? [];
        if (!is_array($domicilios)) $domicilios = [$domicilios];
        $domFiscal  = null;
        foreach ($domicilios as $dom) {
            if (strtoupper($dom->tipoDomicilio ?? '') === 'FISCAL') { $domFiscal = $dom; break; }
        }
        if (!$domFiscal && !empty($domicilios)) $domFiscal = $domicilios[0];

        $direccion = '';
        if ($domFiscal) {
            $linea  = !empty($domFiscal->direccion) ? $domFiscal->direccion : trim(($domFiscal->calle ?? '') . ' ' . ($domFiscal->numero ?? ''));
            $partes = array_filter([$linea ?: null, $domFiscal->localidad ?? null, $domFiscal->descripcionProvincia ?? null, !empty($domFiscal->codigoPostal) ? 'CP ' . $domFiscal->codigoPostal : null]);
            $direccion = implode(', ', $partes);
        }

        return [
            'nombre'        => $nombre ?: $cuitConsulta,
            'direccion'     => $direccion,
            'condicion_iva' => $tipoPersona === 'J' ? 'responsable_inscripto' : null,
            'estado'        => $persona->estadoClave ?? 'DESCONOCIDO',
        ];
    }

    protected function esErrorAutorizacion(string $msg): bool
    {
        return str_contains($msg, 'notAuthorized')
            || str_contains($msg, 'no autorizado')
            || str_contains($msg, 'coe.notAuthorized')
            || str_contains($msg, 'no habilitada');
    }

    protected function throwPadronException(\SoapFault $e, string $servicio, string $nombre): void
    {
        $msg = $e->getMessage();
        if ($this->esErrorAutorizacion($msg)) {
            throw new \Exception(
                "El certificado no está autorizado para {$servicio}. " .
                "Habilitalo en auth.afip.gob.ar → Adm. Relaciones → Nueva relación → \"{$nombre}\" → computador \"plotear\"."
            );
        }
        throw new \Exception("Error SOAP ({$servicio}): {$msg}");
    }
}

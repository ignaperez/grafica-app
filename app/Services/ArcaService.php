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
        // Todo desde el tenant (cargado por super-admin), fallback al env global
        $t = tenant();

        $this->production = $t?->arca_production !== null
            ? (bool) $t->arca_production
            : (bool) config('arca.production');

        // CUIT: tenant.data.cuit → env
        $cuitRaw    = $t?->cuit ?: config('arca.cuit');
        $this->cuit = (int) preg_replace('/\D/', '', $cuitRaw);

        // Punto de venta: tenant.data.arca_punto_venta → configuracion → env
        $this->ptoVta = (int) (
            $t?->arca_punto_venta
            ?: \App\Models\Configuracion::get('arca_punto_venta')
            ?: config('arca.punto_venta')
        );

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
        // WSDL propio (copia del de AFIP + campo CondicionIVAReceptorId de la RG 5616).
        // Vive en resources/ para que composer install en el deploy NO lo pise (el de
        // vendor/multinexo es viejo y descarta el campo silenciosamente en modo WSDL).
        $this->wsdl   = base_path('resources/arca/wsfe.wsdl');

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
        // Eliminar TA expirado antes de que el paquete lo lea
        // (workaround: multinexo no detecta expiración en PHP 8.4)
        $this->eliminarTaExpirado($this->xmlDir . 'TA-' . $this->cuit . '-wsfe.xml');

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
            // Sin caché de WSDL: garantiza que se lea siempre el archivo actual
            // (evita que un WSDL cacheado viejo descarte CondicionIVAReceptorId).
            'cache_wsdl'     => WSDL_CACHE_NONE,
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

        // Importes fiscales calculados a partir de los ítems (precio final, IVA contenido).
        $imp      = $this->importesDesdeItems($datos['Items'] ?? [], $cbteTipo);
        $impNeto  = $imp['neto'];
        $impIva   = $imp['iva'];
        $total    = $imp['total'];
        $ivaArray = $imp['ivaArray'];

        $concepto = (int) $datos['Concepto'];

        $det = [
            'Concepto'   => $concepto,
            'DocTipo'    => $datos['DocTipo'],
            'DocNro'     => $datos['DocNro'],
            'CbteDesde'  => $nro,
            'CbteHasta'  => $nro,
            'CbteFch'    => $fecha,
            'ImpTotal'   => $total,
            'ImpTotConc' => $imp['noGrav'],  // no gravado
            'ImpNeto'    => $impNeto,
            'ImpOpEx'    => $imp['opex'],     // exento
            'ImpIVA'     => $impIva,
            'ImpTrib'    => 0,
            // Condición IVA del receptor — OBLIGATORIO desde RG 5616/2024.
            // Se deriva de clientes.condicion_iva; fallback a Consumidor Final (5).
            'CondicionIVAReceptorId' => $this->condicionIvaReceptorId(
                $datos['CondicionIVAReceptor'] ?? null,
                (int) $datos['DocTipo']
            ),
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

        // Loguear request + respuesta completos para diagnóstico/auditoría.
        // El request (trace=true) permite verificar que CondicionIVAReceptorId viajó a AFIP.
        \Illuminate\Support\Facades\Log::debug('ARCA FECAESolicitar', [
            'request'  => $client->__getLastRequest(),
            'raw'      => json_decode(json_encode($raw), true),
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

    /**
     * Mapa condicion_iva (app) → CondicionIVAReceptorId (AFIP, FEParamGetCondicionIvaReceptor).
     *   1 = IVA Responsable Inscripto
     *   4 = IVA Sujeto Exento
     *   5 = Consumidor Final
     *   6 = Responsable Monotributo
     */
    public const COND_IVA_RECEPTOR = [
        'responsable_inscripto' => 1,
        'exento'                => 4,
        'consumidor_final'      => 5,
        'monotributo'           => 6,
    ];

    /**
     * Resuelve el CondicionIVAReceptorId a enviar a AFIP.
     * - Si el DocTipo es 99 (Consumidor Final sin identificar) → SIEMPRE 5.
     * - Si el cliente tiene condición cargada → su código.
     * - Fallback → Consumidor Final (5).
     */
    protected function condicionIvaReceptorId(?string $condicion, int $docTipo): int
    {
        if ($docTipo === 99) {
            return 5; // Consumidor Final sin identificar
        }
        return self::COND_IVA_RECEPTOR[$condicion] ?? 5;
    }

    /**
     * Mapa alícuota (%) → AlicIva.Id de AFIP (FEParamGetTiposIva).
     *   3 = 0%   ·   9 = 2,5%   ·   8 = 5%   ·   4 = 10,5%   ·   5 = 21%   ·   6 = 27%
     */
    public const ALIC_IVA_ID = [
        '0.0'  => 3,
        '2.5'  => 9,
        '5.0'  => 8,
        '10.5' => 4,
        '21.0' => 5,
        '27.0' => 6,
    ];

    protected function alicIvaId(float $ali): int
    {
        return self::ALIC_IVA_ID[number_format($ali, 1, '.', '')] ?? 5;
    }

    /**
     * Calcula los importes fiscales de un comprobante a partir de sus ítems.
     *
     * El precio de cada ítem es SIEMPRE FINAL (lo que paga el cliente); el IVA está
     * CONTENIDO en ese precio y se retrocalcula según la alícuota:
     *   - gravado   → neto = final / (1 + alic/100); iva = final − neto  (→ ImpNeto + AlicIva)
     *   - exento    → todo el importe va a ImpOpEx, sin IVA
     *   - no_gravado→ todo el importe va a ImpTotConc, sin IVA
     * El total NUNCA se infla: ImpTotal = Σ finales.
     *
     * @param array $items  [['final'=>float, 'iva_tipo'=>'gravado'|'exento'|'no_gravado', 'alicuota'=>float], ...]
     * @param int   $cbteTipo
     * @return array{neto:float, iva:float, opex:float, noGrav:float, total:float, ivaArray:?array, desglose:array}
     */
    public function importesDesdeItems(array $items, int $cbteTipo): array
    {
        // Factura C (11) y NC C (13): monotributista, sin IVA discriminado.
        $sinIva = in_array($cbteTipo, [11, 13]);

        $neto   = 0.0;
        $iva    = 0.0;
        $opex   = 0.0;
        $noGrav = 0.0;
        $grupos = []; // clave alícuota => ['ali'=>, 'base'=>, 'iva'=>]

        foreach ($items as $it) {
            $final = round((float) ($it['final'] ?? 0), 2);
            $tipo  = $it['iva_tipo'] ?? 'gravado';

            // Factura C: sin discriminar IVA, todo el final es "neto" sin impuesto.
            if ($sinIva) { $neto += $final; continue; }

            if ($tipo === 'exento')     { $opex   += $final; continue; }
            if ($tipo === 'no_gravado') { $noGrav += $final; continue; }

            // Gravado: el IVA está contenido en el precio final → se retrocalcula.
            $ali    = (float) ($it['alicuota'] ?? 21);
            $base   = round($final / (1 + $ali / 100), 2);
            $impIva = round($final - $base, 2);

            $neto += $base;
            $iva  += $impIva;

            $k = number_format($ali, 1, '.', '');
            if (!isset($grupos[$k])) {
                $grupos[$k] = ['ali' => $ali, 'base' => 0.0, 'iva' => 0.0];
            }
            $grupos[$k]['base'] = round($grupos[$k]['base'] + $base, 2);
            $grupos[$k]['iva']  = round($grupos[$k]['iva']  + $impIva, 2);
        }

        $neto   = round($neto, 2);
        $iva    = round($iva, 2);
        $opex   = round($opex, 2);
        $noGrav = round($noGrav, 2);
        $total  = round($neto + $iva + $opex + $noGrav, 2);

        // Array Iva para AFIP: una entrada <AlicIva> por alícuota gravada (no en C/NC-C).
        $ivaArray = null;
        if (!$sinIva && $grupos) {
            $alic = [];
            foreach ($grupos as $g) {
                $alic[] = [
                    'Id'      => $this->alicIvaId($g['ali']),
                    'BaseImp' => $g['base'],
                    'Importe' => $g['iva'],
                ];
            }
            // Lista → SoapClient serializa un <AlicIva> por entrada.
            $ivaArray = ['AlicIva' => $alic];
        }

        return [
            'neto'     => $neto,
            'iva'      => $iva,
            'opex'     => $opex,
            'noGrav'   => $noGrav,
            'total'    => $total,
            'ivaArray' => $ivaArray,
            'desglose' => array_values($grupos),
        ];
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

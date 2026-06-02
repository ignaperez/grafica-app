<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $factura->tipoLabel() }} {{ $factura->numeroFormateado() }}</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #000;
            background: #e8e8e8;
            line-height: 1.35;
        }

        /* ── Toolbar de pantalla ── */
        .toolbar {
            background: #1a1a1a;
            color: #ccc;
            padding: 10px 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
        }
        .toolbar a { color: #aaa; text-decoration: none; }
        .toolbar a:hover { color: #fff; }
        .toolbar button {
            background: #e6502a;
            color: #fff;
            border: none;
            padding: 8px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 13px;
            font-family: inherit;
            font-weight: 600;
        }

        /* ── Hoja A4 ── */
        .page {
            width: 794px;
            min-height: 1122px;
            background: #fff;
            margin: 20px auto;
            padding: 22px 26px 28px;
            display: flex;
            flex-direction: column;
        }

        /* ═══════════════════════════════════
           ENCABEZADO
           ═══════════════════════════════════ */
        .hd-wrapper {
            border: 1px solid #000;
        }

        /* Franja superior: ORIGINAL / tipo / ORIGINAL */
        .hd-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #000;
            padding: 3px 10px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: .08em;
        }
        .hd-top .doc-type-name {
            font-size: 11px;
            font-weight: bold;
            letter-spacing: .12em;
        }

        /* Cuerpo del encabezado: empresa | caja letra | datos doc */
        .hd-body {
            display: flex;
            min-height: 130px;
        }

        /* Columna izquierda: info empresa */
        .hd-empresa {
            flex: 1;
            padding: 10px 12px;
            border-right: 1px solid #000;
        }
        .hd-empresa .empresa-nombre {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .hd-empresa .empresa-row {
            font-size: 10px;
            color: #222;
            margin-bottom: 2px;
        }
        .hd-empresa .empresa-row .lbl {
            font-weight: bold;
        }

        /* Columna central: caja letra */
        .hd-letra {
            width: 90px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border-right: 1px solid #000;
            padding: 8px 6px;
            text-align: center;
            gap: 4px;
        }
        .letra-box {
            border: 3px solid #000;
            width: 54px;
            height: 54px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            font-weight: bold;
            line-height: 1;
        }
        .letra-cod {
            font-size: 9px;
            font-weight: bold;
            letter-spacing: .05em;
        }

        /* Columna derecha: datos del comprobante */
        .hd-doc {
            flex: 1;
            padding: 10px 12px;
        }
        .hd-doc-row {
            font-size: 10px;
            margin-bottom: 3px;
            color: #000;
        }
        .hd-doc-row .lbl {
            font-weight: bold;
        }
        .hd-doc-sep {
            border-top: 1px solid #ccc;
            margin: 6px 0;
        }
        .hd-doc-num {
            font-size: 14px;
            font-weight: bold;
            letter-spacing: .03em;
            margin-bottom: 4px;
        }

        /* ═══════════════════════════════════
           RECEPTOR (fondo gris)
           ═══════════════════════════════════ */
        .receptor {
            border: 1px solid #000;
            border-top: none;
            background: #f2f2f2;
        }
        .receptor-row {
            display: flex;
            border-bottom: 1px solid #aaa;
            font-size: 10px;
        }
        .receptor-row:last-child {
            border-bottom: none;
        }
        .receptor-cell {
            flex: 1;
            padding: 4px 8px;
            border-right: 1px solid #aaa;
        }
        .receptor-cell:last-child {
            border-right: none;
        }
        .receptor-cell .lbl {
            font-weight: bold;
        }

        /* ═══════════════════════════════════
           REFERENCIA COMPROBANTE ORIG (NC)
           ═══════════════════════════════════ */
        .nc-ref {
            margin-top: 8px;
            padding: 6px 10px;
            border: 1px solid #c8a000;
            background: #fffbe6;
            font-size: 10px;
            color: #7a5f00;
        }

        /* ═══════════════════════════════════
           TABLA DE ÍTEMS
           ═══════════════════════════════════ */
        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.items thead th {
            background: #e0e0e0;
            border: 1px solid #555;
            padding: 5px 4px;
            font-size: 9px;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
            line-height: 1.2;
        }
        table.items thead th.l { text-align: left; }
        table.items tbody td {
            border: 1px solid #aaa;
            padding: 5px 4px;
            font-size: 10px;
            vertical-align: top;
        }
        table.items tbody td.r { text-align: right; }
        table.items tbody td.c { text-align: center; }
        table.items tbody tr:last-child td { border-bottom: 1px solid #555; }
        .td-desc { max-width: 180px; }
        .td-muted { color: #666; font-size: 9px; }

        /* ═══════════════════════════════════
           TOTALES
           ═══════════════════════════════════ */
        .totales-wrap {
            display: flex;
            justify-content: flex-end;
            margin-top: 6px;
        }
        .totales {
            min-width: 340px;
            border: 1px solid #555;
        }
        .tot-row {
            display: flex;
            border-bottom: 1px solid #ccc;
            font-size: 10px;
        }
        .tot-row:last-child { border-bottom: none; }
        .tot-label {
            flex: 1;
            padding: 4px 8px;
            border-right: 1px solid #ccc;
            color: #333;
        }
        .tot-value {
            min-width: 110px;
            padding: 4px 8px;
            text-align: right;
        }
        .tot-row.total-final {
            background: #000;
            color: #fff;
            font-weight: bold;
            font-size: 12px;
            border-top: 2px solid #000;
        }
        .tot-row.total-final .tot-label { border-right-color: #444; color: #fff; }

        /* ═══════════════════════════════════
           PIE: QR + CAE
           ═══════════════════════════════════ */
        .pie {
            margin-top: auto;
            padding-top: 14px;
            border-top: 1px solid #000;
        }
        .pie-body {
            display: flex;
            gap: 16px;
            align-items: flex-start;
        }
        .pie-qr img {
            width: 90px;
            height: 90px;
            display: block;
        }
        .pie-qr .qr-placeholder {
            width: 90px;
            height: 90px;
            border: 1px solid #ccc;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8px;
            color: #999;
            text-align: center;
        }
        .pie-cae {
            flex: 1;
            font-size: 10px;
            line-height: 1.6;
        }
        .pie-cae .cae-autorizado {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 4px;
        }
        .pie-cae .cae-num {
            font-size: 11px;
            letter-spacing: .03em;
        }
        .pie-cae .cae-vto {
            font-size: 10px;
            color: #333;
        }
        .pie-right {
            text-align: right;
            font-size: 9px;
            color: #555;
            line-height: 1.6;
        }
        .pie-right .pag { font-size: 10px; font-weight: bold; color: #000; }

        /* Observaciones */
        .obs-block {
            margin-top: 10px;
            font-size: 10px;
            color: #333;
            border-top: 1px solid #ddd;
            padding-top: 6px;
        }
        .obs-block .obs-label { font-weight: bold; margin-right: 6px; }

        /* ═══════════════════════════════════
           IMPRESIÓN
           ═══════════════════════════════════ */
        @media print {
            body { background: #fff; }
            .toolbar { display: none; }
            .page {
                width: auto;
                min-height: 0;
                margin: 0;
                padding: 12mm 15mm 12mm;
            }
        }
    </style>
</head>
<body>

{{-- Toolbar de pantalla (oculto en impresión) --}}
<div class="toolbar">
    <a href="{{ route('facturas.show', $factura->id) }}">← Volver a la factura</a>
    <button onclick="window.print()">🖨 Imprimir / Guardar PDF</button>
</div>

@php
    $empresa = \App\Models\Configuracion::empresa();

    $tipo            = (int) $factura->tipo;
    $esNC            = in_array($tipo, [3, 8, 13]);
    $esC             = in_array($tipo, [11, 13]);  // Factura C / NC-C (monotributista, sin IVA)
    $ivaDiscriminado = in_array($tipo, [1, 3]);    // Solo Factura A / NC-A: IVA se muestra por separado

    $letra = match($tipo) {
        1, 3    => 'A',
        6, 8    => 'B',
        11, 13  => 'C',
        default => '?',
    };
    $codTipo = str_pad($tipo, 2, '0', STR_PAD_LEFT);

    $tipoLabel = $esNC ? 'NOTA DE CRÉDITO' : 'FACTURA';

    // Formateo de CUIT: XX-XXXXXXXX-X
    $fmtCuit = function(string $c): string {
        $c = preg_replace('/\D/', '', $c);
        if (strlen($c) === 11) {
            return substr($c,0,2) . '-' . substr($c,2,8) . '-' . substr($c,10);
        }
        return $c ?: '—';
    };

    // Etiqueta condición IVA receptor
    $condIvaLabel = match($factura->cliente->condicion_iva ?? '') {
        'responsable_inscripto' => 'IVA Responsable Inscripto',
        'monotributo'           => 'Monotributista',
        'exento'                => 'IVA Exento',
        'consumidor_final'      => 'Consumidor Final',
        default                 => $factura->cliente->condicion_iva ?? '—',
    };

    // Etiqueta tipo documento receptor
    $docTipoLabel = match((int) $factura->doc_tipo) {
        80      => 'CUIT',
        96      => 'DNI',
        99      => 'Consumidor Final',
        default => 'Doc. ' . $factura->doc_tipo,
    };

    // Concepto
    $conceptoLabel = match($factura->concepto) {
        1       => 'Productos',
        2       => 'Servicios',
        3       => 'Productos y Servicios',
        default => '—',
    };

    // Desglose IVA por alícuota (solo necesario para Factura A)
    $desgloseIva = [];
    $sumaBase    = 0;

    if ($ivaDiscriminado) {
        foreach ($factura->items as $item) {
            $ali    = (float) $item->alicuota_iva;
            $factor = $ali > 0 ? (1 + $ali / 100) : 1;
            $base   = round((float) $item->subtotal / $factor, 2);
            $iva    = round((float) $item->subtotal - $base, 2);
            $sumaBase += $base;

            $key = number_format($ali, 2, '.', '');
            if (! isset($desgloseIva[$key])) {
                $desgloseIva[$key] = ['ali' => $ali, 'base' => 0, 'iva' => 0];
            }
            $desgloseIva[$key]['base'] = round($desgloseIva[$key]['base'] + $base, 2);
            $desgloseIva[$key]['iva']  = round($desgloseIva[$key]['iva']  + $iva,  2);
        }
        ksort($desgloseIva);
        $sumaBase = round($sumaBase, 2);
    }
@endphp

<div class="page">

    {{-- ════════════════════════════════════════════
         ENCABEZADO
         ════════════════════════════════════════════ --}}
    <div class="hd-wrapper">

        {{-- Franja ORIGINAL / nombre del tipo / ORIGINAL --}}
        <div class="hd-top">
            <span>ORIGINAL</span>
            <span class="doc-type-name">{{ $tipoLabel }}</span>
            <span>ORIGINAL</span>
        </div>

        {{-- Cuerpo: empresa izq. | caja letra | datos doc der. --}}
        <div class="hd-body">

            {{-- Empresa --}}
            <div class="hd-empresa">
                <div class="empresa-nombre">{{ $empresa['nombre'] }}</div>

                @if($empresa['propietario'])
                    <div class="empresa-row">{{ $empresa['propietario'] }}</div>
                @endif

                @if($empresa['direccion'])
                    <div class="empresa-row">
                        <span class="lbl">Domicilio Comercial:</span> {{ $empresa['direccion'] }}
                    </div>
                @endif

                @if($empresa['condicion_iva'])
                    <div class="empresa-row">
                        <span class="lbl">Condición frente al IVA:</span> {{ $empresa['condicion_iva'] }}
                    </div>
                @endif

                @if($empresa['telefono'])
                    <div class="empresa-row" style="margin-top:4px">Tel: {{ $empresa['telefono'] }}</div>
                @endif

                @if($empresa['email'])
                    <div class="empresa-row">{{ $empresa['email'] }}</div>
                @endif
            </div>

            {{-- Caja de letra (A / B / C) --}}
            <div class="hd-letra">
                <div class="letra-box">{{ $letra }}</div>
                <div class="letra-cod">COD. {{ $codTipo }}</div>
            </div>

            {{-- Datos del comprobante --}}
            <div class="hd-doc">
                <div class="hd-doc-row">
                    <span class="lbl">Razón Social:</span> {{ $empresa['nombre'] }}
                </div>

                @if($empresa['cuit'])
                    <div class="hd-doc-row">
                        <span class="lbl">CUIT:</span> {{ $fmtCuit($empresa['cuit']) }}
                    </div>
                @endif

                @if($empresa['iibb'])
                    <div class="hd-doc-row">
                        <span class="lbl">Ingresos Brutos:</span> {{ $empresa['iibb'] }}
                    </div>
                @endif

                @if($empresa['inicio_actividades'])
                    <div class="hd-doc-row">
                        <span class="lbl">Inicio de Actividades:</span> {{ $empresa['inicio_actividades'] }}
                    </div>
                @endif

                <div class="hd-doc-sep"></div>

                <div class="hd-doc-num">
                    Pto. Vta. {{ str_pad($factura->punto_venta, 5, '0', STR_PAD_LEFT) }}
                    &nbsp;–&nbsp;
                    Comp. N° {{ str_pad($factura->numero, 8, '0', STR_PAD_LEFT) }}
                </div>

                <div class="hd-doc-row">
                    <span class="lbl">Fecha de Emisión:</span> {{ $factura->fecha->format('d/m/Y') }}
                </div>

                <div class="hd-doc-row">
                    <span class="lbl">Concepto:</span> {{ $conceptoLabel }}
                </div>

                @if($factura->cae)
                    <div class="hd-doc-row" style="margin-top:4px;color:#1a6b33">
                        <span class="lbl">CAE:</span> {{ $factura->cae }}
                    </div>
                    <div class="hd-doc-row" style="color:#1a6b33">
                        <span class="lbl">Vto. CAE:</span> {{ $factura->cae_vencimiento?->format('d/m/Y') }}
                    </div>
                @endif
            </div>

        </div>{{-- /hd-body --}}
    </div>{{-- /hd-wrapper --}}

    {{-- ════════════════════════════════════════════
         RECEPTOR (fondo gris)
         ════════════════════════════════════════════ --}}
    <div class="receptor">

        <div class="receptor-row">
            <div class="receptor-cell" style="flex:0 0 210px">
                <span class="lbl">CUIT:</span>
                {{ $factura->cliente->cuit ? $fmtCuit($factura->cliente->cuit) : ($factura->doc_nro ?: '—') }}
            </div>
            <div class="receptor-cell">
                <span class="lbl">Apellido y Nombre / Razón Social:</span>
                {{ $factura->cliente->nombre }}
            </div>
        </div>

        <div class="receptor-row">
            <div class="receptor-cell" style="flex:0 0 210px">
                <span class="lbl">Condición frente al IVA:</span>
                {{ $condIvaLabel }}
            </div>
            <div class="receptor-cell">
                <span class="lbl">Domicilio Comercial:</span>
                {{ $factura->cliente->direccion ?: '—' }}
            </div>
        </div>

        <div class="receptor-row">
            <div class="receptor-cell" style="flex:0 0 210px">
                <span class="lbl">Condición de Venta:</span>
                {{ $factura->observaciones ?: 'Contado' }}
            </div>
            <div class="receptor-cell">
                <span class="lbl">{{ $docTipoLabel }}:</span>
                @if((int)$factura->doc_tipo === 99)
                    Consumidor Final
                @else
                    {{ $factura->doc_nro ?: '—' }}
                @endif
            </div>
        </div>

    </div>{{-- /receptor --}}

    {{-- Referencia comprobante original (solo para NC) --}}
    @if($esNC && $factura->nc_nro)
        <div class="nc-ref">
            <strong>Acredita comprobante original:</strong>
            {{ \App\Services\ArcaService::TIPOS_CBTE[$factura->nc_tipo] ?? "Tipo {$factura->nc_tipo}" }}
            — Pto. Vta. {{ str_pad($factura->nc_pto_vta, 4, '0', STR_PAD_LEFT) }}
            Comp. N° {{ str_pad($factura->nc_nro, 8, '0', STR_PAD_LEFT) }}
        </div>
    @endif

    {{-- ════════════════════════════════════════════
         TABLA DE ÍTEMS
         ════════════════════════════════════════════ --}}
    @if($ivaDiscriminado)
    {{-- ── Factura A: IVA discriminado por ítem ── --}}
    {{-- Precio Unitario y Subtotal se muestran SIN IVA; la última columna suma el IVA --}}
    <table class="items">
        <thead>
            <tr>
                <th style="width:28px">Cód.</th>
                <th class="l" style="min-width:160px">Producto / Servicio</th>
                <th style="width:55px">Cantidad</th>
                <th style="width:52px">U.<br>Medida</th>
                <th style="width:78px">Precio<br>Unitario</th>
                <th style="width:46px">%<br>Bonif.</th>
                <th style="width:78px">Subtotal</th>
                <th style="width:58px">Alíc.<br>IVA</th>
                <th style="width:82px">Subtotal<br>c/IVA</th>
            </tr>
        </thead>
        <tbody>
            @foreach($factura->items as $i => $item)
            @php
                $ali       = (float) $item->alicuota_iva;
                $factor    = $ali > 0 ? (1 + $ali / 100) : 1;
                $pNeto     = round((float) $item->precio_unitario / $factor, 2);
                $subNeto   = round((float) $item->subtotal / $factor, 2);
                $subCIva   = (float) $item->subtotal;
                $aliLabel  = $ali > 0 ? number_format($ali, 0) . ' %' : 'Exento';
            @endphp
            <tr>
                <td class="c td-muted">{{ $i + 1 }}</td>
                <td class="td-desc">{{ $item->descripcion }}</td>
                <td class="r">{{ rtrim(rtrim(number_format((float)$item->cantidad, 3, ',', '.'), '0'), ',') }}</td>
                <td class="c td-muted">Un.</td>
                <td class="r">{{ number_format($pNeto, 2, ',', '.') }}</td>
                <td class="c">0,00</td>
                <td class="r">{{ number_format($subNeto, 2, ',', '.') }}</td>
                <td class="c">{{ $aliLabel }}</td>
                <td class="r">{{ number_format($subCIva, 2, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    {{-- ── Factura B / C: precio final sin discriminar IVA ── --}}
    <table class="items">
        <thead>
            <tr>
                <th style="width:28px">Cód.</th>
                <th class="l">Producto / Servicio</th>
                <th style="width:70px">Cantidad</th>
                <th style="width:52px">U.<br>Medida</th>
                <th style="width:100px">Precio<br>Unitario</th>
                <th style="width:52px">%<br>Bonif.</th>
                <th style="width:100px">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($factura->items as $i => $item)
            <tr>
                <td class="c td-muted">{{ $i + 1 }}</td>
                <td class="td-desc">{{ $item->descripcion }}</td>
                <td class="r">{{ rtrim(rtrim(number_format((float)$item->cantidad, 3, ',', '.'), '0'), ',') }}</td>
                <td class="c td-muted">Un.</td>
                <td class="r">{{ number_format((float)$item->precio_unitario, 2, ',', '.') }}</td>
                <td class="c">0,00</td>
                <td class="r">{{ number_format((float)$item->subtotal, 2, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- ════════════════════════════════════════════
         TOTALES
         ════════════════════════════════════════════ --}}
    <div class="totales-wrap">
        <div class="totales">

            @if($ivaDiscriminado)
                {{-- ── Factura A: desglose obligatorio Neto + IVA por alícuota ── --}}
                <div class="tot-row">
                    <div class="tot-label">Importe Neto Gravado</div>
                    <div class="tot-value">$ {{ number_format($sumaBase, 2, ',', '.') }}</div>
                </div>

                @foreach($desgloseIva as $d)
                    @if($d['ali'] > 0)
                    <div class="tot-row">
                        <div class="tot-label">
                            IVA {{ number_format($d['ali'], 0) }}%
                            <span style="font-size:9px;color:#666">(sobre&nbsp;$&nbsp;{{ number_format($d['base'], 2, ',', '.') }})</span>
                        </div>
                        <div class="tot-value">$ {{ number_format($d['iva'], 2, ',', '.') }}</div>
                    </div>
                    @else
                    <div class="tot-row">
                        <div class="tot-label">Importe Exento</div>
                        <div class="tot-value">$ {{ number_format($d['base'], 2, ',', '.') }}</div>
                    </div>
                    @endif
                @endforeach

                <div class="tot-row">
                    <div class="tot-label">Otros Tributos</div>
                    <div class="tot-value">$ 0,00</div>
                </div>
            @endif

            {{-- Importe Total (todas las facturas) --}}
            <div class="tot-row total-final">
                <div class="tot-label">IMPORTE TOTAL</div>
                <div class="tot-value">$ {{ number_format((float)$factura->imp_total, 2, ',', '.') }}</div>
            </div>

        </div>
    </div>

    {{-- ════════════════════════════════════════════
         PIE: QR + CAE + "Comprobante Autorizado"
         ════════════════════════════════════════════ --}}
    <div class="pie">
        <div class="pie-body">

            {{-- QR ARCA --}}
            <div class="pie-qr">
                @if($factura->tieneCAE())
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ urlencode($factura->qrUrl()) }}"
                         alt="QR ARCA">
                @else
                    <div class="qr-placeholder">Sin<br>CAE</div>
                @endif
            </div>

            {{-- Info CAE --}}
            <div class="pie-cae">
                @if($factura->tieneCAE())
                    <div class="cae-autorizado">Comprobante Autorizado</div>
                    <div class="cae-num">
                        <strong>CAE N°:</strong> {{ $factura->cae }}
                    </div>
                    <div class="cae-vto">
                        <strong>Fecha Vto. de CAE:</strong> {{ $factura->cae_vencimiento?->format('d/m/Y') }}
                    </div>
                @else
                    <div style="color:#c00;font-weight:bold">Comprobante sin CAE — No válido fiscalmente</div>
                @endif
            </div>

            {{-- Paginado + datos empresa --}}
            <div class="pie-right">
                <div class="pag">Pág. 1/1</div>
                <div style="margin-top:6px">{{ $empresa['nombre'] }}</div>
                @if($empresa['cuit'])
                    <div>CUIT: {{ $fmtCuit($empresa['cuit']) }}</div>
                @endif
            </div>

        </div>

        {{-- Observaciones (si hay texto y no está en condicion_venta) --}}
        @if($factura->observaciones && $factura->observaciones !== 'Contado')
            <div class="obs-block">
                <span class="obs-label">Observaciones:</span>
                {{ $factura->observaciones }}
            </div>
        @endif

    </div>{{-- /pie --}}

</div>{{-- /page --}}
</body>
</html>

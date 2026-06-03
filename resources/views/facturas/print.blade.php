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

        /* ── Toolbar ── */
        .toolbar {
            background: #1a1a1a; color: #ccc;
            padding: 10px 28px;
            display: flex; justify-content: space-between; align-items: center;
            font-size: 13px; gap: 12px;
        }
        .toolbar a { color: #aaa; text-decoration: none; }
        .toolbar a:hover { color: #fff; }
        .tb-btns { display: flex; gap: 8px; }
        .tb-btn {
            background: #e6502a; color: #fff; border: none;
            padding: 8px 20px; border-radius: 5px; cursor: pointer;
            font-size: 13px; font-family: inherit; font-weight: 600;
        }
        .tb-btn.ghost {
            background: transparent; border: 1px solid #555; color: #ccc;
        }
        .tb-btn.ghost:hover { border-color: #aaa; color: #fff; }

        /* ── Hoja A4 ── */
        .page {
            width: 794px; min-height: 1122px; background: #fff;
            margin: 20px auto; padding: 22px 26px 28px;
            display: flex; flex-direction: column;
        }

        /* ═══ ENCABEZADO ═══ */
        .hd-wrapper { border: 1px solid #000; }
        .hd-top {
            display: flex; justify-content: space-between; align-items: center;
            border-bottom: 1px solid #000; padding: 3px 10px;
            font-size: 9px; font-weight: bold;
            text-transform: uppercase; letter-spacing: .08em;
        }
        .hd-top .doc-type-name { font-size: 11px; font-weight: bold; letter-spacing: .12em; }
        .hd-body { display: flex; min-height: 130px; }

        /* Empresa (izq) */
        .hd-empresa { flex: 1; padding: 10px 12px; border-right: 1px solid #000; }
        .hd-empresa .logo-wrap { margin-bottom: 8px; }
        .hd-empresa .logo-wrap img { max-height: 55px; max-width: 155px; object-fit: contain; display: block; }
        .hd-empresa .emp-nombre { font-size: 13px; font-weight: bold; margin-bottom: 4px; }
        .hd-empresa .emp-row { font-size: 10px; color: #222; margin-bottom: 2px; }

        /* Letra (centro) */
        .hd-letra {
            width: 90px; display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            border-right: 1px solid #000; padding: 8px 6px;
            text-align: center; gap: 4px;
        }
        .letra-box {
            border: 3px solid #000; width: 54px; height: 54px;
            display: flex; align-items: center; justify-content: center;
            font-size: 32px; font-weight: bold; line-height: 1;
        }
        .letra-cod { font-size: 9px; font-weight: bold; letter-spacing: .05em; }

        /* Doc (der) */
        .hd-doc { flex: 1; padding: 10px 12px; }
        .hd-doc-title { font-size: 16px; font-weight: bold; margin-bottom: 6px; }
        .hd-doc-row { font-size: 10px; margin-bottom: 3px; }
        .hd-doc-row .lbl { font-weight: bold; }
        .hd-doc-sep { border-top: 1px solid #ccc; margin: 7px 0; }
        .hd-doc-iva { font-size: 11px; font-weight: bold; text-transform: uppercase; margin-bottom: 3px; }

        /* ── Vto pago ── */
        .vto-pago {
            border: 1px solid #000; border-top: none;
            padding: 5px 10px; font-size: 10px; font-weight: bold;
        }

        /* ═══ RECEPTOR ═══ */
        .receptor { border: 1px solid #000; border-top: none; background: #f2f2f2; }
        .receptor-row { display: flex; border-bottom: 1px solid #aaa; font-size: 10px; }
        .receptor-row:last-child { border-bottom: none; }
        .receptor-cell { flex: 1; padding: 5px 8px; border-right: 1px solid #aaa; }
        .receptor-cell:last-child { border-right: none; }
        .receptor-cell .lbl { font-weight: bold; }

        /* ── NC referencia ── */
        .nc-ref {
            margin-top: 8px; padding: 6px 10px;
            border: 1px solid #c8a000; background: #fffbe6;
            font-size: 10px; color: #7a5f00;
        }

        /* ═══ TABLA ÍTEMS ═══ */
        table.items { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.items thead th {
            background: #e0e0e0; border: 1px solid #555;
            padding: 5px 4px; font-size: 9px; font-weight: bold;
            text-align: center; vertical-align: middle; line-height: 1.2;
        }
        table.items thead th.l { text-align: left; }
        table.items tbody td { border: 1px solid #aaa; padding: 5px 4px; font-size: 10px; vertical-align: top; }
        table.items tbody td.r { text-align: right; }
        table.items tbody td.c { text-align: center; }
        table.items tbody tr:last-child td { border-bottom: 1px solid #555; }
        .td-num { color: #555; font-size: 9px; }

        /* ═══ TOTALES ═══ */
        .totales-wrap { display: flex; justify-content: flex-end; margin-top: 8px; }
        .totales { min-width: 360px; border: 1px solid #555; }
        .tot-row { display: flex; border-bottom: 1px solid #ccc; font-size: 10px; }
        .tot-row:last-child { border-bottom: none; }
        .tot-label { flex: 1; padding: 4px 8px; border-right: 1px solid #ccc; color: #333; }
        .tot-value { min-width: 120px; padding: 4px 8px; text-align: right; }
        .tot-row.total-final {
            background: #000; color: #fff; font-weight: bold; font-size: 13px;
        }
        .tot-row.total-final .tot-label { border-right-color: #444; color: #fff; }

        /* Monto en letras */
        .monto-letras { text-align: right; font-size: 9px; color: #444; font-style: italic; margin-top: 3px; }

        /* ── Régimen Transparencia Fiscal ── */
        .transparencia {
            margin-top: 10px; border: 1px solid #ccc;
            padding: 7px 12px; max-width: 400px;
        }
        .transparencia .trans-titulo { font-style: italic; margin-bottom: 5px; color: #333; font-size: 9px; }
        .transparencia .trans-row {
            display: flex; justify-content: space-between;
            font-size: 9px; font-style: italic; padding: 2px 0;
        }

        /* ═══ PIE ═══ */
        .pie { margin-top: auto; padding-top: 14px; border-top: 1px solid #000; }
        .pie-body { display: flex; gap: 16px; align-items: flex-start; }
        .pie-qr img { width: 90px; height: 90px; display: block; }
        .pie-qr .qr-placeholder {
            width: 90px; height: 90px; border: 1px solid #ccc;
            display: flex; align-items: center; justify-content: center;
            font-size: 8px; color: #999; text-align: center;
        }
        .pie-cae { flex: 1; font-size: 10px; line-height: 1.7; }
        .pie-cae .cae-titulo { font-size: 12px; font-weight: bold; margin-bottom: 4px; }
        .pie-right { text-align: right; font-size: 9px; color: #555; line-height: 1.6; }
        .pie-right .pag { font-size: 10px; font-weight: bold; color: #000; }

        /* Comentarios */
        .obs-block { margin-top: 10px; font-size: 10px; color: #333; border-top: 1px solid #ddd; padding-top: 6px; }
        .obs-block .obs-label { font-weight: bold; margin-right: 4px; }

        /* ═══ PRINT ═══ */
        @media print {
            body { background: #fff; }
            .toolbar { display: none; }
            .page { width: auto; min-height: 0; margin: 0; padding: 12mm 15mm 12mm; }
        }
    </style>
</head>
<body>

<div class="toolbar">
    <a href="{{ route('facturas.show', $factura->id) }}">← Volver a la factura</a>
    <div class="tb-btns">
        <button class="tb-btn ghost" onclick="window.print()">Imprimir</button>
        <button class="tb-btn" onclick="descargarPDF()">⬇ Descargar PDF</button>
    </div>
</div>

@php
    $empresa = \App\Models\Configuracion::empresa();

    $tipo            = (int) $factura->tipo;
    $esNC            = in_array($tipo, [3, 8, 13]);
    $ivaDiscriminado = in_array($tipo, [1, 3]);

    $letra    = match($tipo) { 1, 3 => 'A', 6, 8 => 'B', 11, 13 => 'C', default => '?' };
    $codTipo  = str_pad($tipo, 2, '0', STR_PAD_LEFT);
    $tipoLabel = $esNC ? 'NOTA DE CRÉDITO' : 'FACTURA';

    $fmtCuit = function(string $c): string {
        $c = preg_replace('/\D/', '', $c);
        return strlen($c) === 11
            ? substr($c,0,2) . '-' . substr($c,2,8) . '-' . substr($c,10)
            : ($c ?: '—');
    };

    $condIvaShort = match($factura->cliente->condicion_iva ?? '') {
        'responsable_inscripto' => 'RESP. INSCRIPTO',
        'monotributo'           => 'MONOTRIBUTISTA',
        'exento'                => 'EXENTO',
        'consumidor_final'      => 'CONSUMIDOR FINAL',
        default                 => strtoupper($factura->cliente->condicion_iva ?? '—'),
    };

    $conceptoLabel = match($factura->concepto) {
        1 => 'Productos', 2 => 'Servicios', 3 => 'Productos y Servicios', default => '—'
    };

    // ── Desglose IVA (solo Factura A) ──────────────────────────────
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
            if (!isset($desgloseIva[$key])) $desgloseIva[$key] = ['ali' => $ali, 'base' => 0, 'iva' => 0];
            $desgloseIva[$key]['base'] = round($desgloseIva[$key]['base'] + $base, 2);
            $desgloseIva[$key]['iva']  = round($desgloseIva[$key]['iva']  + $iva,  2);
        }
        ksort($desgloseIva);
        $sumaBase = round($sumaBase, 2);
    }

    // ── IVA contenido (Ley 27.743, para B y C) ─────────────────────
    $ivaContenido = round((float) $factura->imp_iva, 2);

    // ── Monto en letras ────────────────────────────────────────────
    $uni = ['','Un','Dos','Tres','Cuatro','Cinco','Seis','Siete','Ocho','Nueve',
            'Diez','Once','Doce','Trece','Catorce','Quince'];
    $letras = function(int $n) use (&$letras, $uni): string {
        if ($n <= 15) return $uni[$n];
        if ($n <= 19) return 'Dieci' . mb_strtolower($letras($n - 10));
        if ($n === 20) return 'Veinte';
        if ($n <= 29)  return 'Veinti' . mb_strtolower($letras($n - 20));
        $dec = ['','','Treinta','Cuarenta','Cincuenta','Sesenta','Setenta','Ochenta','Noventa'];
        if ($n < 100) return $dec[(int)($n/10)] . ($n % 10 ? ' y ' . $letras($n % 10) : '');
        if ($n === 100) return 'Cien';
        $cen = ['','Ciento','Doscientos','Trescientos','Cuatrocientos','Quinientos',
                'Seiscientos','Setecientos','Ochocientos','Novecientos'];
        if ($n < 1000)    return $cen[(int)($n/100)] . ($n%100 ? ' ' . $letras($n%100) : '');
        if ($n < 2000)    return 'Mil' . ($n%1000 ? ' ' . $letras($n%1000) : '');
        if ($n < 1000000) return $letras((int)($n/1000)) . ' Mil' . ($n%1000 ? ' ' . $letras($n%1000) : '');
        if ($n < 2000000) return 'Un Millón' . ($n%1000000 ? ' ' . $letras($n%1000000) : '');
        return $letras((int)($n/1000000)) . ' Millones' . ($n%1000000 ? ' ' . $letras($n%1000000) : '');
    };
    $totalNum  = (float) $factura->imp_total;
    $totalEnt  = (int) $totalNum;
    $totalCts  = (int) round(($totalNum - $totalEnt) * 100);
    $montoLetras = ($totalEnt > 0 ? $letras($totalEnt) : 'Cero') . ' Pesos'
                 . ($totalCts > 0 ? ' con ' . sprintf('%02d', $totalCts) . '/100' : '');

    // ── Nombre de archivo para descarga ────────────────────────────
    $san = fn(string $s) => strtoupper(preg_replace('/[^A-Z0-9]/i', '', \Illuminate\Support\Str::ascii($s)));
    $fileNombre = implode('_', array_filter([
        str_pad($factura->numero,       8, '0', STR_PAD_LEFT),
        str_pad($factura->punto_venta,  5, '0', STR_PAD_LEFT),
        substr($san($factura->cliente->nombre ?? ''), 0, 20),
        substr($san($factura->observaciones    ?? ''), 0, 8),
    ]));

    // ── Logo ───────────────────────────────────────────────────────
    $logoUrl = $empresa['logo']
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($empresa['logo'])
        : null;
@endphp

<div class="page">

    {{-- ════════════════ ENCABEZADO ════════════════ --}}
    <div class="hd-wrapper">

        <div class="hd-top">
            <span>ORIGINAL</span>
            <span class="doc-type-name">{{ $tipoLabel }}</span>
            <span>ORIGINAL</span>
        </div>

        <div class="hd-body">

            {{-- Empresa (izquierda) --}}
            <div class="hd-empresa">
                @if($logoUrl)
                    <div class="logo-wrap">
                        <img src="{{ $logoUrl }}" alt="{{ $empresa['nombre'] }}">
                    </div>
                @endif
                <div class="emp-nombre">{{ $empresa['propietario'] ?: $empresa['nombre'] }}</div>
                @if($empresa['direccion'])
                    <div class="emp-row">{{ $empresa['direccion'] }}</div>
                @endif
                @if($empresa['telefono'])
                    <div class="emp-row">Cel: {{ $empresa['telefono'] }}</div>
                @endif
                @if($empresa['email'])
                    <div class="emp-row">Email: {{ $empresa['email'] }}</div>
                @endif
            </div>

            {{-- Letra (centro) --}}
            <div class="hd-letra">
                <div class="letra-box">{{ $letra }}</div>
                <div class="letra-cod">COD. {{ $codTipo }}</div>
            </div>

            {{-- Datos comprobante (derecha) --}}
            <div class="hd-doc">
                <div class="hd-doc-title">{{ $tipoLabel }} {{ $letra }}</div>
                <div class="hd-doc-row">
                    <span class="lbl">Punto de Venta:</span>
                    {{ str_pad($factura->punto_venta, 5, '0', STR_PAD_LEFT) }}
                    &nbsp;&nbsp;
                    <span class="lbl">Comp. Nro:</span>
                    {{ str_pad($factura->numero, 8, '0', STR_PAD_LEFT) }}
                </div>
                <div class="hd-doc-row">
                    <span class="lbl">Emisión:</span> {{ $factura->fecha->format('d/m/Y') }}
                </div>

                <div class="hd-doc-sep"></div>

                @if($empresa['condicion_iva'])
                    <div class="hd-doc-iva">{{ strtoupper($empresa['condicion_iva']) }}</div>
                @endif
                @if($empresa['cuit'])
                    <div class="hd-doc-row">
                        <span class="lbl">CUIT:</span> {{ $fmtCuit($empresa['cuit']) }}
                        @if($empresa['iibb'])
                            &nbsp;&nbsp;<span class="lbl">IB:</span> {{ $empresa['iibb'] }}
                        @endif
                    </div>
                @endif
                @if($empresa['inicio_actividades'])
                    <div class="hd-doc-row">
                        <span class="lbl">Inicio de Actividades:</span> {{ $empresa['inicio_actividades'] }}
                    </div>
                @endif
            </div>

        </div>
    </div>{{-- /hd-wrapper --}}

    {{-- Vto. pago --}}
    <div class="vto-pago">
        Fecha de Vto. para el pago: {{ $factura->fecha->format('d/m/Y') }}
    </div>

    {{-- ════════════════ RECEPTOR ════════════════ --}}
    <div class="receptor">
        <div class="receptor-row">
            <div class="receptor-cell">
                <span class="lbl">Cliente:</span> {{ $factura->cliente->nombre }}
            </div>
        </div>
        <div class="receptor-row">
            <div class="receptor-cell">
                <span class="lbl">Razón Social:</span> {{ $factura->cliente->nombre }}
                @if($factura->cliente->cuit)
                    &nbsp;&nbsp;<span class="lbl">CUIT:</span> {{ $fmtCuit($factura->cliente->cuit) }}
                @endif
                &nbsp;&nbsp;<span class="lbl">IVA:</span> {{ $condIvaShort }}
            </div>
        </div>
        @if($factura->cliente->direccion)
        <div class="receptor-row">
            <div class="receptor-cell">
                <span class="lbl">Domicilio Comercial:</span> {{ $factura->cliente->direccion }}
            </div>
        </div>
        @endif
        <div class="receptor-row">
            <div class="receptor-cell" style="flex:0 0 200px">
                <span class="lbl">Condición de venta:</span> Contado
            </div>
            <div class="receptor-cell">
                <span class="lbl">Concepto:</span> {{ $conceptoLabel }}
            </div>
        </div>
    </div>{{-- /receptor --}}

    {{-- NC: referencia comprobante original --}}
    @if($esNC && $factura->nc_nro)
        <div class="nc-ref">
            <strong>Acredita comprobante original:</strong>
            {{ \App\Services\ArcaService::TIPOS_CBTE[$factura->nc_tipo] ?? "Tipo {$factura->nc_tipo}" }}
            — Pto. Vta. {{ str_pad($factura->nc_pto_vta, 4, '0', STR_PAD_LEFT) }}
            Comp. N° {{ str_pad($factura->nc_nro, 8, '0', STR_PAD_LEFT) }}
        </div>
    @endif

    {{-- ════════════════ TABLA ÍTEMS ════════════════ --}}
    @if($ivaDiscriminado)
    {{-- Factura A: precio neto + columna IVA --}}
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
                $ali     = (float) $item->alicuota_iva;
                $factor  = $ali > 0 ? (1 + $ali / 100) : 1;
                $pNeto   = round((float) $item->precio_unitario / $factor, 2);
                $subNeto = round((float) $item->subtotal / $factor, 2);
                $subCIva = (float) $item->subtotal;
                $aliLabel = $ali > 0 ? number_format($ali, 0) . ' %' : 'Exento';
            @endphp
            <tr>
                <td class="c td-num">{{ $i + 1 }}</td>
                <td>{{ $item->descripcion }}</td>
                <td class="r">{{ rtrim(rtrim(number_format((float)$item->cantidad, 3, ',', '.'), '0'), ',') }}</td>
                <td class="c td-num">Un.</td>
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
    {{-- Factura B / C: precio final --}}
    <table class="items">
        <thead>
            <tr>
                <th style="width:28px">Cód.</th>
                <th class="l">Producto / Servicio</th>
                <th style="width:70px">Cantidad</th>
                <th style="width:52px">U.<br>Medida</th>
                <th style="width:100px">Precio<br>Unit.</th>
                <th style="width:52px">%<br>Bonif.</th>
                <th style="width:100px">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($factura->items as $i => $item)
            <tr>
                <td class="c td-num">{{ $i + 1 }}</td>
                <td>{{ $item->descripcion }}</td>
                <td class="r">{{ rtrim(rtrim(number_format((float)$item->cantidad, 3, ',', '.'), '0'), ',') }}</td>
                <td class="c td-num">Un.</td>
                <td class="r">{{ number_format((float)$item->precio_unitario, 2, ',', '.') }}</td>
                <td class="c">0,00</td>
                <td class="r">{{ number_format((float)$item->subtotal, 2, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- ════════════════ TOTALES ════════════════ --}}
    <div class="totales-wrap">
        <div class="totales">
            @if($ivaDiscriminado)
                <div class="tot-row">
                    <div class="tot-label">Importe Neto Gravado</div>
                    <div class="tot-value">$ {{ number_format($sumaBase, 2, ',', '.') }}</div>
                </div>
                @foreach($desgloseIva as $d)
                    @if($d['ali'] > 0)
                    <div class="tot-row">
                        <div class="tot-label">
                            IVA {{ number_format($d['ali'], 0) }}%
                            <span style="font-size:9px;color:#666">(sobre $ {{ number_format($d['base'], 2, ',', '.') }})</span>
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

            <div class="tot-row total-final">
                <div class="tot-label">Total (SEUO): ARS</div>
                <div class="tot-value">{{ number_format((float)$factura->imp_total, 2, ',', '.') }}</div>
            </div>
        </div>
    </div>

    <div class="monto-letras">Son {{ $montoLetras }}</div>

    {{-- Régimen de Transparencia Fiscal al Consumidor (Ley 27.743) — solo B y C --}}
    @if(!$ivaDiscriminado)
    <div class="transparencia">
        <div class="trans-titulo">Régimen de Transparencia Fiscal al Consumidor (Ley 27.743)</div>
        <div class="trans-row">
            <span>IVA Contenido: $</span>
            <span>{{ number_format($ivaContenido, 2, ',', '.') }}</span>
        </div>
        <div class="trans-row">
            <span>Otros Impuestos Nacionales Indirectos: $</span>
            <span>0,00</span>
        </div>
    </div>
    @endif

    {{-- Comentarios --}}
    @if($factura->observaciones)
        <div class="obs-block">
            <span class="obs-label">Comentarios:</span>{{ $factura->observaciones }}
        </div>
    @endif

    {{-- ════════════════ PIE ════════════════ --}}
    <div class="pie">
        <div class="pie-body">

            <div class="pie-qr">
                @if($factura->tieneCAE())
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ urlencode($factura->qrUrl()) }}"
                         alt="QR ARCA">
                @else
                    <div class="qr-placeholder">Sin<br>CAE</div>
                @endif
            </div>

            <div class="pie-cae">
                @if($factura->tieneCAE())
                    <div class="cae-titulo">Comprobante Autorizado</div>
                    <div><strong>CAE N°:</strong> {{ $factura->cae }}</div>
                    <div><strong>Fecha de Vto. de CAE:</strong> {{ $factura->cae_vencimiento?->format('d/m/Y') }}</div>
                @else
                    <div style="color:#c00;font-weight:bold">Sin CAE — No válido fiscalmente</div>
                @endif
            </div>

            <div class="pie-right">
                <div class="pag">Pág. 1/1</div>
                <div style="margin-top:6px">{{ $empresa['nombre'] }}</div>
                @if($empresa['cuit'])
                    <div>CUIT: {{ $fmtCuit($empresa['cuit']) }}</div>
                @endif
            </div>

        </div>
    </div>{{-- /pie --}}

</div>{{-- /page --}}

<script>
function descargarPDF() {
    var orig = document.title;
    document.title = '{{ addslashes($fileNombre) }}';
    window.print();
    setTimeout(function() { document.title = orig; }, 1000);
}
</script>
</body>
</html>

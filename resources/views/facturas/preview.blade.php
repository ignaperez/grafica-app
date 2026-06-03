<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vista previa — {{ \App\Services\ArcaService::TIPOS_CBTE[$tipo] ?? "Comprobante $tipo" }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root { --accent: #e6502a; }

        body {
            background: #f0f0f0;
            font-family: 'Manrope', system-ui, sans-serif;
            font-size: 11px;
            color: #15171a;
            letter-spacing: -0.005em;
        }

        /* ── Barra de pantalla ── */
        .screen-bar {
            background: #1a1a1a; color: #ccc;
            padding: 10px 32px;
            display: flex; justify-content: space-between; align-items: center;
            font-size: 13px; gap: 16px;
        }
        .screen-bar a, .screen-bar button {
            background: none; border: none; color: #aaa;
            cursor: pointer; font-size: 13px; font-family: inherit;
            text-decoration: none; padding: 0;
        }
        .screen-bar a:hover, .screen-bar button:hover { color: #fff; }
        .screen-bar .notice {
            flex: 1; text-align: center;
            background: #2a1a00; color: #f0a030;
            padding: 6px 16px; border-radius: 6px; font-size: 12px; font-weight: 600;
        }

        /* ── Hoja A4 ── */
        .v1 {
            width: 794px; min-height: 1123px; background: #fff;
            margin: 24px auto; display: flex; flex-direction: column;
            position: relative; overflow: hidden;
        }

        /* ── Marca de agua BORRADOR ── */
        .watermark {
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%) rotate(-35deg);
            font-size: 100px; font-weight: 800;
            color: rgba(230,80,42,0.07);
            letter-spacing: 0.05em;
            pointer-events: none;
            white-space: nowrap;
            z-index: 0;
            font-family: 'Manrope', sans-serif;
        }

        /* ── Zonas ── */
        .page-hd { padding: 56px 56px 0; position: relative; z-index: 1; }
        .page-ct { padding: 0 56px; flex: 1; position: relative; z-index: 1; }
        .page-ft { padding: 28px 56px 48px; position: relative; z-index: 1; }

        /* ── Tipografía ── */
        .label {
            font-size: 8.5px; letter-spacing: 0.16em; text-transform: uppercase;
            color: #7a8189; font-weight: 600;
        }
        .mono { font-family: 'JetBrains Mono', ui-monospace, monospace; }

        /* ── Cabecera ── */
        .head { display: flex; justify-content: space-between; align-items: flex-start; gap: 32px; }
        .brand { display: flex; align-items: flex-start; gap: 16px; }
        .brand img { width: 64px; height: 64px; object-fit: contain; display: block; }
        .brand-text { padding-top: 4px; }
        .brand h1 { font-family: 'Manrope'; font-weight: 800; font-size: 18px; margin: 0; letter-spacing: -0.02em; }
        .brand .meta { color: #5b636d; font-size: 10px; line-height: 1.6; margin-top: 4px; }

        .doc { text-align: right; }
        .doc .num {
            font-family: 'JetBrains Mono', ui-monospace, monospace;
            font-size: 26px; font-weight: 600; letter-spacing: -0.02em;
            color: #15171a; margin-top: 2px;
            display: inline-flex; align-items: center; gap: 8px;
        }
        .doc .num::before {
            content: ''; display: inline-block;
            width: 8px; height: 8px;
            background: var(--accent); border-radius: 50%;
        }
        .doc .draft-badge {
            display: inline-block; margin-top: 6px;
            background: #fff3e0; color: #e65100;
            border: 1px solid #ffcc80; border-radius: 4px;
            padding: 2px 10px; font-size: 9px; font-weight: 700;
            letter-spacing: 0.12em; text-transform: uppercase;
        }
        .doc dl {
            margin: 10px 0 0;
            display: grid; grid-template-columns: auto auto;
            gap: 3px 14px; font-size: 10px; justify-content: end;
        }
        .doc dt { color: #7a8189; text-transform: uppercase; letter-spacing: 0.1em; font-size: 8.5px; font-weight: 600; align-self: center; }
        .doc dd { margin: 0; font-family: 'JetBrains Mono', monospace; color: #15171a; }

        /* ── Receptor ── */
        .who { margin-top: 32px; }
        .who .block .name { font-size: 16px; font-weight: 700; margin: 8px 0 4px; letter-spacing: -0.01em; }
        .who .block .row { font-size: 10.5px; color: #41464d; line-height: 1.55; }
        .who-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0 32px; }

        /* ── Tabla ── */
        table { width: 100%; border-collapse: collapse; margin-top: 28px; }
        thead th {
            text-align: left; font-size: 8.5px; text-transform: uppercase;
            letter-spacing: 0.14em; font-weight: 700; color: #15171a;
            padding: 10px 8px; border-bottom: 1.5px solid #15171a;
        }
        thead th.r { text-align: right; }
        thead th.idx { width: 26px; color: #9aa0a8; }
        thead th.cant { width: 70px; text-align: center; }
        thead th.p, thead th.s { width: 90px; }

        tbody td { padding: 11px 8px; vertical-align: top; font-size: 11px; border-bottom: 1px solid #f0f1f3; }
        tbody td.idx { color: #9aa0a8; font-family: 'JetBrains Mono', monospace; font-size: 10px; }
        tbody td.cant { text-align: center; font-family: 'JetBrains Mono', monospace; }
        tbody td.num { text-align: right; font-family: 'JetBrains Mono', monospace; font-variant-numeric: tabular-nums; }
        tbody td.s { font-weight: 600; }

        /* ── Totales ── */
        .totales { margin-top: 12px; display: flex; flex-direction: column; align-items: flex-end; gap: 5px; }
        .tot-line { display: flex; gap: 32px; font-size: 10.5px; color: #5b636d; }
        .tot-line .v { font-family: 'JetBrains Mono', monospace; min-width: 100px; text-align: right; }

        .total-row { display: flex; justify-content: flex-end; margin-top: 8px; }
        .total-row .box {
            display: flex; align-items: center; gap: 24px;
            padding: 14px 18px;
            background: #15171a; color: #fff;
            border-radius: 4px; position: relative;
        }
        .total-row .box::before {
            content: ''; position: absolute;
            left: -4px; top: 0; bottom: 0; width: 4px;
            background: var(--accent); border-radius: 4px 0 0 4px;
        }
        .total-row .box .lbl { font-size: 9px; letter-spacing: 0.16em; text-transform: uppercase; opacity: .7; }
        .total-row .box .val { font-family: 'JetBrains Mono', monospace; font-size: 22px; font-weight: 600; letter-spacing: -0.01em; }

        /* ── Pie ── */
        .foot-grid { display: grid; grid-template-columns: 1fr; gap: 24px; align-items: start; }
        .cond-pane {
            border: 1px solid #e8eaed; border-radius: 6px;
            padding: 14px 16px;
        }
        .cond-pane h3 {
            margin: 0 0 8px;
            font-size: 8.5px; letter-spacing: 0.16em; text-transform: uppercase;
            color: #7a8189; font-weight: 700;
        }
        .cond-pane p { margin: 0; font-size: 10.5px; line-height: 1.6; color: #2c3036; }
        .cond-pane .cond-dates {
            margin-top: 10px; padding-top: 8px;
            border-top: 1px solid #e8eaed;
            font-size: 9.5px; color: #7a8189;
        }

        /* Caja CAE pendiente */
        .cae-pending {
            padding: 12px 14px;
            border: 1px dashed #e0c080; border-radius: 6px;
            background: #fffbf0; text-align: center;
            font-size: 9.5px; color: #a07830; margin-top: 16px;
        }

        .doc-footer {
            margin-top: 14px; padding-top: 14px;
            border-top: 1px solid #e8eaed;
            display: flex; justify-content: space-between; align-items: flex-end;
            font-size: 9.5px; color: #7a8189;
        }
        .doc-footer .left { line-height: 1.5; }

        @media print {
            body { background: #fff; }
            .screen-bar { display: none; }
            .v1 { width: auto; min-height: 0; margin: 0; }
        }
    </style>
</head>
<body>

@php
    $empData = \App\Models\Configuracion::empresa();
    $empresa = $empData['nombre'];
    $cuitEmp = $empData['cuit'];
    $dir     = $empData['direccion'];
    $tel     = $empData['telefono'];
    $owner   = $empData['propietario'];
    $email   = $empData['email'];
    $tipoLabel = \App\Services\ArcaService::TIPOS_CBTE[$tipo] ?? "Comprobante $tipo";
    $hoy    = \Carbon\Carbon::today()->format('d/m/Y');
    $ptoVta = (int) (\App\Models\Configuracion::get('arca_punto_venta') ?: config('arca.punto_venta', 1));
@endphp

<div class="screen-bar">
    <button onclick="window.close()">← Cerrar previa</button>
    <div class="notice">⚠ VISTA PREVIA — Este documento es un borrador. El número y CAE se asignan al emitir.</div>
    <button onclick="window.print()">🖨 Imprimir borrador</button>
</div>

<div class="v1">
    <div class="watermark">BORRADOR</div>

    {{-- ══ ENCABEZADO ══ --}}
    <div class="page-hd">
        <header class="head">
            <div class="brand">
                <img src="{{ asset('images/logo.png') }}" alt="{{ $empresa }}">
                <div class="brand-text">
                    <h1>{{ $empresa }}</h1>
                    <div class="meta">
                        @if($owner){{ $owner }}<br>@endif
                        @if($cuitEmp)CUIT {{ $cuitEmp }}<br>@endif
                        @if($dir){{ $dir }}<br>@endif
                        @if($tel)Tel. {{ $tel }}@endif
                    </div>
                </div>
            </div>

            <div class="doc">
                <div class="label">{{ $tipoLabel }}</div>
                <div class="num">
                    {{ str_pad($ptoVta, 4, '0', STR_PAD_LEFT) }}-????????
                </div>
                <div class="draft-badge">Borrador — sin CAE</div>
                <dl>
                    <dt>Fecha</dt>
                    <dd>{{ $hoy }}</dd>
                </dl>
            </div>
        </header>
    </div>

    {{-- ══ CONTENIDO ══ --}}
    <div class="page-ct">

        {{-- Receptor --}}
        <section class="who">
            <div class="who-grid">
                <div class="block">
                    <div class="label">Facturado a</div>
                    @if($cliente)
                        <div class="name">{{ $cliente->nombre }}</div>
                        <div class="row">
                            @if($cliente->direccion){{ $cliente->direccion }}<br>@endif
                            @if($cliente->email){{ $cliente->email }}<br>@endif
                            @if($cliente->telefono)Tel. {{ $cliente->telefono }}@endif
                        </div>
                    @else
                        <div class="name" style="color:#9aa0a8">Sin cliente seleccionado</div>
                    @endif
                </div>
                <div class="block">
                    <div class="label">Identificación</div>
                    <div class="name" style="font-size:13px">{{ $docTipoLabel }}</div>
                    @if($docNro)
                        <div class="row mono">{{ $docNro }}</div>
                    @endif
                    <div class="row" style="margin-top:6px">Concepto: {{ $conceptoLabel }}</div>
                </div>
            </div>
        </section>

        {{-- Tabla de ítems --}}
        <table>
            <thead>
                <tr>
                    <th class="idx">#</th>
                    <th>Descripción</th>
                    <th class="cant r">Cantidad</th>
                    <th class="p r">P. Unit.</th>
                    <th class="s r">Subtotal</th>
                </tr>
            </thead>
            <tbody>
            @foreach($items as $i => $item)
            @if(!empty($item['descripcion']))
            <tr>
                <td class="idx">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</td>
                <td>{{ $item['descripcion'] }}</td>
                <td class="cant">{{ rtrim(rtrim(number_format((float)$item['cantidad'], 3, ',', '.'), '0'), ',') }}</td>
                <td class="num">${{ number_format((float)$item['precio_unitario'], 2, ',', '.') }}</td>
                <td class="num s">${{ number_format((float)$item['subtotal'], 2, ',', '.') }}</td>
            </tr>
            @endif
            @endforeach
            </tbody>
        </table>

        {{-- Desglose IVA --}}
        @if($impIva > 0)
        <div class="totales">
            <div class="tot-line">
                <span>Neto gravado</span>
                <span class="v">${{ number_format($impNeto, 2, ',', '.') }}</span>
            </div>
            <div class="tot-line">
                <span>IVA 21%</span>
                <span class="v">${{ number_format($impIva, 2, ',', '.') }}</span>
            </div>
        </div>
        @endif

        <div class="total-row">
            <div class="box">
                <span class="lbl">Total</span>
                <span class="val">${{ number_format($total, 2, ',', '.') }}</span>
            </div>
        </div>

    </div>{{-- /page-ct --}}

    {{-- ══ PIE ══ --}}
    <div class="page-ft">
        <div class="foot-grid">
            <div class="cond-pane">
                <h3>Condiciones y notas</h3>
                <p>{{ $observaciones ?: 'Precios expresados en pesos argentinos.' }}</p>
                <div class="cond-dates">Fecha estimada: {{ $hoy }}</div>
            </div>
        </div>

        <div class="cae-pending">
            ⏳ El número de comprobante y el CAE (código de autorización electrónica) se generan al emitir el comprobante en ARCA.
        </div>

        <div class="doc-footer">
            <div class="left">
                <strong>{{ $empresa }}</strong>
                @if($dir) · {{ $dir }}@endif
                <br>
                @if($tel)Tel. {{ $tel }}@endif
                @if($cuitEmp) · CUIT {{ $cuitEmp }}@endif
                @if($email) · {{ $email }}@endif
            </div>
            <div class="mono" style="color:#c0a060">BORRADOR · {{ $hoy }}</div>
        </div>
    </div>

</div>{{-- /v1 --}}
</body>
</html>

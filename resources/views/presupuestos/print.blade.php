<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $presupuesto->numeroFormateado() }}</title>
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

        /* ── Barra de pantalla (oculta en impresión) ── */
        .screen-bar {
            background: #1a1a1a;
            color: #ccc;
            padding: 10px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
        }
        .screen-bar a { color: #aaa; text-decoration: none; }
        .screen-bar a:hover { color: #fff; }
        .screen-bar button {
            background: var(--accent); color: #fff; border: none;
            padding: 8px 20px; border-radius: 6px; cursor: pointer;
            font-size: 13px; font-family: inherit; font-weight: 600;
        }

        /* ── Hoja A4 (pantalla) ── */
        .v1 {
            width: 794px;
            min-height: 1123px;
            background: #fff;
            margin: 24px auto;
            display: flex;
            flex-direction: column;
        }

        /* ── Zonas ── */
        .page-hd { padding: 56px 56px 0; }
        .page-ct { padding: 0 56px; flex: 1; }   /* crece para empujar el pie al fondo */
        .page-ft { padding: 28px 56px 48px; }

        /* ── Tipografía auxiliar ── */
        .label {
            font-size: 8.5px; letter-spacing: 0.16em; text-transform: uppercase;
            color: #7a8189; font-weight: 600;
        }
        .mono { font-family: 'JetBrains Mono', ui-monospace, monospace; }

        /* ── Cabecera ── */
        .head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 32px;
        }
        .brand { display: flex; align-items: flex-start; gap: 16px; }
        .brand img { width: 64px; height: 64px; object-fit: contain; display: block; }
        .brand-text { padding-top: 4px; }
        .brand h1 {
            font-family: 'Manrope'; font-weight: 800; font-size: 18px;
            margin: 0; letter-spacing: -0.02em;
        }
        .brand .meta { color: #5b636d; font-size: 10px; line-height: 1.6; margin-top: 4px; }

        .doc { text-align: right; }
        .doc .num {
            font-family: 'JetBrains Mono', ui-monospace, monospace;
            font-size: 28px; font-weight: 600; letter-spacing: -0.02em;
            color: #15171a; margin-top: 2px;
            display: inline-flex; align-items: center; gap: 8px;
        }
        .doc .num::before {
            content: ''; display: inline-block;
            width: 8px; height: 8px;
            background: var(--accent); border-radius: 50%;
        }
        .doc dl {
            margin: 14px 0 0;
            display: grid; grid-template-columns: auto auto;
            gap: 3px 14px; font-size: 10px; justify-content: end;
        }
        .doc dt {
            color: #7a8189; text-transform: uppercase;
            letter-spacing: 0.1em; font-size: 8.5px; font-weight: 600;
            align-self: center;
        }
        .doc dd {
            margin: 0;
            font-family: 'JetBrains Mono', monospace; color: #15171a;
        }

        /* ── Bloque quién ── */
        .who { margin-top: 32px; }
        .who .block .name { font-size: 16px; font-weight: 700; margin: 8px 0 4px; letter-spacing: -0.01em; }
        .who .block .row { font-size: 10.5px; color: #41464d; line-height: 1.55; }

        /* ── Tabla ── */
        table { width: 100%; border-collapse: collapse; margin-top: 28px; }
        thead th {
            text-align: left; font-size: 8.5px; text-transform: uppercase;
            letter-spacing: 0.14em; font-weight: 700; color: #15171a;
            padding: 10px 8px; border-bottom: 1.5px solid #15171a;
        }
        thead th.r { text-align: right; }
        thead th.idx { width: 26px; color: #9aa0a8; }
        thead th.u { width: 60px; text-align: center; }
        thead th.m, thead th.p, thead th.s { width: 86px; }

        tbody td {
            padding: 11px 8px; vertical-align: top; font-size: 11px;
            border-bottom: 1px solid #f0f1f3;
        }
        tbody td.idx { color: #9aa0a8; font-family: 'JetBrains Mono', monospace; font-size: 10px; }
        tbody td.u { text-align: center; color: #5b636d; }
        tbody td.num {
            text-align: right;
            font-family: 'JetBrains Mono', monospace;
            font-variant-numeric: tabular-nums;
        }
        tbody td.s { font-weight: 600; }
        tbody .desc-main { font-weight: 600; }
        tbody .desc-sub { color: #7a8189; font-size: 10px; margin-top: 2px; }

        /* ── Total ── */
        .total-row {
            display: flex; justify-content: flex-end;
            margin-top: 16px;
        }
        .total-row .box {
            display: flex; align-items: center; gap: 24px;
            padding: 14px 18px;
            background: #15171a; color: #fff;
            border-radius: 4px; position: relative;
        }
        .total-row .box::before {
            content: ''; position: absolute;
            left: -4px; top: 0; bottom: 0; width: 4px;
            background: var(--accent);
            border-radius: 4px 0 0 4px;
        }
        .total-row .box .lbl {
            font-size: 9px; letter-spacing: 0.16em;
            text-transform: uppercase; opacity: .7;
        }
        .total-row .box .val {
            font-family: 'JetBrains Mono', monospace;
            font-size: 22px; font-weight: 600; letter-spacing: -0.01em;
        }

        /* ── Pie (condiciones + barra) ── */
        .cond-pane {
            border: 1px solid #e8eaed; border-radius: 6px;
            padding: 14px 16px;
            overflow: hidden;
        }
        .cond-pane h3 {
            margin: 0 0 8px;
            font-size: 8.5px; letter-spacing: 0.16em; text-transform: uppercase;
            color: #7a8189; font-weight: 700;
        }
        .cond-pane p {
            margin: 0; font-size: 10.5px; line-height: 1.6; color: #2c3036;
            /* máx 5 líneas: 10.5 × 1.6 × 5 ≈ 84px */
            max-height: 84px;
            overflow: hidden;
        }
        .cond-pane .cond-dates {
            margin-top: 10px; padding-top: 8px;
            border-top: 1px solid #e8eaed;
            font-size: 9.5px; color: #7a8189;
        }

        .doc-footer {
            margin-top: 14px; padding-top: 14px;
            border-top: 1px solid #e8eaed;
            display: flex; justify-content: space-between; align-items: flex-end;
            font-size: 9.5px; color: #7a8189;
        }
        .doc-footer .left { line-height: 1.5; }

        /* ════════════════════════════════
           IMPRESIÓN
           ════════════════════════════════ */
        @media print {
            body { background: #fff; }
            .screen-bar { display: none; }

            /* Quitar el wrapper de pantalla */
            .v1 { width: auto; min-height: 0; margin: 0; display: block; background: transparent; }

            /* ── Encabezado fijo: se repite en cada página ── */
            .page-hd {
                position: fixed;
                top: 0; left: 0; right: 0;
                padding: 28px 56px 14px;
                background: #fff;
                border-bottom: 1px solid #e8eaed;
            }

            /* ── Pie fijo: siempre al fondo, se repite en cada página ── */
            .page-ft {
                position: fixed;
                bottom: 0; left: 0; right: 0;
                padding: 14px 56px 28px;
                background: #fff;
                border-top: 2px solid #e8eaed;
                overflow: hidden;
            }

            /* ── Contenido: márgenes para no quedar debajo de los fijos ── */
            /* Encabezado en pantalla ~80px; con padding 28+14 = ~122px   */
            /* Pie: cond-pane ~120px + doc-footer ~42px + padding 14+28 = ~204px */
            .page-ct {
                padding: 0 56px;
                margin-top: 130px;
                margin-bottom: 210px;
            }

            /* En el encabezado reducimos el número de doc un poco */
            .doc .num { font-size: 22px; }
        }
    </style>
</head>
<body>

{{-- Barra de pantalla --}}
<div class="screen-bar">
    <a href="{{ route('presupuestos.show', $presupuesto->id) }}">← Volver al presupuesto</a>
    <button onclick="window.print()">🖨 Imprimir / Guardar PDF</button>
</div>

@php
    $cfg = \App\Models\Configuracion::all()->pluck('valor', 'clave');
    $empresa = $cfg->get('empresa_nombre', config('app.name'));
    $cuit    = $cfg->get('empresa_cuit', '');
    $dir     = $cfg->get('empresa_direccion', '');
    $tel     = $cfg->get('empresa_telefono', '');
    $owner   = $cfg->get('empresa_propietario', '');
    $email   = $cfg->get('empresa_email', '');
@endphp

<div class="v1">

    {{-- ══════════════════════════════════════════════
         ENCABEZADO — se repite en cada hoja (print)
         ══════════════════════════════════════════════ --}}
    <div class="page-hd">
        <header class="head">
            <div class="brand">
                <img src="{{ asset('images/logo.png') }}" alt="{{ $empresa }}">
                <div class="brand-text">
                    <h1>{{ $empresa }}</h1>
                    <div class="meta">
                        @if($owner){{ $owner }}<br>@endif
                        @if($cuit)CUIT {{ $cuit }}<br>@endif
                        @if($dir){{ $dir }}<br>@endif
                        @if($tel)Tel. {{ $tel }}@endif
                    </div>
                </div>
            </div>

            <div class="doc">
                <div class="label">Presupuesto</div>
                <div class="num">{{ $presupuesto->numeroFormateado() }}</div>
                <dl>
                    <dt>Emitido</dt>
                    <dd>{{ $presupuesto->fecha->format('d/m/Y') }}</dd>
                    @if($presupuesto->fecha_vencimiento)
                    <dt>Válido hasta</dt>
                    <dd>{{ $presupuesto->fecha_vencimiento->format('d/m/Y') }}</dd>
                    @endif
                </dl>
            </div>
        </header>
    </div>

    {{-- ══════════════════════════════════════════════
         CONTENIDO — fluye entre encabezado y pie
         ══════════════════════════════════════════════ --}}
    <div class="page-ct">

        {{-- Quién --}}
        <section class="who">
            <div class="block">
                <div class="label">Cliente</div>
                <div class="name">{{ $presupuesto->cliente->nombre }}</div>
                <div class="row">
                    @if($presupuesto->cliente->direccion){{ $presupuesto->cliente->direccion }}<br>@endif
                    @if($presupuesto->cliente->email){{ $presupuesto->cliente->email }}<br>@endif
                    @if($presupuesto->cliente->telefono)Tel. {{ $presupuesto->cliente->telefono }}@endif
                </div>
            </div>
        </section>

        {{-- Tabla de ítems --}}
        <table>
            <thead>
                <tr>
                    <th class="idx">#</th>
                    <th>Descripción</th>
                    <th class="u">Unidad</th>
                    <th class="m r">Medida</th>
                    <th class="p r">P. Unit.</th>
                    <th class="s r">Subtotal</th>
                </tr>
            </thead>
            <tbody>
            @foreach($presupuesto->items as $i => $item)
            <tr>
                <td class="idx">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</td>
                <td>
                    <div class="desc-main">{{ $item->descripcion }}</div>
                    <div class="desc-sub">
                        @if($item->unidad === 'm2')
                            {{ number_format($item->ancho, 2) }} × {{ number_format($item->alto, 2) }} m × {{ $item->cantidad }} u
                        @elseif($item->unidad === 'ml')
                            {{ number_format($item->largo, 2) }} ml × {{ $item->cantidad }} u
                        @else
                            {{ $item->cantidad }} unidad(es)
                        @endif
                    </div>
                </td>
                <td class="u">{{ $item->unidadLabel() }}</td>
                <td class="num">{{ number_format($item->medidaTotal(), 3, ',', '.') }}</td>
                <td class="num">${{ number_format($item->precio_unitario, 2, ',', '.') }}</td>
                <td class="num s">${{ number_format($item->subtotal, 2, ',', '.') }}</td>
            </tr>
            @endforeach
            </tbody>
        </table>

        {{-- Total --}}
        <div class="total-row">
            <div class="box">
                <span class="lbl">Total</span>
                <span class="val">${{ number_format($presupuesto->total, 2, ',', '.') }}</span>
            </div>
        </div>

    </div>{{-- /page-ct --}}

    {{-- ══════════════════════════════════════════════
         PIE — siempre al fondo, se repite en cada hoja (print)
         ══════════════════════════════════════════════ --}}
    <div class="page-ft">

        <div class="cond-pane">
            <h3>Condiciones y notas</h3>
            <p>{{ $presupuesto->observaciones ?: 'Precios expresados en pesos argentinos. Se requiere seña del 50% para iniciar producción; saldo contra entrega.' }}</p>
            <div class="cond-dates">
                Emitido el {{ $presupuesto->fecha->format('d/m/Y') }}
                @if($presupuesto->fecha_vencimiento)
                    &nbsp;·&nbsp; Válido hasta {{ $presupuesto->fecha_vencimiento->format('d/m/Y') }}
                @endif
            </div>
        </div>

        <div class="doc-footer">
            <div class="left">
                <strong>{{ $empresa }}</strong>
                @if($dir) · {{ $dir }}@endif
                <br>
                @if($tel)Tel. {{ $tel }}@endif
                @if($cuit) · CUIT {{ $cuit }}@endif
            </div>
            <div class="mono">{{ $presupuesto->numeroFormateado() }} · {{ $presupuesto->fecha->format('d/m/Y') }}</div>
        </div>

    </div>{{-- /page-ft --}}

</div>{{-- /v1 --}}
</body>
</html>

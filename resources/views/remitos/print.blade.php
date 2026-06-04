<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Remito {{ $remito->numeroFormateado() }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
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
            font-size: 13px;
        }
        .screen-bar a { color: #aaa; text-decoration: none; }
        .screen-bar a:hover { color: #fff; }
        .screen-bar button {
            background: var(--accent); color: #fff; border: none;
            padding: 8px 20px; border-radius: 6px; cursor: pointer;
            font-size: 13px; font-family: inherit; font-weight: 600;
        }

        /* ── Hoja A4 ── */
        .v1 {
            width: 794px; min-height: 1123px; background: #fff;
            margin: 24px auto; display: flex; flex-direction: column;
        }

        /* ── Zonas ── */
        .page-hd { padding: 56px 56px 0; }
        .page-ct { padding: 0 56px; flex: 1; }
        .page-ft { padding: 28px 56px 48px; }

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
        .doc .tipo { font-size: 11px; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; color: #7a8189; }
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
        .doc dt { color: #7a8189; text-transform: uppercase; letter-spacing: 0.1em; font-size: 8.5px; font-weight: 600; align-self: center; }
        .doc dd { margin: 0; font-family: 'JetBrains Mono', monospace; color: #15171a; }

        /* ── Receptor ── */
        .who { margin-top: 32px; }
        .who .block .name { font-size: 16px; font-weight: 700; margin: 8px 0 4px; letter-spacing: -0.01em; }
        .who .block .row { font-size: 10.5px; color: #41464d; line-height: 1.55; }

        /* ── Chips de vínculo ── */
        .vinculos { margin-top: 14px; display: flex; gap: 8px; }
        .chip {
            padding: 3px 10px;
            background: #f5f5f5; border: 1px solid #e0e0e0;
            border-radius: 20px; font-size: 9.5px; color: #5b636d;
        }
        .chip strong { font-family: 'JetBrains Mono', monospace; color: #15171a; }

        /* ── Tabla ── */
        table { width: 100%; border-collapse: collapse; margin-top: 28px; }
        thead th {
            text-align: left; font-size: 8.5px; text-transform: uppercase;
            letter-spacing: 0.14em; font-weight: 700; color: #15171a;
            padding: 10px 8px; border-bottom: 1.5px solid #15171a;
        }
        thead th.idx { width: 26px; color: #9aa0a8; }
        thead th.cant, thead th.u { width: 80px; text-align: center; }

        tbody td { padding: 11px 8px; vertical-align: top; font-size: 11px; border-bottom: 1px solid #f0f1f3; }
        tbody td.idx { color: #9aa0a8; font-family: 'JetBrains Mono', monospace; font-size: 10px; }
        tbody td.cant, tbody td.u { text-align: center; font-family: 'JetBrains Mono', monospace; }
        tbody td.u { color: #5b636d; }

        /* ── Firma recibe ── */
        .firma-recibe { width: 260px; }
        .firma-line { border-top: 1px solid #9aa0a8; margin-bottom: 8px; }
        .firma-lbl { font-size: 9px; letter-spacing: 0.1em; text-transform: uppercase; color: #5b636d; font-weight: 600; }
        .firma-sub { font-size: 8.5px; color: #9aa0a8; margin-top: 2px; }

        /* ── Pie ── */
        .cond-pane {
            border: 1px solid #e8eaed; border-radius: 6px;
            padding: 14px 16px; overflow: hidden;
        }
        .cond-pane h3 {
            margin: 0 0 8px;
            font-size: 8.5px; letter-spacing: 0.16em; text-transform: uppercase;
            color: #7a8189; font-weight: 700;
        }
        .cond-pane p { margin: 0; font-size: 10.5px; line-height: 1.6; color: #2c3036; max-height: 84px; overflow: hidden; }
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

        /* ── Bloque CAI ── */
        .cai-block {
            border: 1.5px solid #15171a; border-radius: 6px;
            padding: 12px 16px;
            display: flex; align-items: center; justify-content: space-between; gap: 20px;
            margin-top: 16px;
        }
        .cai-data { line-height: 1.7; }
        .cai-data .cai-label { font-size: 8px; letter-spacing: .18em; text-transform: uppercase; color: #7a8189; font-weight: 700; }
        .cai-data .cai-code { font-family: 'JetBrains Mono', monospace; font-size: 13px; font-weight: 600; letter-spacing: .06em; color: #15171a; }
        .cai-data .cai-vto  { font-size: 9.5px; color: #41464d; margin-top: 2px; }
        .cai-data .cai-nro  { font-size: 9.5px; color: #5b636d; }
        .cai-bc { text-align: center; }
        .cai-bc svg { display: block; }

        /* ════════════════
           IMPRESIÓN
           ════════════════ */
        @media print {
            body { background: #fff; }
            .screen-bar { display: none; }
            .v1 { width: auto; min-height: 0; margin: 0; display: block; background: transparent; }

            .page-hd {
                position: fixed; top: 0; left: 0; right: 0;
                padding: 28px 56px 14px; background: #fff;
                border-bottom: 1px solid #e8eaed;
            }
            .page-ft {
                position: fixed; bottom: 0; left: 0; right: 0;
                padding: 14px 56px 28px; background: #fff;
                border-top: 2px solid #e8eaed; overflow: hidden;
            }
            .page-ct {
                padding: 0 56px;
                margin-top: 130px;
                margin-bottom: 260px;
            }
            .doc .num { font-size: 22px; }
        }
    </style>
</head>
<body>

<div class="screen-bar">
    <a href="{{ route('remitos.show', $remito->id) }}">← Volver al remito</a>
    <button onclick="window.print()">🖨 Imprimir / Guardar PDF</button>
</div>

@php
    $cfg    = \App\Models\Configuracion::all()->pluck('valor', 'clave');
    $empresa = $cfg->get('empresa_nombre', config('app.name'));
    $cuit    = $cfg->get('empresa_cuit',   '');
    $dir     = $cfg->get('empresa_direccion', '');
    $tel     = $cfg->get('empresa_telefono', '');
    $owner   = $cfg->get('empresa_propietario', '');
    $email   = $cfg->get('empresa_email', '');
    $cai     = $remito->remitoCai;
@endphp

<div class="v1">

    {{-- ══ ENCABEZADO ══ --}}
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
                <div class="label">Remito {{ $remito->tieneCai() ? 'R' : '' }}</div>
                <div class="num">{{ $remito->numeroFormateado() }}</div>
                <dl>
                    <dt>Fecha</dt>
                    <dd>{{ $remito->fecha->format('d/m/Y') }}</dd>
                </dl>
            </div>
        </header>
    </div>

    {{-- ══ CONTENIDO ══ --}}
    <div class="page-ct">

        {{-- Destinatario --}}
        <section class="who">
            <div class="block">
                <div class="label">Destinatario</div>
                <div class="name">{{ $remito->cliente?->nombre ?? '—' }}</div>
                <div class="row">
                    @if($remito->cliente?->direccion){{ $remito->cliente->direccion }}<br>@endif
                    @if($remito->cliente?->email){{ $remito->cliente->email }}<br>@endif
                    @if($remito->cliente?->telefono)Tel. {{ $remito->cliente->telefono }}@endif
                </div>
            </div>

            {{-- Vínculos --}}
            @if($remito->presupuesto || $remito->factura)
            <div class="vinculos">
                @if($remito->presupuesto)
                    <span class="chip">Presupuesto <strong>{{ $remito->presupuesto->numeroFormateado() }}</strong></span>
                @endif
                @if($remito->factura)
                    <span class="chip">Factura <strong>{{ $remito->factura->numeroFormateado() }}</strong></span>
                @endif
            </div>
            @endif
        </section>

        {{-- Tabla de ítems --}}
        <table>
            <thead>
                <tr>
                    <th class="idx">#</th>
                    <th>Descripción</th>
                    <th class="cant">Cantidad</th>
                    <th class="u">Unidad</th>
                </tr>
            </thead>
            <tbody>
            @foreach($remito->items as $i => $item)
            <tr>
                <td class="idx">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</td>
                <td>{{ $item->descripcion }}</td>
                <td class="cant">{{ rtrim(rtrim(number_format($item->cantidad, 3, ',', '.'), '0'), ',') }}</td>
                <td class="u">{{ $item->unidad }}</td>
            </tr>
            @endforeach
            </tbody>
        </table>

    </div>{{-- /page-ct --}}

    {{-- ══ PIE ══ --}}
    <div class="page-ft">

        {{-- Firma recibí conforme — pegada arriba de observaciones --}}
        <div class="firma-recibe">
            <div class="firma-line"></div>
            <div class="firma-lbl">Recibí conforme</div>
            <div class="firma-sub">Firma y aclaración del receptor</div>
        </div>

        <div class="cond-pane" style="margin-top: 18px;">
            <h3>Observaciones</h3>
            <p>{{ $remito->observaciones ?: 'Sin observaciones.' }}</p>
            <div class="cond-dates">
                Emitido el {{ $remito->fecha->format('d/m/Y') }}
                @if($remito->tieneAutorizacion())
                    &nbsp;·&nbsp; Comprobante electrónico autorizado por ARCA
                @elseif($remito->tieneCai())
                    &nbsp;·&nbsp; Comprobante fiscal autorizado — CAI vigente
                @else
                    &nbsp;·&nbsp; Documento interno — no válido como comprobante fiscal
                @endif
            </div>
        </div>

        {{-- ── Bloque Remito Electrónico ── --}}
        @if($remito->tieneAutorizacion())
        <div class="cai-block">
            <div class="cai-data">
                <div class="cai-label">Código de Autorización ARCA</div>
                <div class="cai-code">{{ $remito->cod_autorizacion }}</div>
                @if($remito->cod_autorizacion_vto)
                <div class="cai-vto">Vto. autorización: <strong>{{ $remito->cod_autorizacion_vto->format('d/m/Y') }}</strong></div>
                @endif
                <div class="cai-nro">
                    N° comprobante: <span class="mono">{{ $remito->numeroElectronicoFormateado() }}</span>
                    &nbsp;·&nbsp; Tipo: 91 — Remito R (Electrónico)
                </div>
            </div>
            <div class="cai-bc">
                <svg id="barcode-cai"></svg>
                <div style="font-size:8.5px;color:#9aa0a8;margin-top:3px;letter-spacing:.04em">{{ $remito->cod_autorizacion }}</div>
            </div>
        </div>

        {{-- ── Bloque CAI papel (solo si tiene CAI y no es electrónico) ── --}}
        @elseif($remito->tieneCai() && $cai)
        <div class="cai-block">
            <div class="cai-data">
                <div class="cai-label">CAI</div>
                <div class="cai-code">{{ $cai->codigo }}</div>
                <div class="cai-vto">Fecha de vencimiento: <strong>{{ $cai->vencimiento->format('d/m/Y') }}</strong></div>
                <div class="cai-nro">
                    N° comprobante: <span class="mono">{{ $remito->numeroFormateado() }}</span>
                    &nbsp;·&nbsp; Tipo de comprobante: R (Remito)
                </div>
            </div>
            <div class="cai-bc">
                <svg id="barcode-cai"></svg>
                <div style="font-size:8.5px;color:#9aa0a8;margin-top:3px;letter-spacing:.04em">{{ $cai->codigo }}</div>
            </div>
        </div>
        @endif

        <div class="doc-footer">
            <div class="left">
                <strong>{{ $empresa }}</strong>
                @if($dir) · {{ $dir }}@endif
                <br>
                @if($tel)Tel. {{ $tel }}@endif
                @if($cuit) · CUIT {{ $cuit }}@endif
                @if($email) · {{ $email }}@endif
            </div>
            <div class="mono">{{ $remito->numeroFormateado() }} · {{ $remito->fecha->format('d/m/Y') }}</div>
        </div>

    </div>{{-- /page-ft --}}

</div>{{-- /v1 --}}

@if($remito->tieneAutorizacion() || ($remito->tieneCai() && $cai))
<script>
document.addEventListener('DOMContentLoaded', function () {
    var codigo = @if($remito->tieneAutorizacion())
        "{{ $remito->cod_autorizacion }}"
    @else
        "{{ $cai->codigo }}"
    @endif;
    JsBarcode("#barcode-cai", codigo, {
        format:      "CODE128",
        lineColor:   "#15171a",
        width:       1.4,
        height:      36,
        displayValue: false,
        margin:      0,
    });
});
</script>
@endif

</body>
</html>

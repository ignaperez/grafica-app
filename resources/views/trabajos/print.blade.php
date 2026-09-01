<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trabajo #{{ $trabajo->id }} — Imprimir</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px; color: #111; background: #fff; padding: 20mm 18mm;
        }

        .header {
            display: flex; justify-content: space-between; align-items: flex-start;
            border-bottom: 2px solid #111; padding-bottom: 10px; margin-bottom: 14px;
        }
        .header-logo { font-size: 20px; font-weight: 900; letter-spacing: -0.5px; color: #111; text-transform: uppercase; }
        .header-logo span { color: #c0392b; }
        .header-info { text-align: right; line-height: 1.7; }
        .header-info .num { font-size: 22px; font-weight: 800; color: #111; }
        .estado {
            display: inline-block; padding: 2px 10px; border-radius: 4px; font-size: 10px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.8px; background: #eee; color: #555; margin-top: 3px;
        }
        .estado.terminado { background: #e8f5e9; color: #2e7d32; }
        .estado.en_produccion { background: #fff3e0; color: #e65100; }
        .estado.pendiente { background: #fafafa; color: #777; }

        .meta {
            display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px 16px;
            background: #f7f7f7; border: 1px solid #ddd; border-radius: 6px; padding: 12px 14px; margin-bottom: 16px;
        }
        .label { font-size: 8px; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; color: #888; margin-bottom: 2px; }
        .value { font-size: 12px; font-weight: 500; color: #111; }
        .value.mono { font-family: 'Courier New', monospace; }
        .value.accent { color: #c0392b; font-weight: 700; font-family: 'Courier New', monospace; }
        .value a { color: #c0392b; text-decoration: none; font-weight: 700; }

        .section-title {
            font-size: 9px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase;
            color: #666; margin: 18px 0 10px;
        }

        .datos-grid {
            display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px 16px;
            border: 1px solid #eee; border-radius: 6px; padding: 12px 14px;
        }

        .obs-block {
            margin-top: 14px; padding: 10px 14px; border-left: 3px solid #c0392b; background: #fafafa;
            font-size: 11.5px; color: #333; line-height: 1.55;
        }
        .obs-block strong {
            display: block; font-size: 8px; font-weight: 700; letter-spacing: 1.2px;
            text-transform: uppercase; color: #888; margin-bottom: 4px;
        }

        /* Referencias — miniaturas grandes */
        .refs-grid { display: flex; flex-wrap: wrap; gap: 10px; }
        .ref-card { width: 150px; page-break-inside: avoid; }
        .ref-card img {
            width: 150px; height: 150px; object-fit: cover; border-radius: 6px; border: 1px solid #ddd; display: block;
        }
        .ref-file {
            width: 150px; height: 150px; border-radius: 6px; border: 1px solid #ddd; background: #f4f4f4;
            display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px;
        }
        .ref-file .ext {
            font-size: 15px; font-weight: 800; letter-spacing: 1px; color: #999;
            font-family: 'Courier New', monospace; background: #fff; border: 1px solid #ddd; border-radius: 4px; padding: 4px 10px;
        }
        .ref-name {
            font-size: 8.5px; color: #777; margin-top: 4px; max-width: 150px; overflow: hidden;
            text-overflow: ellipsis; white-space: nowrap; text-align: center;
        }

        .imprimir-list { list-style: none; }
        .imprimir-list li { font-size: 11px; color: #444; padding: 3px 0; }
        .imprimir-list li::before { content: '↓ '; color: #c0392b; font-weight: 700; }

        .footer {
            margin-top: 22px; border-top: 1px solid #ddd; padding-top: 8px;
            display: flex; justify-content: space-between; align-items: center; color: #aaa; font-size: 9px;
        }

        @media print {
            body { padding: 12mm 14mm; }
            .no-print { display: none !important; }
            a { color: inherit; text-decoration: none; }
        }
    </style>
</head>
<body>

<div class="no-print" style="position:fixed;top:16px;right:16px;display:flex;gap:8px;z-index:99">
    <button onclick="window.print()" style="padding:8px 18px;background:#c0392b;color:#fff;border:none;border-radius:6px;font-size:13px;font-weight:600;cursor:pointer">🖨 Imprimir</button>
    <button onclick="window.close()" style="padding:8px 14px;background:#eee;color:#333;border:none;border-radius:6px;font-size:13px;cursor:pointer">✕ Cerrar</button>
</div>

{{-- Cabecera --}}
<div class="header">
    <div>
        <div class="header-logo">Gráfica<span>.</span></div>
        <div style="font-size:9px;color:#999;margin-top:3px;letter-spacing:0.5px">Ficha de Trabajo</div>
    </div>
    <div class="header-info">
        <div class="num"># {{ str_pad($trabajo->id, 5, '0', STR_PAD_LEFT) }}</div>
        <div><span class="estado {{ $trabajo->estado }}">{{ ucfirst(str_replace('_', ' ', $trabajo->estado)) }}</span></div>
        <div style="font-size:9px;color:#999;margin-top:5px">Impreso: {{ now()->format('d/m/Y H:i') }}</div>
    </div>
</div>

{{-- Datos generales --}}
<div class="meta">
    <div>
        <div class="label">Cliente</div>
        <div class="value">{{ $trabajo->cliente->nombre ?? $trabajo->orden->cliente->nombre ?? '-' }}</div>
    </div>
    <div>
        <div class="label">Orden de trabajo</div>
        <div class="value">
            @if($trabajo->orden_trabajo_id)
                <a href="#">OT #{{ str_pad($trabajo->orden_trabajo_id, 4, '0', STR_PAD_LEFT) }}</a>
            @else
                <span style="color:#999">Sin asignar</span>
            @endif
        </div>
    </div>
    <div>
        <div class="label">Fecha de entrega</div>
        <div class="value">{{ $trabajo->fecha_entrega ? \Carbon\Carbon::parse($trabajo->fecha_entrega)->format('d/m/Y') : '-' }}</div>
    </div>
    <div>
        <div class="label">Fecha de carga</div>
        <div class="value">{{ $trabajo->fecha_carga ? \Carbon\Carbon::parse($trabajo->fecha_carga)->format('d/m/Y') : ($trabajo->created_at ? $trabajo->created_at->format('d/m/Y') : '-') }}</div>
    </div>
</div>

{{-- Datos técnicos --}}
<div class="section-title">Datos del trabajo</div>
<div class="datos-grid">
    @if($trabajo->tipoTrabajo)
    <div><div class="label">Tipo de trabajo</div><div class="value">{{ $trabajo->tipoTrabajo->nombre }}</div></div>
    @endif
    @if($trabajo->material)
    <div><div class="label">Material</div><div class="value">{{ $trabajo->material->nombre }}</div></div>
    @endif
    @if($trabajo->maquina)
    <div><div class="label">Máquina</div><div class="value">{{ $trabajo->maquina->nombre }}</div></div>
    @endif
    <div><div class="label">Unidad</div><div class="value mono">{{ $trabajo->unidad ?? '-' }}</div></div>

    @if($trabajo->ancho || $trabajo->alto)
    <div><div class="label">Medidas</div><div class="value mono">{{ rtrim(rtrim((string)$trabajo->ancho,'0'),'.') }}m × {{ rtrim(rtrim((string)$trabajo->alto,'0'),'.') }}m</div></div>
    <div><div class="label">m² total</div><div class="value accent">{{ number_format($trabajo->ancho * $trabajo->alto * $trabajo->cantidad, 2) }} m²</div></div>
    @endif
    @if($trabajo->largo)
    <div><div class="label">Largo</div><div class="value mono">{{ rtrim(rtrim((string)$trabajo->largo,'0'),'.') }} m</div></div>
    @endif

    <div><div class="label">Cantidad</div><div class="value mono">{{ $trabajo->cantidad }}</div></div>
</div>

{{-- Descripción --}}
@if($trabajo->descripcion)
<div class="obs-block">
    <strong>Descripción / Observaciones</strong>
    {{ $trabajo->descripcion }}
</div>
@endif

{{-- Referencias (miniaturas) --}}
@if($trabajo->referencias->isNotEmpty())
<div class="section-title">Referencias ({{ $trabajo->referencias->count() }})</div>
<div class="refs-grid">
    @foreach($trabajo->referencias as $ref)
        @php
            $ext = strtolower(pathinfo($ref->nombre_original, PATHINFO_EXTENSION));
            $esImagen = in_array($ext, ['jpg','jpeg','png','gif','bmp','webp','tif','tiff']);
        @endphp
        <div class="ref-card">
            @if($esImagen)
                <img src="{{ $ref->url }}" alt="{{ $ref->nombre_original }}">
            @else
                <div class="ref-file"><span class="ext">{{ strtoupper($ext) ?: 'ARCH' }}</span></div>
            @endif
            <div class="ref-name" title="{{ $ref->nombre_original }}">{{ $ref->nombre_original }}</div>
        </div>
    @endforeach
</div>
@endif

{{-- Archivos para imprimir --}}
@if($trabajo->archivosImprimir->isNotEmpty())
<div class="section-title">Archivos para imprimir ({{ $trabajo->archivosImprimir->count() }})</div>
<ul class="imprimir-list">
    @foreach($trabajo->archivosImprimir as $arch)
        <li>{{ $arch->nombre_original }} <span style="color:#aaa">· {{ $arch->tamanioFormateado }}</span></li>
    @endforeach
</ul>
@endif

<div class="footer">
    <span>Trabajo #{{ $trabajo->id }} — {{ $trabajo->cliente->nombre ?? $trabajo->orden->cliente->nombre ?? '' }}</span>
    <span>{{ now()->format('d/m/Y H:i') }}</span>
</div>

</body>
</html>

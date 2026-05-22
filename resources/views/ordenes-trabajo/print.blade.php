<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orden #{{ $orden->id }} — Imprimir</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #111;
            background: #fff;
            padding: 20mm 18mm;
        }

        /* ── Cabecera ─────────────────────────────────────── */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #111;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }
        .header-logo {
            font-size: 20px;
            font-weight: 900;
            letter-spacing: -0.5px;
            color: #111;
            text-transform: uppercase;
        }
        .header-logo span {
            color: #c0392b;
        }
        .header-info {
            text-align: right;
            line-height: 1.7;
        }
        .header-info .orden-num {
            font-size: 22px;
            font-weight: 800;
            color: #111;
        }
        .header-info .orden-estado {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            background: #f0f0f0;
            color: #333;
            margin-top: 3px;
        }

        /* ── Datos de la orden ────────────────────────────── */
        .orden-meta {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px 16px;
            background: #f7f7f7;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 10px 14px;
            margin-bottom: 18px;
        }
        .meta-field {}
        .meta-label {
            font-size: 8px;
            font-weight: 700;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            color: #888;
            margin-bottom: 2px;
        }
        .meta-value {
            font-size: 12px;
            font-weight: 500;
            color: #111;
        }

        /* ── Observaciones ────────────────────────────────── */
        .obs-block {
            margin-bottom: 18px;
            padding: 8px 12px;
            border-left: 3px solid #c0392b;
            background: #fafafa;
            font-size: 11px;
            color: #333;
            line-height: 1.5;
        }
        .obs-block strong {
            display: block;
            font-size: 8px;
            font-weight: 700;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            color: #888;
            margin-bottom: 3px;
        }

        /* ── Título de sección ────────────────────────────── */
        .section-title {
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #666;
            margin-bottom: 10px;
        }

        /* ── Trabajo card ─────────────────────────────────── */
        .trabajo {
            page-break-inside: avoid;
            margin-bottom: 14px;
        }
        .trabajo + .trabajo {
            border-top: 1.5px dashed #ccc;
            padding-top: 14px;
        }

        .trabajo-header {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            margin-bottom: 8px;
        }
        .trabajo-num {
            font-size: 9px;
            font-weight: 700;
            color: #999;
            letter-spacing: 1px;
            font-family: 'Courier New', monospace;
        }
        .trabajo-desc {
            font-size: 13px;
            font-weight: 700;
            color: #111;
            margin-left: 8px;
        }
        .trabajo-estado {
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            padding: 2px 8px;
            border-radius: 3px;
            background: #eee;
            color: #555;
        }
        .trabajo-estado.terminado { background: #e8f5e9; color: #2e7d32; }
        .trabajo-estado.en_produccion { background: #fff3e0; color: #e65100; }
        .trabajo-estado.pendiente { background: #fafafa; color: #777; }

        .trabajo-body {
            display: flex;
            gap: 14px;
        }

        /* Datos técnicos (izquierda) */
        .trabajo-datos {
            flex: 1;
        }
        .datos-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 6px 12px;
        }
        .dato-field {}
        .dato-label {
            font-size: 8px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #999;
            margin-bottom: 1px;
        }
        .dato-value {
            font-size: 11.5px;
            color: #111;
            font-weight: 500;
        }
        .dato-value.mono {
            font-family: 'Courier New', monospace;
            font-size: 11px;
        }
        .dato-value.accent {
            color: #c0392b;
            font-weight: 700;
            font-family: 'Courier New', monospace;
        }

        /* Archivos / referencias (derecha) */
        .trabajo-archivos {
            width: 160px;
            flex-shrink: 0;
        }
        .arch-label {
            font-size: 8px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #999;
            margin-bottom: 5px;
        }
        .refs-row {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin-bottom: 8px;
        }
        .ref-thumb {
            width: 52px;
            height: 52px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        .ref-file-badge {
            width: 52px;
            height: 52px;
            border-radius: 4px;
            border: 1px solid #ddd;
            background: #f4f4f4;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8px;
            font-weight: 700;
            letter-spacing: 0.5px;
            color: #777;
            text-transform: uppercase;
            font-family: 'Courier New', monospace;
        }
        .imprimir-list {
            list-style: none;
        }
        .imprimir-list li {
            font-size: 9.5px;
            color: #555;
            padding: 1px 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 155px;
        }
        .imprimir-list li::before {
            content: '↓ ';
            color: #c0392b;
            font-weight: 700;
        }

        /* ── Pie ──────────────────────────────────────────── */
        .footer {
            margin-top: 20px;
            border-top: 1px solid #ddd;
            padding-top: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #aaa;
            font-size: 9px;
        }

        /* ── Print ────────────────────────────────────────── */
        @media print {
            body { padding: 12mm 14mm; }
            .no-print { display: none !important; }
            a { color: inherit; text-decoration: none; }
        }
    </style>
</head>
<body>

{{-- Botón imprimir (solo pantalla) --}}
<div class="no-print" style="position:fixed;top:16px;right:16px;display:flex;gap:8px;z-index:99">
    <button onclick="window.print()"
            style="padding:8px 18px;background:#c0392b;color:#fff;border:none;border-radius:6px;
                   font-size:13px;font-weight:600;cursor:pointer">
        🖨 Imprimir
    </button>
    <button onclick="window.close()"
            style="padding:8px 14px;background:#eee;color:#333;border:none;border-radius:6px;
                   font-size:13px;cursor:pointer">
        ✕ Cerrar
    </button>
</div>

{{-- ── Cabecera ─────────────────────────────────────────────── --}}
<div class="header">
    <div>
        <div class="header-logo">Gráfica<span>.</span></div>
        <div style="font-size:9px;color:#999;margin-top:3px;letter-spacing:0.5px">
            Orden de Trabajo
        </div>
    </div>
    <div class="header-info">
        <div class="orden-num"># {{ str_pad($orden->id, 5, '0', STR_PAD_LEFT) }}</div>
        <div>
            <span class="orden-estado">{{ ucfirst(str_replace('_', ' ', $orden->estado)) }}</span>
        </div>
        <div style="font-size:9px;color:#999;margin-top:5px">
            Impreso: {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>
</div>

{{-- ── Datos de la orden ────────────────────────────────────── --}}
<div class="orden-meta">
    <div class="meta-field">
        <div class="meta-label">Cliente</div>
        <div class="meta-value">{{ $orden->cliente->nombre ?? '-' }}</div>
    </div>
    <div class="meta-field">
        <div class="meta-label">Fecha de ingreso</div>
        <div class="meta-value">
            {{ $orden->fecha_recibido ? \Carbon\Carbon::parse($orden->fecha_recibido)->format('d/m/Y') : '-' }}
        </div>
    </div>
    <div class="meta-field">
        <div class="meta-label">Total trabajos</div>
        <div class="meta-value">{{ $orden->trabajos->count() }}</div>
    </div>
</div>

@if($orden->observaciones)
<div class="obs-block">
    <strong>Observaciones</strong>
    {{ $orden->observaciones }}
</div>
@endif

{{-- ── Trabajos ──────────────────────────────────────────────── --}}
<div class="section-title">Trabajos</div>

@foreach($orden->trabajos as $i => $t)
<div class="trabajo">

    {{-- Header del trabajo --}}
    <div class="trabajo-header">
        <div style="display:flex;align-items:baseline;gap:0">
            <span class="trabajo-num">#{{ $t->id }}</span>
            <span class="trabajo-desc">
                {{ $t->descripcion ?? ('Trabajo ' . ($i + 1)) }}
            </span>
        </div>
        <span class="trabajo-estado {{ $t->estado }}">
            {{ ucfirst(str_replace('_', ' ', $t->estado)) }}
        </span>
    </div>

    {{-- Cuerpo --}}
    <div class="trabajo-body">

        {{-- Datos técnicos --}}
        <div class="trabajo-datos">
            <div class="datos-grid">

                @if($t->tipoTrabajo)
                <div class="dato-field">
                    <div class="dato-label">Tipo de trabajo</div>
                    <div class="dato-value">{{ $t->tipoTrabajo->nombre }}</div>
                </div>
                @endif

                @if($t->material)
                <div class="dato-field">
                    <div class="dato-label">Material</div>
                    <div class="dato-value">{{ $t->material->nombre }}</div>
                </div>
                @endif

                @if($t->maquina)
                <div class="dato-field">
                    <div class="dato-label">Máquina</div>
                    <div class="dato-value">{{ $t->maquina->nombre }}</div>
                </div>
                @endif

                @if($t->ancho || $t->alto)
                <div class="dato-field">
                    <div class="dato-label">Medidas</div>
                    <div class="dato-value mono">{{ $t->ancho }}m × {{ $t->alto }}m</div>
                </div>
                <div class="dato-field">
                    <div class="dato-label">m² total</div>
                    <div class="dato-value accent">
                        {{ number_format($t->ancho * $t->alto * $t->cantidad, 2) }} m²
                    </div>
                </div>
                @endif

                <div class="dato-field">
                    <div class="dato-label">Cantidad</div>
                    <div class="dato-value mono">{{ $t->cantidad }}</div>
                </div>

                @if($t->fecha_entrega)
                <div class="dato-field">
                    <div class="dato-label">Fecha entrega</div>
                    <div class="dato-value">{{ $t->fecha_entrega->format('d/m/Y') }}</div>
                </div>
                @endif

            </div>
        </div>

        {{-- Archivos --}}
        @if($t->referencias->isNotEmpty() || $t->archivosImprimir->isNotEmpty())
        <div class="trabajo-archivos">

            @if($t->referencias->isNotEmpty())
            <div class="arch-label">Referencias</div>
            <div class="refs-row">
                @foreach($t->referencias as $ref)
                    @php
                        $ext = strtolower(pathinfo($ref->nombre_original, PATHINFO_EXTENSION));
                        $esImagen = in_array($ext, ['jpg','jpeg','png','gif','bmp','webp','tif','tiff']);
                    @endphp
                    @if($esImagen)
                        <img src="{{ $ref->url }}" alt="{{ $ref->nombre_original }}" class="ref-thumb">
                    @else
                        <div class="ref-file-badge">{{ strtoupper($ext) }}</div>
                    @endif
                @endforeach
            </div>
            @endif

            @if($t->archivosImprimir->isNotEmpty())
            <div class="arch-label" style="margin-top:2px">Para imprimir</div>
            <ul class="imprimir-list">
                @foreach($t->archivosImprimir as $arch)
                    <li title="{{ $arch->nombre_original }}">{{ $arch->nombre_original }}</li>
                @endforeach
            </ul>
            @endif

        </div>
        @endif

    </div>
</div>
@endforeach

{{-- ── Pie ──────────────────────────────────────────────────── --}}
<div class="footer">
    <span>Orden #{{ $orden->id }} — {{ $orden->cliente->nombre ?? '' }}</span>
    <span>{{ now()->format('d/m/Y H:i') }}</span>
</div>

</body>
</html>

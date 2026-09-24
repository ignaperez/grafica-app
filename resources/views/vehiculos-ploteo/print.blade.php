<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $vehiculo->patente }} — Ficha de ploteo</title>
    <style>
        @page { size: A4; margin: 12mm 10mm; }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: 'DM Sans', -apple-system, 'Segoe UI', Arial, sans-serif;
            font-size: 11px;
            color: #111;
            background: #f4f4f4;
        }

        .hoja {
            width: 190mm;
            margin: 14px auto;
            padding: 10mm;
            background: #fff;
        }

        /* ── Encabezado ─────────────────────────────────────────── */
        .cab {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #111;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .patente {
            font-family: 'DM Mono', 'Consolas', monospace;
            font-size: 30px;
            font-weight: 700;
            letter-spacing: 5px;
            line-height: 1;
            text-transform: uppercase;
        }
        .vehiculo-txt { font-size: 14px; font-weight: 600; margin-top: 4px; }
        .cab-der { text-align: right; font-size: 10px; color: #555; }
        .cab-titulo {
            font-size: 11px;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #888;
            margin-bottom: 4px;
        }
        .chip {
            display: inline-block;
            border: 1px solid #111;
            border-radius: 3px;
            padding: 2px 7px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* ── Secciones ──────────────────────────────────────────── */
        .sec-titulo {
            font-size: 9px;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #888;
            border-bottom: 1px solid #ddd;
            padding-bottom: 3px;
            margin: 14px 0 8px;
        }

        .datos {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 9px 14px;
        }
        .dato-label {
            font-size: 8px;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #999;
            margin-bottom: 1px;
        }
        .dato-valor { font-size: 11.5px; font-weight: 500; word-break: break-word; }
        .mono { font-family: 'DM Mono', 'Consolas', monospace; }

        .obs {
            font-size: 11px;
            line-height: 1.5;
            white-space: pre-line;
            border-left: 3px solid #ddd;
            padding-left: 9px;
        }

        /* ── Imágenes ───────────────────────────────────────────── */
        .img-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; }
        .img-item { page-break-inside: avoid; }
        .img-item img,
        .img-item .doc {
            width: 100%;
            height: 34mm;
            object-fit: cover;
            display: block;
            border: 1px solid #ccc;
            border-radius: 3px;
            background: #fafafa;
        }
        .img-item .doc {
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'DM Mono', monospace;
            font-size: 13px;
            font-weight: 700;
            color: #666;
            letter-spacing: 1px;
        }
        .img-item .vacia {
            border-style: dashed;
            color: #ccc;
            font-size: 9px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .img-pie {
            font-size: 8.5px;
            color: #666;
            margin-top: 2px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* Las referencias son lo que mira el ploteador: van más grandes */
        .ref-grid { grid-template-columns: repeat(3, 1fr); }
        .ref-grid .img-item img,
        .ref-grid .img-item .doc { height: 48mm; object-fit: contain; }

        .pie {
            margin-top: 16px;
            padding-top: 6px;
            border-top: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
            font-size: 9px;
            color: #888;
        }

        @media print {
            body { background: #fff; }
            .hoja { width: auto; margin: 0; padding: 0; }
            .no-print { display: none !important; }
            .evitar-corte { page-break-inside: avoid; }
        }
    </style>
</head>
<body>

<div class="no-print" style="position:fixed;top:16px;right:16px;display:flex;gap:8px;z-index:99">
    <button onclick="window.print()"
            style="padding:9px 16px;border:none;border-radius:8px;background:#e6502a;color:#fff;
                   font-size:13px;font-weight:600;cursor:pointer">
        🖨 Imprimir
    </button>
    <button onclick="window.close()"
            style="padding:9px 16px;border:1px solid #bbb;border-radius:8px;background:#fff;color:#444;
                   font-size:13px;cursor:pointer">
        ✕ Cerrar
    </button>
</div>

<div class="hoja">

    {{-- ── Encabezado ─────────────────────────────────────────── --}}
    <div class="cab">
        <div>
            <div class="cab-titulo">Ficha de ploteo</div>
            <div class="patente">{{ $vehiculo->patente }}</div>
            <div class="vehiculo-txt">{{ $vehiculo->marca }} {{ $vehiculo->modelo }}</div>
        </div>
        <div class="cab-der">
            <span class="chip">
                @if($vehiculo->tipo_ploteo === 'completo')
                    Ploteo completo
                @else
                    Parcial{{ $vehiculo->sector ? ' · ' . (\App\Models\VehiculoPloteo::sectores()[$vehiculo->sector] ?? $vehiculo->sector) : '' }}
                @endif
            </span>
            <div style="margin-top:6px">Vehículo #{{ $vehiculo->id }}</div>
            <div>Impreso {{ now()->format('d/m/Y H:i') }}</div>
        </div>
    </div>

    {{-- ── Datos ──────────────────────────────────────────────── --}}
    <div class="datos">
        @php
            $datos = [
                'Cliente'         => $vehiculo->cliente->nombre ?? '—',
                'Fecha de ploteo' => $vehiculo->fecha_ploteo ? $vehiculo->fecha_ploteo->format('d/m/Y') : '—',
                'Tipo de ploteo'  => $vehiculo->tipo_ploteo === 'completo' ? 'Completo' : 'Parcial',
            ];
            if ($vehiculo->tipo_ploteo !== 'completo' && $vehiculo->sector) {
                $datos['Sector'] = \App\Models\VehiculoPloteo::sectores()[$vehiculo->sector] ?? $vehiculo->sector;
            }
            if ($vehiculo->orden_trabajo_id) {
                $datos['Orden de trabajo'] = '#' . str_pad($vehiculo->orden_trabajo_id, 4, '0', STR_PAD_LEFT);
            }
            if ($vehiculo->presupuesto) {
                $datos['Presupuesto'] = $vehiculo->presupuesto->numeroFormateado();
            }
            $datos['Cargado'] = $vehiculo->created_at?->format('d/m/Y H:i') ?? '—';
        @endphp

        @foreach($datos as $etiqueta => $valor)
            <div>
                <div class="dato-label">{{ $etiqueta }}</div>
                <div class="dato-valor">{{ $valor }}</div>
            </div>
        @endforeach
    </div>

    {{-- ── Observaciones ──────────────────────────────────────── --}}
    @if(trim((string) $vehiculo->observaciones) !== '')
        <div class="sec-titulo">Observaciones</div>
        <div class="obs">{{ $vehiculo->observaciones }}</div>
    @endif

    {{-- ── Referencias: lo que el ploteador necesita ver ───────── --}}
    @php $refs = $vehiculo->referencias; @endphp
    @if($refs->isNotEmpty() || $vehiculo->refe)
        <div class="sec-titulo">Referencias ({{ $refs->count() ?: 1 }})</div>
        <div class="img-grid ref-grid">
            @foreach($refs as $ref)
                <div class="img-item">
                    @if($ref->es_imagen)
                        <img src="{{ $ref->url }}" alt="{{ $ref->nombre_original }}">
                    @else
                        <div class="doc">{{ strtoupper($ref->extension) ?: 'ARCH' }}</div>
                    @endif
                    <div class="img-pie">{{ $ref->nombre_original }}</div>
                </div>
            @endforeach

            @if($vehiculo->refe && $refs->isEmpty())
                @php $extRefe = strtolower(pathinfo($vehiculo->refe, PATHINFO_EXTENSION)); @endphp
                <div class="img-item">
                    @if(in_array($extRefe, ['jpg','jpeg','png','webp','gif']))
                        <img src="{{ route('vehiculos-ploteo.foto', [$vehiculo->id, 'refe']) }}" alt="Referencia">
                    @else
                        <div class="doc">{{ strtoupper($extRefe) ?: 'ARCH' }}</div>
                    @endif
                    <div class="img-pie">Referencia</div>
                </div>
            @endif
        </div>
    @endif

    {{-- ── Fotos antes / después ──────────────────────────────── --}}
    @foreach([
        ['Antes de plotear',   ['foto_antes_frente'=>'Frente','foto_antes_atras'=>'Atrás','foto_antes_izq'=>'Izquierda','foto_antes_der'=>'Derecha']],
        ['Después de plotear', ['foto_despues_frente'=>'Frente','foto_despues_atras'=>'Atrás','foto_despues_izq'=>'Izquierda','foto_despues_der'=>'Derecha']],
    ] as $bloque)
        @php $hayAlguna = collect(array_keys($bloque[1]))->contains(fn ($c) => (bool) $vehiculo->$c); @endphp
        @if($hayAlguna)
            <div class="sec-titulo">{{ $bloque[0] }}</div>
            <div class="img-grid evitar-corte">
                @foreach($bloque[1] as $campo => $label)
                    <div class="img-item">
                        @if($vehiculo->$campo)
                            <img src="{{ route('vehiculos-ploteo.foto', [$vehiculo->id, $campo]) }}" alt="{{ $label }}">
                        @else
                            <div class="doc vacia">sin foto</div>
                        @endif
                        <div class="img-pie">{{ $label }}</div>
                    </div>
                @endforeach
            </div>
        @endif
    @endforeach

    {{-- ── Pie ────────────────────────────────────────────────── --}}
    <div class="pie">
        <span>{{ $vehiculo->patente }} — {{ $vehiculo->marca }} {{ $vehiculo->modelo }}</span>
        <span>{{ $vehiculo->cliente->nombre ?? '' }}</span>
    </div>

</div>

</body>
</html>

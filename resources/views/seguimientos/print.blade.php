<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Seguimiento {{ $anio }}</title>
    <style>
        @page { size: A4 landscape; margin: 8mm; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, Helvetica, sans-serif; color: #111; font-size: 8px; }

        .toolbar { background:#1a1a1a; color:#ccc; padding:8px 16px; display:flex; justify-content:space-between; align-items:center; font-size:12px; }
        .toolbar button { background:#e6502a; color:#fff; border:none; padding:7px 18px; border-radius:5px; cursor:pointer; font-size:12px; font-weight:600; }

        .head { display:flex; justify-content:space-between; align-items:flex-end; padding:10px 4px 6px; }
        .head h1 { font-size:14px; }
        .head .tot { display:flex; gap:16px; font-size:9px; }
        .head .tot b { display:block; font-size:11px; }

        table { width:100%; border-collapse:collapse; }
        th, td { border:0.4pt solid #999; padding:2.5px 3px; text-align:left; vertical-align:middle; }
        th { background:#e8e8e8; font-size:7px; text-transform:uppercase; letter-spacing:.02em; }
        td.r { text-align:right; font-variant-numeric:tabular-nums; }
        td.c { text-align:center; }
        tr.cobrado td { background:#dcf3e6; }
        .chip { display:inline-block; padding:2px 6px; border-radius:10px; font-size:6.5px; font-weight:700; }

        @media print { .toolbar { display:none; } body { font-size:7.5px; } }
    </style>
</head>
<body>

<div class="toolbar">
    <span>Seguimiento — Año {{ $anio }}</span>
    <button onclick="window.print()">Imprimir</button>
</div>

<div class="head">
    <h1>Seguimiento de facturación · {{ $anio }}</h1>
    <div class="tot">
        <div>Presupuestado <b>${{ number_format($totales['presupuestado'], 2, ',', '.') }}</b></div>
        <div>Facturado <b>${{ number_format($totales['facturado'], 2, ',', '.') }}</b></div>
        <div>Cobrado <b>${{ number_format($totales['cobrado'], 2, ',', '.') }}</b></div>
        <div>Pendiente <b>${{ number_format($totales['pendiente'], 2, ',', '.') }}</b></div>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>Fecha</th>
            <th>Presup.</th>
            <th class="r">Monto</th>
            <th>Área</th>
            <th>Detalle</th>
            <th>OC</th>
            <th class="r">M. O.P.</th>
            <th>F. Fact.</th>
            <th>Factura</th>
            <th>Estado</th>
            <th>Obs.</th>
            <th>Pasó a</th>
            <th class="r">21%</th>
            <th class="r">5%</th>
            <th>F. transf.</th>
            <th class="r">TRANSF.</th>
        </tr>
    </thead>
    <tbody>
        @forelse($seguimientos as $s)
        <tr class="{{ $s->estado === 'cobrado' ? 'cobrado' : '' }}">
            <td>{{ $s->fechaRef()?->format('d/m/y') ?? '—' }}</td>
            <td>{{ $s->numeroRef() }}</td>
            <td class="r">${{ number_format($s->montoBase(), 2, ',', '.') }}</td>
            <td>{{ $s->area_oficina }}</td>
            <td>{{ $s->detalle }}</td>
            <td class="c">{{ $s->orden_compra }}</td>
            <td class="r">{{ $s->monto_op !== null ? '$'.number_format($s->monto_op, 2, ',', '.') : '' }}</td>
            <td>{{ $s->factura?->fecha?->format('d/m/y') ?? '—' }}</td>
            <td>{{ $s->factura?->numeroFormateado() ?? '—' }}</td>
            <td><span class="chip" style="background:{{ $s->estadoBg() }};color:{{ $s->estadoText() }}">{{ $s->estadoLabel() }}</span></td>
            <td>{{ $s->observaciones }}</td>
            <td>{{ $s->pasado_a }}</td>
            <td class="r">{{ $s->mostrarCalculos() ? '$'.number_format($s->iva21(), 2, ',', '.') : '' }}</td>
            <td class="r">{{ $s->mostrarCalculos() ? '$'.number_format($s->cinco(), 2, ',', '.') : '' }}</td>
            <td>{{ $s->fecha_pago?->format('d/m/y') }}</td>
            <td class="r">{{ $s->mostrarCalculos() ? '$'.number_format($s->totalHernan(), 2, ',', '.') : '' }}</td>
        </tr>
        @empty
        <tr><td colspan="16" style="text-align:center;padding:20px">No hay presupuestos en {{ $anio }}.</td></tr>
        @endforelse
    </tbody>
</table>

</body>
</html>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        table { border-collapse: collapse; }
        th, td { border: 0.5pt solid #999; padding: 3px 6px; font-family: Calibri, Arial, sans-serif; font-size: 10pt; vertical-align: top; }
        th { background: #1f2d3d; color: #fff; font-weight: bold; text-align: left; }
        td.num { mso-number-format: "\#\,\#\#0\.00"; text-align: right; }
        .titulo { font-size: 14pt; font-weight: bold; }
    </style>
</head>
<body>
    <table>
        <tr><td class="titulo" colspan="17">Seguimiento {{ $anio }}</td></tr>
    </table>
    <br>
    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>N° Presup.</th>
                <th>Cliente</th>
                <th>Monto</th>
                <th>Área / Oficina</th>
                <th>Detalle</th>
                <th>OC</th>
                <th>Monto O.P.</th>
                <th>F. Factura</th>
                <th>Factura</th>
                <th>Estado</th>
                <th>Observaciones</th>
                <th>Pasó a</th>
                <th>IVA 21%</th>
                <th>5%</th>
                <th>F. Transf.</th>
                <th>TRANSF.</th>
            </tr>
        </thead>
        <tbody>
            @foreach($seguimientos as $s)
            <tr>
                <td>{{ $s->fechaRef()?->format('d/m/Y') ?? '' }}</td>
                <td>{{ $s->numeroRef() }}</td>
                <td>{{ $s->presupuesto?->cliente?->nombre ?? '' }}</td>
                <td class="num">{{ number_format($s->montoBase(), 2, ',', '') }}</td>
                <td>{{ $s->area_oficina }}</td>
                <td>{{ $s->detalle }}</td>
                <td>{{ $s->orden_compra }}</td>
                <td class="num">{{ $s->monto_op !== null ? number_format($s->monto_op, 2, ',', '') : '' }}</td>
                <td>{{ $s->factura?->fecha?->format('d/m/Y') ?? '' }}</td>
                <td>{{ $s->factura?->numeroFormateado() ?? '' }}</td>
                <td>{{ $s->estadoLabel() }}</td>
                <td>{{ $s->observaciones }}</td>
                <td>{{ $s->pasado_a }}</td>
                <td class="num">{{ $s->mostrarCalculos() ? number_format($s->iva21(), 2, ',', '') : '' }}</td>
                <td class="num">{{ $s->mostrarCalculos() ? number_format($s->cinco(), 2, ',', '') : '' }}</td>
                <td>{{ $s->fecha_pago?->format('d/m/Y') ?? '' }}</td>
                <td class="num">{{ $s->mostrarCalculos() ? number_format($s->totalHernan(), 2, ',', '') : '' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3" style="text-align:right">TOTALES</th>
                <th class="num" style="background:#eef;color:#111">{{ number_format($totales['presupuestado'], 2, ',', '') }}</th>
                <th colspan="13" style="background:#fff;color:#111"></th>
            </tr>
            <tr>
                <td colspan="3" style="text-align:right;font-weight:bold">Presupuestado: {{ number_format($totales['presupuestado'], 2, ',', '.') }}
                    &nbsp;·&nbsp; Facturado: {{ number_format($totales['facturado'], 2, ',', '.') }}
                    &nbsp;·&nbsp; Cobrado: {{ number_format($totales['cobrado'], 2, ',', '.') }}
                    &nbsp;·&nbsp; Pendiente: {{ number_format($totales['pendiente'], 2, ',', '.') }}</td>
                <td colspan="14"></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>

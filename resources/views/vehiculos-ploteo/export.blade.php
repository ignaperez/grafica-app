<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        table { border-collapse: collapse; }
        th, td { border: 0.5pt solid #999; padding: 3px 6px; font-family: Calibri, Arial, sans-serif; font-size: 10pt; vertical-align: top; }
        th { background: #1f2d3d; color: #fff; font-weight: bold; text-align: left; }
        .titulo { font-size: 14pt; font-weight: bold; }
    </style>
</head>
<body>
    <table><tr><td class="titulo" colspan="9">Vehículos ploteados — {{ now()->format('d/m/Y') }}</td></tr></table>
    <br>
    <table>
        <thead>
            <tr>
                <th>Patente</th>
                <th>Marca</th>
                <th>Modelo</th>
                <th>Cliente</th>
                <th>Fecha ploteo</th>
                <th>Tipo ploteo</th>
                <th>Sector</th>
                <th>Orden</th>
                <th>Presupuestado</th>
            </tr>
        </thead>
        <tbody>
            @php $sectores = \App\Models\VehiculoPloteo::sectores(); @endphp
            @foreach($vehiculos as $v)
            <tr>
                <td>{{ $v->patente }}</td>
                <td>{{ $v->marca }}</td>
                <td>{{ $v->modelo }}</td>
                <td>{{ $v->cliente->nombre ?? '' }}</td>
                <td>{{ $v->fecha_ploteo ? $v->fecha_ploteo->format('d/m/Y') : '' }}</td>
                <td>{{ ucfirst($v->tipo_ploteo ?? '') }}</td>
                <td>{{ $v->sector ? ($sectores[$v->sector] ?? $v->sector) : '' }}</td>
                <td>{{ $v->orden_trabajo_id ? 'OT #'.str_pad($v->orden_trabajo_id, 4, '0', STR_PAD_LEFT) : '' }}</td>
                <td>{{ $v->presupuestado() ? 'Sí' . ($v->presupuesto ? ' (P-'.str_pad($v->presupuesto->numero ?? 0, 4, '0', STR_PAD_LEFT).')' : '') : 'No' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

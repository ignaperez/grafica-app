<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        table { border-collapse: collapse; }
        th, td { border: 0.5pt solid #999; padding: 3px 6px; font-family: Calibri, Arial, sans-serif; font-size: 10pt; vertical-align: top; }
        th { background: #1f2d3d; color: #fff; font-weight: bold; text-align: left; }
        td.num { text-align: right; }
        .titulo { font-size: 14pt; font-weight: bold; }
    </style>
</head>
<body>
    <table><tr><td class="titulo" colspan="7">Presupuestos — {{ now()->format('d/m/Y') }}</td></tr></table>
    <br>
    <table>
        <thead>
            <tr>
                <th>N°</th>
                <th>Cliente</th>
                <th>Fecha</th>
                <th>Vence</th>
                <th>Total</th>
                <th>Estado</th>
                <th>Creado por</th>
            </tr>
        </thead>
        <tbody>
            @foreach($presupuestos as $p)
            <tr>
                <td>{{ $p->numeroFormateado() }}</td>
                <td>{{ $p->cliente->nombre ?? '' }}</td>
                <td>{{ $p->fecha ? \Carbon\Carbon::parse($p->fecha)->format('d/m/Y') : '' }}</td>
                <td>{{ $p->fecha_vencimiento ? \Carbon\Carbon::parse($p->fecha_vencimiento)->format('d/m/Y') : '' }}</td>
                <td class="num">{{ number_format($p->total, 2, ',', '') }}</td>
                <td>{{ $p->estadoLabel() }}</td>
                <td>{{ $p->createdBy->name ?? '' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

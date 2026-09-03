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
    <table><tr><td class="titulo" colspan="8">Facturas — {{ now()->format('d/m/Y') }}</td></tr></table>
    <br>
    <table>
        <thead>
            <tr>
                <th>N° Comprobante</th>
                <th>Tipo</th>
                <th>Cliente</th>
                <th>Fecha</th>
                <th>Total</th>
                <th>CAE</th>
                <th>Estado</th>
                <th>Cobro</th>
            </tr>
        </thead>
        <tbody>
            @foreach($facturas as $f)
            <tr>
                <td>{{ $f->numeroFormateado() }}</td>
                <td>{{ $f->tipoLabel() }}</td>
                <td>{{ $f->cliente->nombre ?? '' }}</td>
                <td>{{ $f->fecha ? \Carbon\Carbon::parse($f->fecha)->format('d/m/Y') : '' }}</td>
                <td class="num">{{ number_format($f->imp_total, 2, ',', '') }}</td>
                <td>{{ $f->cae }}</td>
                <td>{{ $f->estadoLabel() }}</td>
                <td>{{ $f->esFactura() ? $f->estadoCobroLabel() : '' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

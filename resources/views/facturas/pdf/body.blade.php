@php
    $fmtMoney = fn($v) => number_format((float) $v, 2, ',', '.');
    $fmtCant  = function ($v) {
        $v = (float) $v;
        return $v == floor($v)
            ? number_format($v, 0, ',', '.')
            : rtrim(number_format($v, 3, ',', '.'), '0');
    };
    $umLabel = fn($u) => match($u) { 'm2' => 'm²', 'ml' => 'ml', default => 'unidad' };

    // El precio guardado es NETO. En Factura A se discrimina IVA → se muestra el
    // neto tal cual. En B/C (no discrimina) se muestra el precio FINAL (con IVA).
    $precioMostrar = function ($it) use ($ivaDiscriminado) {
        $p = (float) $it->precio_unitario;
        return $ivaDiscriminado ? $p : round($p * (1 + (float) $it->alicuota_iva / 100), 2);
    };
    $subMostrar = function ($it) use ($ivaDiscriminado) {
        $s = (float) $it->subtotal;
        return $ivaDiscriminado ? $s : round($s * (1 + (float) $it->alicuota_iva / 100), 2);
    };
@endphp

<table class="items">
    <thead>
        <tr>
            <th class="l">Descripción</th>
            <th class="c" style="width:16mm">Cantidad</th>
            <th class="c" style="width:16mm">U. Medida</th>
            <th class="r" style="width:26mm">P. Unitario</th>
            <th class="r" style="width:28mm">Subtotal</th>
        </tr>
    </thead>
    <tbody>
        @foreach($factura->items as $it)
        <tr>
            <td class="l">{{ $it->descripcion }}</td>
            <td class="c">{{ $fmtCant($it->cantidad) }}</td>
            <td class="c">{{ $umLabel($it->unidad ?? 'unidad') }}</td>
            <td class="r">$ {{ $fmtMoney($precioMostrar($it)) }}</td>
            <td class="r">$ {{ $fmtMoney($subMostrar($it)) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

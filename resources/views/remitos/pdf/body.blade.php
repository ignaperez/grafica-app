@php
    $fmtCant = function ($v) {
        $v = (float) $v;
        return $v == floor($v)
            ? number_format($v, 0, ',', '.')
            : rtrim(number_format($v, 3, ',', '.'), '0');
    };
@endphp

<table class="items">
    <thead>
        <tr>
            <th class="c" style="width:9mm">#</th>
            <th class="l">Descripción</th>
            <th class="c" style="width:22mm">Cantidad</th>
            <th class="c" style="width:24mm">Unidad</th>
        </tr>
    </thead>
    <tbody>
        @foreach($remito->items as $i => $it)
        <tr>
            <td class="c">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</td>
            <td class="l">{{ $it->descripcion }}</td>
            <td class="c">{{ $fmtCant($it->cantidad) }}</td>
            <td class="c">{{ $it->unidad }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

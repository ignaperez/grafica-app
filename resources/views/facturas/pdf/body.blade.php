@php
    $fmtMoney = fn($v) => number_format((float) $v, 2, ',', '.');
    $fmtCant  = function ($v) {
        $v = (float) $v;
        return $v == floor($v)
            ? number_format($v, 0, ',', '.')
            : rtrim(number_format($v, 3, ',', '.'), '0');
    };
    $umLabel = fn($u) => match($u) { 'm2' => 'm²', 'ml' => 'ml', default => 'unidad' };

    $sumaBase = 0;
    foreach ($desgloseIva as $d) { $sumaBase += $d['base']; }
    $sumaBase = round($sumaBase, 2);
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
            <td class="r">$ {{ $fmtMoney($it->precio_unitario) }}</td>
            <td class="r">$ {{ $fmtMoney($it->subtotal) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- Régimen de Transparencia Fiscal (Ley 27.743) — solo B y C --}}
@if(!$ivaDiscriminado)
<div class="transp">
    <div class="transp-t">Régimen de Transparencia Fiscal al Consumidor (Ley 27.743)</div>
    <div>IVA Contenido: $ {{ $fmtMoney($ivaContenido) }} &nbsp;·&nbsp; Otros Impuestos Nacionales Indirectos: $ 0,00</div>
</div>
@endif

{{-- ── Banda inferior: Observaciones (izq) + Total (der). Cae en la última hoja, arriba del pie. ── --}}
<table class="cierre">
    <tr>
        <td class="cierre-obs">
            <div class="obs-t">Observaciones</div>
            <div>{{ $factura->observaciones ?: '—' }}</div>
        </td>
        <td class="cierre-tot">
            <table style="width:100%;border:none;border-collapse:collapse">
                @if($ivaDiscriminado)
                    <tr><td class="lbl muted">Subtotal neto:</td><td class="val">$ {{ $fmtMoney($sumaBase) }}</td></tr>
                    @foreach($desgloseIva as $d)
                        <tr><td class="lbl muted">IVA {{ rtrim(rtrim(number_format($d['ali'],2,'.',''),'0'),'.') }}%:</td><td class="val">$ {{ $fmtMoney($d['iva']) }}</td></tr>
                    @endforeach
                @endif
                <tr class="tot-final"><td class="lbl">Total (SEUO): ARS</td><td class="val">{{ $fmtMoney($factura->imp_total) }}</td></tr>
            </table>
            <div class="tot-words">Son {{ $montoLetras }}</div>
        </td>
    </tr>
</table>

@php
    $fmtMoney = fn($v) => number_format((float) $v, 2, ',', '.');

    $sumaBase = 0;
    foreach ($desgloseIva as $d) { $sumaBase += $d['base']; }
    $sumaBase = round($sumaBase, 2);
@endphp

{{-- Régimen de Transparencia Fiscal (Ley 27.743) — solo B y C --}}
@if(!$ivaDiscriminado)
<div class="transp">
    <div class="transp-t">Régimen de Transparencia Fiscal al Consumidor (Ley 27.743)</div>
    <div>IVA Contenido: $ {{ $fmtMoney($ivaContenido) }} &nbsp;·&nbsp; Otros Impuestos Nacionales Indirectos: $ 0,00</div>
</div>
@endif

{{-- ── Banda inferior: Observaciones (izq) + Total (der). Posicionada al fondo de la última hoja. ── --}}
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

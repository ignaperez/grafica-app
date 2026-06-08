<table class="pie">
    <tr>
        {{-- QR ARCA --}}
        <td class="pie-qr">
            @if($qrData)
                <img src="{{ $qrData }}" alt="QR ARCA" style="width:20mm;height:20mm">
            @else
                <div class="b" style="color:#c00">Sin<br>CAE</div>
            @endif
        </td>

        {{-- Autorización CAE --}}
        <td class="pie-cae">
            @if($factura->tieneCAE())
                <div class="cae-auth">Comprobante Autorizado</div>
                <div><span class="b">CAE N°:</span> {{ $factura->cae }}</div>
                <div><span class="b">Fecha de Vto. de CAE:</span> {{ $factura->cae_vencimiento?->format('d/m/Y') }}</div>
            @else
                <div class="b" style="color:#c00">Sin CAE — No válido fiscalmente</div>
            @endif
        </td>

        {{-- Código de barras AFIP --}}
        <td class="pie-bc">
            @if($barcode)
                <barcode code="{{ $barcode }}" type="I25" size="0.5" height="0.7" />
                <div class="bc-num">{{ $barcode }}</div>
            @endif
        </td>
    </tr>
</table>

<div class="pie-pag">
    {{ $empresa['nombre'] }}@if($cuitFmt && $cuitFmt !== '—') · CUIT {{ $cuitFmt }}@endif
    &nbsp;&nbsp;&nbsp; Pág. {PAGENO}/{nbpg}
</div>

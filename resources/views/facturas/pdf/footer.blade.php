<table class="pie">
    <tr>
        {{-- QR ARCA (un poco más grande) --}}
        <td class="pie-qr">
            @if($qrData)
                <img src="{{ $qrData }}" alt="QR ARCA" style="width:27mm;height:27mm">
            @else
                <div class="b" style="color:#c00">Sin<br>CAE</div>
            @endif
        </td>

        {{-- Autorización CAE (sin código de barras: el QR ya lo contiene) --}}
        <td class="pie-cae">
            @if($factura->tieneCAE())
                <div class="cae-auth">Comprobante Autorizado</div>
                <div><span class="b">CAE N°:</span> {{ $factura->cae }}</div>
                <div><span class="b">Fecha de Vto. de CAE:</span> {{ $factura->cae_vencimiento?->format('d/m/Y') }}</div>
            @else
                <div class="b" style="color:#c00">Sin CAE — No válido fiscalmente</div>
            @endif
        </td>

        <td class="pie-emp">
            <div>{{ $empresa['nombre'] }}</div>
            @if($cuitFmt && $cuitFmt !== '—')<div>CUIT {{ $cuitFmt }}</div>@endif
            <div class="pie-pag">Pág. {PAGENO}/{nbpg}</div>
        </td>
    </tr>
</table>

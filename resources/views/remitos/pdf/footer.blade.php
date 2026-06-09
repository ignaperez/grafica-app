@if($codTipoFiscal === 'cai' && $cai)
<table style="width:100%;border:none;border-collapse:collapse" class="cai-block">
    <tr>
        <td style="border:none;vertical-align:top">
            <div class="cai-label">CAI</div>
            <div class="cai-code">{{ $cai->codigo }}</div>
            <div class="cai-row"><span class="muted">Vencimiento:</span> <span class="b">{{ $cai->vencimiento->format('d/m/Y') }}</span></div>
            <div class="cai-row"><span class="muted">N° comprobante:</span> {{ $remito->numeroFormateado() }} · Tipo: R (Remito)</div>
        </td>
        @if($codigoBarras)
        <td style="border:none;width:55mm;text-align:right;vertical-align:middle">
            <barcode code="{{ $codigoBarras }}" type="I25" size="0.5" height="0.7" />
        </td>
        @endif
    </tr>
</table>
@elseif($codTipoFiscal === 'electronico')
<table style="width:100%;border:none;border-collapse:collapse" class="cai-block">
    <tr>
        <td style="border:none;vertical-align:top">
            <div class="cai-label">Código de Autorización ARCA</div>
            <div class="cai-code">{{ $remito->cod_autorizacion }}</div>
            @if($remito->cod_autorizacion_vto)
            <div class="cai-row"><span class="muted">Vto. autorización:</span> <span class="b">{{ $remito->cod_autorizacion_vto->format('d/m/Y') }}</span></div>
            @endif
            <div class="cai-row"><span class="muted">N° comprobante:</span> {{ $remito->numeroElectronicoFormateado() }} · Tipo: 91 — Remito R (Electrónico)</div>
        </td>
        @if($codigoBarras)
        <td style="border:none;width:55mm;text-align:right;vertical-align:middle">
            <barcode code="{{ $codigoBarras }}" type="I25" size="0.5" height="0.7" />
        </td>
        @endif
    </tr>
</table>
@endif

<table class="pie">
    <tr>
        <td style="border:none">
            <span class="b">{{ $empresa['nombre'] }}</span>@if($cuitFmt && $cuitFmt !== '—') · CUIT {{ $cuitFmt }}@endif
            @if($empresa['direccion']) · {{ $empresa['direccion'] }}@endif
        </td>
        <td class="pie-pag" style="border:none;width:30mm">Pág. {PAGENO}/{nbpg}</td>
    </tr>
</table>

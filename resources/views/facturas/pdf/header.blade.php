@php
    $emisorIva = match($empresa['condicion_iva'] ?? '') {
        'responsable_inscripto' => 'IVA Responsable Inscripto',
        'monotributo'           => 'Responsable Monotributo',
        'exento'                => 'IVA Exento',
        default                 => '',
    };
    $ventaLabel = 'Contado';
@endphp

<div class="hd-strip">ORIGINAL</div>

<table class="hd">
    <tr>
        {{-- ── Emisor ── --}}
        <td class="hd-left">
            <table style="width:100%;border:none;border-collapse:collapse">
                <tr>
                    @if($logoData)
                    <td style="border:none;width:46px;padding:0 8px 0 0;vertical-align:top">
                        <img src="{{ $logoData }}" class="emp-logo" alt="" style="width:12mm;height:12mm">
                    </td>
                    @endif
                    <td style="border:none;padding:0;vertical-align:top">
                        <div class="emp-name">{{ $empresa['nombre_factura'] }}</div>
                        @if($empresa['direccion'])<div class="emp-row">{{ $empresa['direccion'] }}</div>@endif
                        @if($empresa['telefono'])<div class="emp-row">Cel: {{ $empresa['telefono'] }}</div>@endif
                        @if($empresa['email'])<div class="emp-row">Email: {{ $empresa['email'] }}</div>@endif
                        @if($emisorIva)<div class="emp-row b">{{ $emisorIva }}</div>@endif
                    </td>
                </tr>
            </table>
        </td>

        {{-- ── Letra central ── --}}
        <td class="hd-letra">
            <div class="letra-big">{{ $letra }}</div>
            <div class="letra-cod">COD. {{ $codTipo }}</div>
        </td>

        {{-- ── Datos del comprobante ── --}}
        <td class="hd-right">
            <div class="doc-title">{{ $tipoLabel }}</div>
            <div class="doc-num">{{ $factura->numeroFormateado() }}</div>
            <div class="doc-row"><span class="muted">Fecha de Emisión:</span> <span class="b">{{ $factura->fecha->format('d/m/Y') }}</span></div>
            <div class="doc-row"><span class="muted">CUIT:</span> <span class="b">{{ $cuitFmt }}</span></div>
            @if($empresa['iibb'])<div class="doc-row"><span class="muted">Ingresos Brutos:</span> {{ $empresa['iibb'] }}</div>@endif
            @if($empresa['inicio_actividades'])<div class="doc-row"><span class="muted">Inicio de Actividades:</span> {{ $empresa['inicio_actividades'] }}</div>@endif
        </td>
    </tr>
</table>

{{-- ── Cliente ── --}}
<table class="cli">
    <tr>
        <td style="width:60%">
            <span class="muted">{{ $docTipoLabel }}:</span>
            <span class="b">{{ $factura->doc_nro ?: '—' }}</span>
            &nbsp;&nbsp;
            <span class="muted">Cliente:</span>
            <span class="b">{{ $factura->cliente->nombre ?? '—' }}</span>
        </td>
        <td style="width:40%">
            <span class="muted">Cond. IVA:</span> {{ $condIvaShort }}
            &nbsp;&nbsp;
            <span class="muted">Cond. venta:</span> {{ $ventaLabel }}
        </td>
    </tr>
    @if($factura->cliente && $factura->cliente->direccion)
    <tr>
        <td colspan="2" style="padding-top:0">
            <span class="muted">Domicilio:</span> {{ $factura->cliente->direccion }}
        </td>
    </tr>
    @endif
    <tr>
        <td colspan="2" style="padding-top:0">
            <span class="muted">Concepto:</span> {{ $conceptoLabel }}
        </td>
    </tr>
</table>

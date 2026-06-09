@php
    $emisorIva = match($empresa['condicion_iva'] ?? '') {
        'responsable_inscripto' => 'IVA Responsable Inscripto',
        'monotributo'           => 'Responsable Monotributo',
        'exento'                => 'IVA Exento',
        default                 => '',
    };
    $tipoRemito = match($remito->tipo) {
        'oficial'     => 'Oficial (CAI)',
        'electronico' => 'Electrónico (ARCA)',
        default       => 'Interno',
    };
@endphp

<div class="hd-strip">ORIGINAL</div>

<table class="hd">
    <tr>
        {{-- Emisor --}}
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

        {{-- Letra R --}}
        <td class="hd-letra">
            <div class="letra-big">{{ $letra }}</div>
            <div class="letra-cod">REMITO</div>
        </td>

        {{-- Datos del remito --}}
        <td class="hd-right">
            <div class="doc-title">{{ $tipoLabel }}</div>
            <div class="doc-num">{{ $remito->numeroFormateado() }}</div>
            <div class="doc-row"><span class="muted">Fecha:</span> <span class="b">{{ $remito->fecha->format('d/m/Y') }}</span></div>
            <div class="doc-row"><span class="muted">Tipo:</span> {{ $tipoRemito }}</div>
            @if($cuitFmt && $cuitFmt !== '—')<div class="doc-row"><span class="muted">CUIT:</span> <span class="b">{{ $cuitFmt }}</span></div>@endif
        </td>
    </tr>
</table>

{{-- Destinatario --}}
<table class="cli">
    <tr>
        <td style="width:62%">
            <span class="muted">Destinatario:</span>
            <span class="b">{{ $remito->cliente->nombre ?? '—' }}</span>
            @if($remito->cliente && $remito->cliente->cuit)
                &nbsp;&nbsp;<span class="muted">CUIT:</span> {{ $remito->cliente->cuit }}
            @endif
        </td>
        <td style="width:38%">
            @if($remito->presupuesto)<span class="chip">Presup. {{ $remito->presupuesto->numeroFormateado() }}</span>@endif
            @if($remito->factura)<span class="chip">Factura {{ $remito->factura->numeroFormateado() }}</span>@endif
        </td>
    </tr>
    @if($remito->cliente && $remito->cliente->direccion)
    <tr>
        <td colspan="2" style="padding-top:0">
            <span class="muted">Domicilio:</span> {{ $remito->cliente->direccion }}
        </td>
    </tr>
    @endif
</table>

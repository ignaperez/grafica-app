@php
    $emisorIva = match($empresa['condicion_iva'] ?? '') {
        'responsable_inscripto' => 'IVA Responsable Inscripto',
        'monotributo'           => 'Responsable Monotributo',
        'exento'                => 'IVA Exento',
        default                 => '',
    };
    $ventaLabel = 'Contado';
    $pvFmt   = str_pad((string) $factura->punto_venta, 4, '0', STR_PAD_LEFT);
    $nroFmt  = str_pad((string) $factura->numero,      8, '0', STR_PAD_LEFT);

    // Razón social (legal): si hay nombre de fantasía distinto, el nombre es la razón
    // social; si no, se usa el propietario (para no repetir el título).
    $razonSocial = ($empresa['nombre'] !== $empresa['nombre_factura'])
        ? $empresa['nombre']
        : ($empresa['propietario'] ?? '');

    // Dato variable: regular y en MAYÚSCULA.
    $up = fn($s) => mb_strtoupper((string) $s);
@endphp

<div class="hd-strip">{{ ($preview ?? false) ? 'PREVISUALIZACIÓN — SIN VALOR FISCAL' : 'ORIGINAL' }}</div>

<table class="hd">
    <tr>
        {{-- ── Emisor ── --}}
        <td class="hd-left">
            <table style="width:100%;border:none;border-collapse:collapse">
                <tr>
                    @if($logoData)
                    <td style="border:none;width:62px;padding:0 9px 0 0;vertical-align:top">
                        <img src="{{ $logoData }}" class="emp-logo" alt="" style="width:17mm;height:17mm">
                    </td>
                    @endif
                    <td style="border:none;padding:0;vertical-align:top">
                        <div class="emp-name">{{ $empresa['nombre_factura'] }}</div>
                        @if($razonSocial)<div class="emp-row"><span class="b">Razón Social:</span> {{ $up($razonSocial) }}</div>@endif
                        @if($empresa['direccion'])<div class="emp-row"><span class="b">Domicilio Comercial:</span> {{ $up($empresa['direccion']) }}</div>@endif
                        @if($empresa['telefono'])<div class="emp-row"><span class="b">Teléfono:</span> {{ $empresa['telefono'] }}</div>@endif
                        @if($empresa['email'])<div class="emp-row"><span class="b">Email:</span> {{ $up($empresa['email']) }}</div>@endif
                        @if($emisorIva)<div class="emp-row"><span class="b">Condición frente al IVA:</span> {{ $up($emisorIva) }}</div>@endif
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
            <div class="doc-row"><span class="b">Punto de Venta:</span> {{ $pvFmt }}</div>
            <div class="doc-row"><span class="b">Comp. Nro:</span> {{ ($preview ?? false) ? '????????' : $nroFmt }}</div>
            <div class="doc-row"><span class="b">Fecha de Emisión:</span> {{ $factura->fecha->format('d/m/Y') }}</div>
            <div class="doc-sep"></div>
            <div class="doc-row"><span class="b">CUIT:</span> {{ $cuitFmt }}</div>
            @if($empresa['iibb'])<div class="doc-row"><span class="b">Ingresos Brutos:</span> {{ $empresa['iibb'] }}</div>@endif
            @if($empresa['inicio_actividades'])<div class="doc-row"><span class="b">Inicio de Actividades:</span> {{ $empresa['inicio_actividades'] }}</div>@endif
        </td>
    </tr>
</table>

{{-- ── Cliente ── --}}
<table class="cli">
    <tr>
        <td style="width:60%">
            <span class="b">{{ $docTipoLabel }}:</span> {{ $factura->doc_nro ?: '—' }}
            &nbsp;&nbsp;
            <span class="b">Cliente:</span> {{ $up($factura->cliente->nombre ?? '—') }}
        </td>
        <td style="width:40%">
            <span class="b">Cond. IVA:</span> {{ $condIvaShort }}
            &nbsp;&nbsp;
            <span class="b">Cond. venta:</span> {{ $up($ventaLabel) }}
        </td>
    </tr>
    @if($factura->cliente && $factura->cliente->direccion)
    <tr>
        <td colspan="2" style="padding-top:0">
            <span class="b">Domicilio:</span> {{ $up($factura->cliente->direccion) }}
        </td>
    </tr>
    @endif
    <tr>
        <td colspan="2" style="padding-top:0">
            <span class="b">Concepto:</span> {{ $up($conceptoLabel) }}
        </td>
    </tr>
</table>

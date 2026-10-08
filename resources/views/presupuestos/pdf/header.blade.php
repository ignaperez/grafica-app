{{-- Encabezado: se repite en TODAS las hojas (SetHTMLHeader de mPDF).
     El cuerpo nunca entra acá — el margin_top del documento lo reserva. --}}
@php
    // Las líneas se juntan acá en vez de intercalar @if dentro del texto: una
    // directiva pegada a una letra no se compila (gotcha 19).
    $metaEmpresa = array_filter([
        $owner  ?: null,
        $cuit   ? 'CUIT ' . $cuit : null,
        $empIva ?: null,
        $iibb   ? 'IIBB ' . $iibb : null,
        $inicio ? 'Inicio activ. ' . $inicio : null,
        $dir    ?: null,
        implode(' · ', array_filter([$tel ? 'Tel. ' . $tel : null, $email ?: null])) ?: null,
    ]);
@endphp
<table class="hd">
    <tr>
        {{-- Marca --}}
        @if($logoData)
        <td style="width:18mm;padding-right:5mm">
            <img src="{{ $logoData }}" alt="" style="width:16mm;height:16mm">
        </td>
        @endif
        <td style="width:{{ $logoData ? '52%' : '62%' }}">
            <div class="emp-name">{{ $empresa }}</div>
            <div class="emp-meta">{!! implode('<br>', array_map('e', $metaEmpresa)) !!}</div>
        </td>

        {{-- Documento --}}
        <td class="r">
            <div class="label">Presupuesto</div>
            <div class="doc-num"><span class="doc-dot">&#9679;</span> {{ $presupuesto->numeroFormateado() }}</div>
            <table class="doc-dl" style="margin-left:auto">
                <tr>
                    <td class="dt">Emitido</td>
                    <td class="dd">{{ $presupuesto->fecha->format('d/m/Y') }}</td>
                </tr>
                @if($presupuesto->fecha_vencimiento)
                <tr>
                    <td class="dt">Válido hasta</td>
                    <td class="dd">{{ $presupuesto->fecha_vencimiento->format('d/m/Y') }}</td>
                </tr>
                @endif
            </table>
        </td>
    </tr>
</table>
<div class="hd-rule" style="margin-top:6px"></div>

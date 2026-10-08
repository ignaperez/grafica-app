{{-- Pie: se repite en TODAS las hojas (SetHTMLFooter de mPDF). Liviano a
     propósito — las condiciones van en el cierre, en la última hoja, para no
     comerse el espacio de ítems en cada página. {PAGENO}/{nbpg} los resuelve
     mPDF al cerrar el documento, así que el X/Y es real. --}}
@php
    // Las partes se juntan acá en vez de intercalar @if dentro del texto: una
    // directiva pegada a una letra (`@endif@if`) no se compila. Ver gotcha 19.
    $linea2 = array_filter([
        $tel  ? 'Tel. ' . $tel  : null,
        $cuit ? 'CUIT ' . $cuit : null,
    ]);
@endphp
<table class="pie">
    <tr>
        <td style="width:70%">
            <span class="b" style="color:#41464d">{{ $empresa }}</span>{{ $dir ? ' · ' . $dir : '' }}<br>
            {{ implode(' · ', $linea2) }}
        </td>
        <td class="pie-pag" style="width:30%">
            {{ $presupuesto->numeroFormateado() }} · {{ $presupuesto->fecha->format('d/m/Y') }}<br>
            Pág. {PAGENO}/{nbpg}
        </td>
    </tr>
</table>

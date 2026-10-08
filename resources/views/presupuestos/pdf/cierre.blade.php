{{-- Cierre: total + condiciones. Cae SOLO en la última hoja, anclado al fondo
     (el servicio mide el alto de este bloque y baja el cursor). La caja negra
     con la barra naranja replica el .total-row del print viejo. --}}

<table class="tot">
    <tr>
        <td class="tot-bar">&nbsp;</td>
        <td class="tot-box">
            <span class="tot-lbl">Total</span>&nbsp;&nbsp;&nbsp;&nbsp;
            <span class="tot-val">${{ number_format($presupuesto->total, 2, ',', '.') }}</span>
        </td>
    </tr>
</table>

<div class="cond">
    <div class="cond-t">Condiciones y notas</div>
    <div class="cond-p">{{ $presupuesto->observaciones ?: \App\Models\Presupuesto::CONDICIONES_DEFAULT }}</div>
    <div class="cond-d">
        Emitido el {{ $presupuesto->fecha->format('d/m/Y') }}
        @if($presupuesto->fecha_vencimiento)
            &nbsp;·&nbsp; Válido hasta {{ $presupuesto->fecha_vencimiento->format('d/m/Y') }}
        @endif
    </div>
</div>

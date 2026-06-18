{{-- ── Cierre: Observaciones + "Recibí conforme". Anclado al fondo de la última hoja. ── --}}
<table style="width:100%;border:none;border-collapse:collapse">
    <tr>
        <td style="border:none;width:60%;vertical-align:top">
            <div class="obs-box">
                <div class="obs-t">Observaciones</div>
                <div>{{ $remito->observaciones ?: 'Sin observaciones.' }}</div>
                <div class="obs-meta">
                    Emitido el {{ $remito->fecha->format('d/m/Y') }}
                    @if($remito->tieneAutorizacion())
                        · Comprobante electrónico autorizado por ARCA
                    @elseif($remito->tieneCai())
                        · Comprobante fiscal autorizado — CAI vigente
                    @else
                        · Documento interno — no válido como comprobante fiscal
                    @endif
                </div>
            </div>
        </td>
        <td style="border:none;width:40%;vertical-align:bottom">
            <table style="border:none;margin-left:auto"><tr><td style="border:none">
                <div class="firma">
                    <div class="firma-line"></div>
                    <div class="firma-lbl">Recibí conforme</div>
                    <div class="firma-sub">Firma y aclaración del receptor</div>
                </div>
            </td></tr></table>
        </td>
    </tr>
</table>

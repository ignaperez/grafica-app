@extends('layouts.app')

@section('page-title', 'Nueva Factura')

@section('topbar-actions')
    <a href="{{ route('facturas.index') }}" class="gbtn gbtn-ghost gbtn-sm">← Volver</a>
@endsection

@section('content')

<form method="POST" action="{{ route('facturas.store') }}" id="form-factura">
@csrf

<div style="display:grid;grid-template-columns:1fr 320px;gap:16px;align-items:start">

{{-- ── COLUMNA PRINCIPAL ──────────────────────────────────────────────── --}}
<div>

    {{-- Ítems --}}
    <div class="gcard" style="margin-bottom:16px">
        <div class="gcard-hd">
            <span class="gcard-title">Detalle de ítems</span>
            <button type="button" class="gbtn gbtn-ghost gbtn-sm" id="btn-add-row">+ Agregar ítem</button>
        </div>
        <div class="gcard-bd" style="padding:0">
            <table class="gtable" id="tabla-items">
                <thead>
                    <tr>
                        <th style="width:45%">Descripción</th>
                        <th style="width:15%;text-align:center">Cantidad</th>
                        <th style="width:20%;text-align:right">Precio unitario</th>
                        <th style="width:15%;text-align:right">Subtotal</th>
                        <th style="width:5%"></th>
                    </tr>
                </thead>
                <tbody id="items-body">
                    {{-- filas precargadas desde presupuesto o vacías --}}
                    @if($presupuesto && $presupuesto->items->count())
                        @foreach($presupuesto->items as $i => $item)
                        @php
                            // medidaTotal(): m2→ancho×alto×cant, ml→largo×cant, unidad→cant
                            $cantFac  = old('items.'.$i.'.cantidad',     $item->medidaTotal());
                            $precioFac = old('items.'.$i.'.precio_unitario', $item->precio_unitario);
                            $subFac   = round($cantFac * $precioFac, 2);
                        @endphp
                        <tr class="item-row" data-index="{{ $i }}">
                            <td>
                                <input type="text" name="items[{{ $i }}][descripcion]"
                                    class="ginput ginput-sm"
                                    value="{{ old('items.'.$i.'.descripcion', $item->descripcion) }}"
                                    placeholder="Descripción del servicio" required>
                            </td>
                            <td style="text-align:center">
                                <input type="number" name="items[{{ $i }}][cantidad]"
                                    class="ginput ginput-sm item-cant"
                                    value="{{ $cantFac }}"
                                    min="0.001" step="0.001" required
                                    style="text-align:center;width:80px">
                            </td>
                            <td style="text-align:right">
                                <input type="number" name="items[{{ $i }}][precio_unitario]"
                                    class="ginput ginput-sm item-precio"
                                    value="{{ $precioFac }}"
                                    min="0" step="0.01" required
                                    style="text-align:right;width:110px">
                            </td>
                            <td style="text-align:right">
                                <span class="mono item-subtotal" style="font-size:13px;color:var(--tx)">
                                    ${{ number_format($subFac, 2, ',', '.') }}
                                </span>
                            </td>
                            <td style="text-align:center">
                                <button type="button" class="gbtn gbtn-danger gbtn-xs btn-remove-row">×</button>
                            </td>
                        </tr>
                        @endforeach
                    @else
                        {{-- fila vacía inicial --}}
                        <tr class="item-row" data-index="0">
                            <td>
                                <input type="text" name="items[0][descripcion]"
                                    class="ginput ginput-sm"
                                    value="{{ old('items.0.descripcion') }}"
                                    placeholder="Descripción del servicio" required>
                            </td>
                            <td style="text-align:center">
                                <input type="number" name="items[0][cantidad]"
                                    class="ginput ginput-sm item-cant"
                                    value="{{ old('items.0.cantidad', 1) }}"
                                    min="0.001" step="0.001" required
                                    style="text-align:center;width:80px">
                            </td>
                            <td style="text-align:right">
                                <input type="number" name="items[0][precio_unitario]"
                                    class="ginput ginput-sm item-precio"
                                    value="{{ old('items.0.precio_unitario') }}"
                                    min="0" step="0.01" required
                                    style="text-align:right;width:110px">
                            </td>
                            <td style="text-align:right">
                                <span class="mono item-subtotal" style="font-size:13px;color:var(--tx)">$0,00</span>
                            </td>
                            <td style="text-align:center">
                                <button type="button" class="gbtn gbtn-danger gbtn-xs btn-remove-row">×</button>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        {{-- Totales --}}
        <div style="padding:14px 18px;border-top:1px solid var(--b);display:flex;flex-direction:column;align-items:flex-end;gap:6px">
            <div id="desglose-iva" style="display:none;gap:32px;flex-direction:column;align-items:flex-end">
                <div style="display:flex;gap:32px;color:var(--txd);font-size:13px">
                    <span>Neto gravado</span>
                    <span class="mono" id="lbl-neto" style="min-width:100px;text-align:right">$0,00</span>
                </div>
                <div style="display:flex;gap:32px;color:var(--txd);font-size:13px">
                    <span>IVA 21%</span>
                    <span class="mono" id="lbl-iva" style="min-width:100px;text-align:right">$0,00</span>
                </div>
            </div>
            <div style="display:flex;gap:32px;align-items:center">
                <span style="font-size:12px;color:var(--txd);letter-spacing:1px;text-transform:uppercase">Total</span>
                <span class="mono" id="lbl-total" style="font-size:20px;font-weight:600;color:var(--tx)">$0,00</span>
            </div>
        </div>
    </div>

    {{-- Observaciones --}}
    <div class="gcard">
        <div class="gcard-hd"><span class="gcard-title">Observaciones</span></div>
        <div class="gcard-bd">
            <div class="gfg" style="margin-bottom:0">
                <textarea name="observaciones" class="gtextarea" rows="3"
                    placeholder="Observaciones opcionales...">{{ old('observaciones', $presupuesto?->observaciones) }}</textarea>
            </div>
        </div>
    </div>

</div>

{{-- ── COLUMNA LATERAL ─────────────────────────────────────────────────── --}}
<div style="display:flex;flex-direction:column;gap:16px">

    {{-- Cliente --}}
    <div class="gcard">
        <div class="gcard-hd"><span class="gcard-title">Cliente</span></div>
        <div class="gcard-bd">
            <div class="gfg">
                <label class="glabel">Cliente *</label>
                {{-- Select vacío: Select2 AJAX carga opciones al escribir --}}
                <select name="cliente_id" class="gselect" id="sel-cliente" required>
                    @if($clienteSeleccionado)
                        <option value="{{ $clienteSeleccionado->id }}"
                                data-cuit="{{ $clienteSeleccionado->cuit }}"
                                data-condicion="{{ $clienteSeleccionado->condicion_iva }}"
                                selected>
                            {{ $clienteSeleccionado->nombre }}
                        </option>
                    @endif
                </select>
                <div id="cliente-iva-badge" style="margin-top:6px;font-size:11.5px;min-height:16px;"></div>
                @error('cliente_id')<div class="gerr">{{ $message }}</div>@enderror
            </div>

            <div class="gfg">
                <label class="glabel">Tipo documento receptor</label>
                <select name="doc_tipo" class="gselect" id="sel-doc-tipo">
                    <option value="99" {{ old('doc_tipo', 99) == 99 ? 'selected' : '' }}>Consumidor Final</option>
                    <option value="96" {{ old('doc_tipo') == 96 ? 'selected' : '' }}>DNI</option>
                    <option value="80" {{ old('doc_tipo') == 80 ? 'selected' : '' }}>CUIT</option>
                </select>
            </div>

            <div class="gfg" id="row-doc-nro" style="{{ old('doc_tipo', 99) == 99 ? 'display:none' : '' }}">
                <label class="glabel">N° documento</label>
                <input type="text" name="doc_nro" class="ginput"
                    value="{{ old('doc_nro') }}" placeholder="Sin guiones ni espacios">
                @error('doc_nro')<div class="gerr">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>

    {{-- Tipo de comprobante --}}
    <div class="gcard">
        <div class="gcard-hd"><span class="gcard-title">Comprobante</span></div>
        <div class="gcard-bd">
            <div class="gfg">
                <label class="glabel">Tipo *</label>
                <select name="tipo" class="gselect" id="sel-tipo" required>
                    @foreach($tiposCbte as $id => $label)
                        <option value="{{ $id }}"
                            {{ old('tipo', $tipoCbte) == $id ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('tipo')<div class="gerr">{{ $message }}</div>@enderror
            </div>

            <div class="gfg">
                <label class="glabel">Concepto *</label>
                <select name="concepto" class="gselect" required>
                    <option value="2" {{ old('concepto', 2) == 2 ? 'selected' : '' }}>Servicios</option>
                    <option value="1" {{ old('concepto') == 1 ? 'selected' : '' }}>Productos</option>
                    <option value="3" {{ old('concepto') == 3 ? 'selected' : '' }}>Productos y servicios</option>
                </select>
                @error('concepto')<div class="gerr">{{ $message }}</div>@enderror
            </div>

            {{-- Comprobante original — solo para Notas de Crédito --}}
            <div id="nc-ref-box" style="display:none;border-top:1px solid var(--b);padding-top:14px;margin-top:4px">
                <div class="txd" style="font-size:10px;letter-spacing:1px;text-transform:uppercase;margin-bottom:10px">
                    ⚠ Comprobante original a acreditar
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px">
                    <div class="gfg" style="margin-bottom:0">
                        <label class="glabel">Tipo *</label>
                        <select name="nc_tipo" class="gselect">
                            <option value="11" {{ old('nc_tipo') == 11 ? 'selected' : '' }}>Factura C</option>
                            <option value="6"  {{ old('nc_tipo') == 6  ? 'selected' : '' }}>Factura B</option>
                            <option value="1"  {{ old('nc_tipo') == 1  ? 'selected' : '' }}>Factura A</option>
                        </select>
                        @error('nc_tipo')<div class="gerr">{{ $message }}</div>@enderror
                    </div>
                    <div class="gfg" style="margin-bottom:0">
                        <label class="glabel">Pto. venta *</label>
                        <input type="number" name="nc_pto_vta" class="ginput"
                            value="{{ old('nc_pto_vta', config('arca.punto_venta')) }}"
                            min="1" placeholder="0006">
                        @error('nc_pto_vta')<div class="gerr">{{ $message }}</div>@enderror
                    </div>
                    <div class="gfg" style="margin-bottom:0">
                        <label class="glabel">N° cbte *</label>
                        <input type="number" name="nc_nro" class="ginput"
                            value="{{ old('nc_nro') }}" min="1" placeholder="00000001">
                        @error('nc_nro')<div class="gerr">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            @if($presupuesto)
            <input type="hidden" name="presupuesto_id" value="{{ $presupuesto->id }}">
            <div style="padding:10px 12px;background:#0d0d0d;border:1px solid var(--bm);border-radius:8px;font-size:12px;color:var(--txd);margin-top:12px">
                Basado en presupuesto
                <span class="mono" style="color:var(--ac)">{{ $presupuesto->numeroFormateado() }}</span>
            </div>
            @endif
        </div>
    </div>

    {{-- Aviso ARCA --}}
    <div style="padding:12px 14px;background:#0a1a0e;border:1px solid #163321;border-radius:8px;font-size:12px;color:#5aad76;line-height:1.6">
        <div style="font-weight:600;margin-bottom:4px">⚡ Emisión electrónica</div>
        Al confirmar se solicitará el CAE a ARCA en tiempo real. El comprobante queda registrado fiscalmente de forma inmediata y no puede revertirse.
    </div>

    <button type="button" id="btn-preview" class="gbtn gbtn-ghost" style="width:100%;justify-content:center;padding:11px">
        👁 Vista previa
    </button>

    <button type="button" class="gbtn gbtn-primary" style="width:100%;justify-content:center;padding:12px"
            onclick="confirmarEmision()">
        ⚡ Emitir comprobante
    </button>

</div>
</div>{{-- /grid --}}

</form>

{{-- Modal de confirmación --}}
<div id="modal-confirmar" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.75);z-index:9999;align-items:center;justify-content:center">
    <div style="background:#141414;border:1px solid #2a2a2a;border-radius:12px;padding:32px;max-width:400px;width:90%;text-align:center">
        <div style="font-size:2rem;margin-bottom:12px">⚡</div>
        <div style="font-size:1.05rem;font-weight:600;color:#e8e4dc;margin-bottom:8px">¿Emitir el comprobante?</div>
        <div style="font-size:.85rem;color:#888;margin-bottom:24px;line-height:1.6">
            Se enviará la factura a ARCA y se generará el CAE.<br>
            <strong style="color:#e6502a">Esta acción no se puede deshacer.</strong>
        </div>
        <div style="display:flex;gap:12px;justify-content:center">
            <button type="button" class="gbtn gbtn-ghost" onclick="cerrarModal()">Cancelar</button>
            <button type="button" class="gbtn gbtn-primary" onclick="document.getElementById('form-factura').submit()">
                Sí, emitir
            </button>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
(function () {
    let rowIndex = {{ max(1, $presupuesto?->items->count() ?? 0) }};

    // ── Helpers ──────────────────────────────────────────────────────────
    function fmt(v) {
        return '$' + parseFloat(v || 0).toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function recalcRow($row) {
        const cant  = parseFloat($row.find('.item-cant').val())  || 0;
        const price = parseFloat($row.find('.item-precio').val()) || 0;
        const sub   = Math.round(cant * price * 100) / 100;
        $row.find('.item-subtotal').text(fmt(sub));
        return sub;
    }

    function recalcTotal() {
        let total = 0;
        $('.item-row').each(function () { total += recalcRow($(this)); });

        const tipo = parseInt($('#sel-tipo').val());
        $('#lbl-total').text(fmt(total));

        // Sin IVA: Factura C (11) y Nota de Crédito C (13)
        if (tipo !== 11 && tipo !== 13) {
            const neto = Math.round(total / 1.21 * 100) / 100;
            const iva  = Math.round((total - neto) * 100) / 100;
            $('#lbl-neto').text(fmt(neto));
            $('#lbl-iva').text(fmt(iva));
            $('#desglose-iva').css('display', 'flex');
        } else {
            $('#desglose-iva').css('display', 'none');
        }
    }

    // ── Agregar fila ─────────────────────────────────────────────────────
    $('#btn-add-row').on('click', function () {
        const i = rowIndex++;
        const row = `
        <tr class="item-row" data-index="${i}">
            <td>
                <input type="text" name="items[${i}][descripcion]"
                    class="ginput ginput-sm" placeholder="Descripción del servicio" required>
            </td>
            <td style="text-align:center">
                <input type="number" name="items[${i}][cantidad]"
                    class="ginput ginput-sm item-cant"
                    value="1" min="0.001" step="0.001" required
                    style="text-align:center;width:80px">
            </td>
            <td style="text-align:right">
                <input type="number" name="items[${i}][precio_unitario]"
                    class="ginput ginput-sm item-precio"
                    value="" min="0" step="0.01" required
                    style="text-align:right;width:110px">
            </td>
            <td style="text-align:right">
                <span class="mono item-subtotal" style="font-size:13px;color:var(--tx)">$0,00</span>
            </td>
            <td style="text-align:center">
                <button type="button" class="gbtn gbtn-danger gbtn-xs btn-remove-row">×</button>
            </td>
        </tr>`;
        $('#items-body').append(row);
        $('#items-body tr.item-row:last input[type="text"]').focus();
    });

    // ── Eliminar fila ─────────────────────────────────────────────────────
    $(document).on('click', '.btn-remove-row', function () {
        if ($('.item-row').length === 1) return; // siempre una fila mínimo
        $(this).closest('tr.item-row').remove();
        recalcTotal();
    });

    // ── Recalc al cambiar campos ─────────────────────────────────────────
    $(document).on('input', '.item-cant, .item-precio', function () {
        recalcTotal();
    });

    $('#sel-tipo').on('change', function () {
        recalcTotal();
    });

    // ── Doc tipo → mostrar/ocultar N° doc ───────────────────────────────
    $('#sel-doc-tipo').on('change', function () {
        if ($(this).val() === '99') {
            $('#row-doc-nro').hide();
            $('#row-doc-nro input').val('');
        } else {
            $('#row-doc-nro').show();
        }
    });

    // ── Tipo comprobante → mostrar sección NC si es nota de crédito ──────
    const NC_TIPOS = [3, 8, 13];
    function toggleNC() {
        const tipo = parseInt($('#sel-tipo').val());
        if (NC_TIPOS.includes(tipo)) {
            $('#nc-ref-box').show();
            $('#nc-ref-box input, #nc-ref-box select').attr('required', true);
        } else {
            $('#nc-ref-box').hide();
            $('#nc-ref-box input, #nc-ref-box select').removeAttr('required');
        }
        recalcTotal(); // el desglose IVA también depende del tipo
    }
    $('#sel-tipo').on('change', toggleNC);

    // ── Init ─────────────────────────────────────────────────────────────
    recalcTotal();
    toggleNC();

    // ── Select2 AJAX — búsqueda predictiva de clientes ───────────────────
    $('#sel-cliente').select2({
        ajax: {
            url: '{{ route("clientes.search") }}',
            dataType: 'json',
            delay: 250,
            data:           params => ({ q: params.term }),
            processResults: data   => ({ results: data }),
            cache: true,
        },
        minimumInputLength: 1,
        placeholder:  'Escribí el nombre del cliente...',
        allowClear:   true,
        width:        'resolve',
    });

    // ── Auto-completar doc + tipo según condición IVA del cliente ────────
    const IVA_MAP = {
        responsable_inscripto: { label: 'Resp. Inscripto', color: '#4caf50', tipoRI: '1' },
        monotributo:           { label: 'Monotributo',     color: '#2196f3', tipoRI: '6' },
        exento:                { label: 'Exento',          color: '#ff9800', tipoRI: '6' },
        consumidor_final:      { label: 'Consumidor Final',color: '#888',    tipoRI: '6' },
    };

    function showBadge(condicion) {
        const info = IVA_MAP[condicion] || null;
        $('#cliente-iva-badge').html(info
            ? `<span style="color:${info.color}">● ${info.label}</span>`
            : `<span style="color:var(--txd)">● Sin condición IVA registrada</span>`
        );
    }

    function applyCliente(cuit, condicion) {
        const digits = (cuit || '').replace(/\D/g, '');
        const info   = IVA_MAP[condicion] || null;

        // Badge
        showBadge(condicion);

        // Tipo de comprobante — solo aplica si el EMISOR es Responsable Inscripto
        @if(config('arca.condicion_emisor') === 'responsable_inscripto')
        const NC_TIPOS = [3, 8, 13];
        if (info && !NC_TIPOS.includes(parseInt($('#sel-tipo').val()))) {
            $('#sel-tipo').val(info.tipoRI).trigger('change');
        }
        @endif
        // Si el emisor es Monotributista: siempre Factura C — no tocar el tipo

        // Doc tipo / nro según si el cliente es CF o tiene CUIT identificado
        if (condicion === 'consumidor_final' || (!condicion && digits.length !== 11)) {
            $('#sel-doc-tipo').val('99');
            $('input[name="doc_nro"]').val('');
            $('#row-doc-nro').hide();
        } else if (digits.length === 11) {
            $('#sel-doc-tipo').val('80');
            $('input[name="doc_nro"]').val(digits);
            $('#row-doc-nro').show();
        } else {
            // Tiene condición IVA pero no tiene CUIT cargado
            $('#sel-doc-tipo').val('99');
            $('input[name="doc_nro"]').val('');
            $('#row-doc-nro').hide();
        }
    }

    $('#sel-cliente').on('select2:select', function (e) {
        applyCliente(e.params.data.cuit || '', e.params.data.condicion_iva || '');
    });

    $('#sel-cliente').on('select2:clear', function () {
        $('#cliente-iva-badge').html('');
        $('#sel-doc-tipo').val('99');
        $('input[name="doc_nro"]').val('');
        $('#row-doc-nro').hide();
    });

    // Solo badge, sin tocar doc/tipo (para vuelta de validación con old())
    function showBadge(condicion) {
        const info = IVA_MAP[condicion] || null;
        $('#cliente-iva-badge').html(info
            ? `<span style="color:${info.color}">● ${info.label}</span>`
            : `<span style="color:var(--txd)">● Sin condición IVA registrada</span>`
        );
    }

    // Al cargar la página con cliente preseleccionado (desde presupuesto o old())
    @if($clienteSeleccionado)
        @if(!old('doc_tipo'))
        {{-- Carga limpia desde presupuesto: aplicar todo --}}
        applyCliente('{{ addslashes($clienteSeleccionado->cuit ?? '') }}', '{{ $clienteSeleccionado->condicion_iva ?? '' }}');
        @else
        {{-- Vuelta de validación: doc/tipo ya los restaura old(), solo el badge --}}
        showBadge('{{ $clienteSeleccionado->condicion_iva ?? '' }}');
        @endif
    @endif

    // ── Vista previa — envía el form a /facturas/preview en nueva pestaña ─
    $('#btn-preview').on('click', function () {
        const $form = $('#form-factura');
        $form.attr('action', '{{ route("facturas.preview") }}').attr('target', '_blank');
        $form[0].submit();
        setTimeout(() => {
            $form.attr('action', '{{ route("facturas.store") }}').removeAttr('target');
        }, 300);
    });

})();

// ── Modal confirmación emisión ──────────────────────────────────────────
function confirmarEmision() {
    const modal = document.getElementById('modal-confirmar');
    modal.style.display = 'flex';
}
function cerrarModal() {
    document.getElementById('modal-confirmar').style.display = 'none';
}
// Cerrar con Escape
document.addEventListener('keydown', e => { if (e.key === 'Escape') cerrarModal(); });
// Cerrar al click fuera del panel
document.getElementById('modal-confirmar').addEventListener('click', function(e) {
    if (e.target === this) cerrarModal();
});
</script>

<style>
.ginput-sm { padding: 6px 9px; font-size: 12.5px; border-radius: 6px; }
</style>
@endsection

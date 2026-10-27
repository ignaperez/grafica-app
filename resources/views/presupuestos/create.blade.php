@extends('layouts.app')

@section('page-title', 'Nuevo presupuesto')

@section('topbar-actions')
    <a href="{{ route('presupuestos.index') }}" class="gbtn gbtn-ghost gbtn-sm">← Volver</a>
@endsection

@section('content')

<form method="POST" action="{{ route('presupuestos.store') }}" id="form-presupuesto">
@csrf

{{-- Si se llegó acá agrupando presupuestos, los ids viajan para marcarlos al
     guardar. Se marcan recién al confirmar: si cancela, no pasa nada. --}}
@foreach(($prefill['agrupar_ids'] ?? []) as $pid)
    <input type="hidden" name="agrupar_ids[]" value="{{ $pid }}">
@endforeach

@if(! empty($prefill['agrupar_label']))
    <div class="gcard" style="margin-bottom:14px;border-color:rgba(230,80,42,.35)">
        <div class="gcard-bd" style="font-size:13px;line-height:1.6">
            <strong style="color:var(--ac)">Agrupando {{ $prefill['agrupar_label'] }}</strong><br>
            <span class="txd">
                Los ítems vienen con el precio tal cual fue cotizado. Este presupuesto es
                independiente: edita lo que quieras, no se toca el original. Al guardar, esos
                presupuestos quedan marcados como agrupados y no se podrán facturar por separado.
            </span>
            @if(! empty($prefill['agrupar_viejos']))
                <div style="margin-top:8px;color:#e6a23c">
                    ⚠ Ojo con los precios de {{ implode(' · ', $prefill['agrupar_viejos']) }} —
                    tienen más de 30 días.
                </div>
            @endif
        </div>
    </div>
@endif

{{-- ── Cabecera ───────────────────────────────────────────────────────────── --}}
<div class="gcard" style="margin-bottom:16px">
    <div class="gcard-hd">
        <span class="gcard-title">Datos del presupuesto</span>
    </div>
    <div class="gcard-bd">
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px">

            <div class="gfg" style="grid-column:1/-1">
                <label class="glabel">Cliente *</label>
                <select name="cliente_id" id="select-cliente" class="gselect" required style="width:100%">
                    <option value=""></option>
                    @if(old('cliente_id'))
                        @php $cl = $clientes->find(old('cliente_id')) @endphp
                        @if($cl)
                            <option value="{{ $cl->id }}" selected>{{ $cl->nombre }}</option>
                        @endif
                    @elseif(!empty($prefill['cliente_id']))
                        <option value="{{ $prefill['cliente_id'] }}" selected>{{ $prefill['cliente_nombre'] ?? '' }}</option>
                    @endif
                </select>
                @error('cliente_id')<div class="gerr">{{ $message }}</div>@enderror
            </div>

            <div class="gfg">
                <label class="glabel">Fecha *</label>
                <input type="date" name="fecha" class="ginput"
                       value="{{ old('fecha', now()->format('Y-m-d')) }}" required>
                @error('fecha')<div class="gerr">{{ $message }}</div>@enderror
            </div>

            <div class="gfg">
                <label class="glabel">Vencimiento</label>
                <input type="date" name="fecha_vencimiento" class="ginput"
                       value="{{ old('fecha_vencimiento') }}">
                @error('fecha_vencimiento')<div class="gerr">{{ $message }}</div>@enderror
            </div>

            <div class="gfg">
                {{-- placeholder --}}
            </div>

            <div class="gfg" style="grid-column:1/-1">
                <label class="glabel">Observaciones</label>
                <textarea name="observaciones" class="gtextarea" rows="3"
                          placeholder="Condiciones, notas para el cliente...">{{ old('observaciones', \App\Models\Presupuesto::CONDICIONES_DEFAULT) }}</textarea>
                <div class="txd" style="font-size:11px;margin-top:4px">
                    Texto por defecto. Editalo y se cambia solo en este presupuesto.
                </div>
            </div>

            <div class="gfg" style="grid-column:1/-1">
                <label class="glabel">🔒 Nota interna (privada)</label>
                <textarea name="nota_interna" class="gtextarea" rows="2"
                          placeholder="Solo para uso interno. NO se imprime ni se muestra al cliente.">{{ old('nota_interna') }}</textarea>
                <div class="txd" style="font-size:11px;margin-top:4px">
                    No aparece en el PDF ni la ve el cliente. Para anotaciones tuyas.
                </div>
            </div>

        </div>
    </div>
</div>

{{-- ── Ítems ──────────────────────────────────────────────────────────────── --}}
<div class="gcard" style="margin-bottom:16px">
    <div class="gcard-hd">
        <span class="gcard-title">Ítems del presupuesto</span>
        <button type="button" id="btn-agregar" class="gbtn gbtn-primary gbtn-sm">+ Agregar ítem</button>
    </div>
    <div class="gcard-bd" style="padding:0">
        <table class="gtable" id="tabla-items">
            <thead>
                <tr>
                    <th style="width:280px">Grupo / Servicio</th>
                    <th style="width:200px">Descripción</th>
                    <th style="width:80px">Unidad</th>
                    <th style="width:90px">Medida</th>
                    <th style="width:70px">Cant.</th>
                    <th style="width:110px;text-align:right">P. Unitario</th>
                    <th style="width:110px;text-align:right">Subtotal</th>
                    <th style="width:36px"></th>
                </tr>
            </thead>
            <tbody id="items-body">
                {{-- filas dinámicas --}}
            </tbody>
        </table>
    </div>
    <div class="gcard-ft" style="padding:16px 20px;border-top:1px solid var(--b)">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:24px;flex-wrap:wrap">
            <div style="display:flex;flex-direction:column;gap:10px;font-size:13px">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                    {{-- hidden 0 + checkbox 1: si se destilda, igual llega '0' (si no, el campo no viaja y el server toma el default) --}}
                    <input type="hidden" name="precio_con_iva" value="0">
                    <input type="checkbox" name="precio_con_iva" id="precio-con-iva" value="1" checked
                           style="width:16px;height:16px;accent-color:var(--ac);cursor:pointer">
                    Los precios que cargo <strong>ya incluyen IVA</strong>
                </label>
                <div style="display:flex;align-items:center;gap:8px">
                    <span class="txd">Alícuota IVA</span>
                    <select name="alicuota_iva" id="alicuota-iva" class="gselect" style="width:auto;font-size:12px">
                        <option value="21" selected>21%</option>
                        <option value="10.5">10,5%</option>
                        <option value="27">27%</option>
                        <option value="0">0%</option>
                    </select>
                    <span class="txd" id="iva-modo" style="font-size:11px"></span>
                </div>
            </div>
            <div style="text-align:right;min-width:230px" id="resumen-totales"></div>
        </div>
    </div>
</div>

<div style="text-align:right;margin-bottom:32px">
    <a href="{{ route('presupuestos.index') }}" class="gbtn gbtn-ghost" style="margin-right:8px">Cancelar</a>
    <button type="submit" class="gbtn gbtn-primary">Guardar presupuesto</button>
</div>

</form>

{{-- ── Template de fila (oculto) ─────────────────────────────────────────── --}}
<template id="fila-template">
<tr class="item-row" data-index="__IDX__">
    {{-- Vehículo de origen, si la fila vino precargada desde el módulo de
         vehículos. Al borrar la fila se va con ella y el vehículo no se marca. --}}
    <input type="hidden" class="inp-vehiculo" name="items[__IDX__][vehiculo_id]" value="">
    <td>
        <select class="gselect sel-grupo" style="width:100%;font-size:12px;margin-bottom:5px">
            <option value="">— Grupo —</option>
        </select>
        <select class="gselect sel-item" style="width:100%;font-size:12px" disabled>
            <option value="">— elegí grupo —</option>
        </select>
        <input type="hidden" name="items[__IDX__][maquina_id]"  class="inp-maquina-id">
        <input type="hidden" name="items[__IDX__][material_id]" class="inp-material-id">
    </td>
    <td>
        <input type="text" class="ginput inp-descripcion" name="items[__IDX__][descripcion]"
               placeholder="Descripción..." required style="font-size:12px">
    </td>
    <td>
        <select class="gselect inp-unidad" name="items[__IDX__][unidad]" required style="font-size:12px">
            <option value="m2">m²</option>
            <option value="ml">ml</option>
            <option value="unidad">u</option>
        </select>
    </td>
    <td>
        {{-- m2: ancho × alto --}}
        <div class="wrap-m2">
            <input type="number" step="0.01" min="0" class="ginput inp-ancho" name="items[__IDX__][ancho]"
                   placeholder="Ancho" style="font-size:11px;margin-bottom:4px">
            <input type="number" step="0.01" min="0" class="ginput inp-alto" name="items[__IDX__][alto]"
                   placeholder="Alto" style="font-size:11px">
        </div>
        {{-- ml: largo --}}
        <div class="wrap-ml" style="display:none">
            <input type="number" step="0.01" min="0" class="ginput inp-largo" name="items[__IDX__][largo]"
                   placeholder="Largo" style="font-size:12px">
        </div>
        {{-- unidad: sin medida extra --}}
        <div class="wrap-unidad" style="display:none">
            <span class="txd" style="font-size:11px">—</span>
        </div>
    </td>
    <td>
        <input type="number" min="1" step="1" class="ginput inp-cantidad" name="items[__IDX__][cantidad]"
               value="1" required style="font-size:12px">
    </td>
    <td>
        <input type="number" step="0.01" min="0" class="ginput inp-precio" name="items[__IDX__][precio_unitario]"
               placeholder="0.00" required style="text-align:right;font-size:12px">
    </td>
    <td style="text-align:right">
        <strong class="mono lbl-subtotal" style="font-size:12px">$0.00</strong>
    </td>
    <td>
        <button type="button" class="gbtn gbtn-danger gbtn-xs btn-quitar" title="Quitar">×</button>
    </td>
</tr>
</template>

@endsection

@section('scripts')
<script>
const CATALOGO = {!! json_encode(collect($catalogo)->values()) !!};
// Agrupar el catálogo por "grupo" una sola vez
const GRUPOS = {};
CATALOGO.forEach((item, i) => {
    item._idx = i;                       // índice estable para referenciar
    const g = item.grupo || 'Otros';
    (GRUPOS[g] = GRUPOS[g] || []).push(item);
});
const GRUPOS_ORD = Object.keys(GRUPOS).sort();
let rowIndex = 0;
const PREFILL = @json($prefill['items'] ?? []);   // ítems precargados (ej. desde vehículos)

$(function () {

// ── Select2 para cliente ─────────────────────────────────────────────────
$('#select-cliente').select2({
    placeholder: 'Escribí el nombre del cliente...',
    minimumInputLength: 1,
    allowClear: true,
    width: 'resolve',
    ajax: {
        url: '{{ route("clientes.search") }}',
        dataType: 'json',
        delay: 250,
        data: params => ({ q: params.term }),
        processResults: data => ({ results: data }),
        cache: true,
    }
});

// condición IVA del cliente elegido (para mostrar discriminado o no)
window.clienteCondicion = @json($prefill['cliente_condicion'] ?? '');
$('#select-cliente').on('select2:select', function (e) {
    window.clienteCondicion = e.params.data.condicion_iva || '';
    recalcularTotal();
});

// Cuando cambia el cliente, recalcular todos los precios
$('#select-cliente').on('change', function () {
    const clienteId = $(this).val();
    if (!clienteId) return;
    document.querySelectorAll('.item-row').forEach(row => {
        const maqId = row.querySelector('.inp-maquina-id').value;
        const matId = row.querySelector('.inp-material-id').value;
        if (maqId && matId) fetchPrecio(row, maqId, matId, clienteId);
    });
});

// ── Agregar fila ─────────────────────────────────────────────────────────
document.getElementById('btn-agregar').addEventListener('click', () => agregarFila());

function agregarFila(datos = null) {
    const idx = rowIndex++;
    const tpl = document.getElementById('fila-template').innerHTML
        .replace(/__IDX__/g, idx);

    const tbody = document.getElementById('items-body');
    // insertAdjacentHTML parsea <tr> en el contexto correcto de tabla
    tbody.insertAdjacentHTML('beforeend', tpl);
    const tr = tbody.lastElementChild;

    // Poblar el select de GRUPO (el de ítems se llena al elegir grupo)
    poblarGrupos(tr);

    // Eventos de la fila
    bindFilaEvents(tr);

    // Inicializar visibilidad de medidas para la unidad default (m2)
    toggleMedidas(tr);

    // Si viene con datos prefillados (útil para old())
    if (datos) aplicarDatos(tr, datos);
}

// Llena el primer select (Grupo) y prepara Select2 en ambos
function poblarGrupos(tr) {
    const selGrupo = tr.querySelector('.sel-grupo');
    GRUPOS_ORD.forEach(g => {
        const opt = document.createElement('option');
        opt.value = g;
        opt.textContent = g;
        selGrupo.appendChild(opt);
    });
    $(selGrupo).select2({ width: '100%', placeholder: '— Grupo —' });
    $(tr.querySelector('.sel-item')).select2({ width: '100%', placeholder: '— elegí grupo —' });
}

// Llena el segundo select (Ítem) con los servicios del grupo elegido
function poblarItems(tr, grupo) {
    const selItem = tr.querySelector('.sel-item');
    selItem.innerHTML = '<option value="">— Ítem —</option>';
    (GRUPOS[grupo] || []).forEach(item => {
        const opt = document.createElement('option');
        opt.value = item._idx;
        opt.textContent = item.label;
        selItem.appendChild(opt);
    });
    selItem.disabled = false;
    // reinicializar Select2 para refrescar las opciones
    if ($(selItem).hasClass('select2-hidden-accessible')) $(selItem).select2('destroy');
    $(selItem).select2({ width: '100%', placeholder: '— Ítem —' });
}

// ── Eventos de una fila ──────────────────────────────────────────────────
function bindFilaEvents(tr) {
    const selGrupo   = tr.querySelector('.sel-grupo');
    const selItem    = tr.querySelector('.sel-item');
    const inpDescripcion = tr.querySelector('.inp-descripcion');
    const inpUnidad  = tr.querySelector('.inp-unidad');
    const inpAncho   = tr.querySelector('.inp-ancho');
    const inpAlto    = tr.querySelector('.inp-alto');
    const inpLargo   = tr.querySelector('.inp-largo');
    const inpCantidad = tr.querySelector('.inp-cantidad');
    const inpPrecio  = tr.querySelector('.inp-precio');
    const lblSubtotal = tr.querySelector('.lbl-subtotal');
    const inpMaqId   = tr.querySelector('.inp-maquina-id');
    const inpMatId   = tr.querySelector('.inp-material-id');
    const btnQuitar  = tr.querySelector('.btn-quitar');

    // Elegir GRUPO → cargar sus ítems en el segundo select
    $(selGrupo).on('change', function () {
        if (this.value) poblarItems(tr, this.value);
    });

    // Elegir ÍTEM → autocompletar según la fuente
    $(selItem).on('change', function () {
        const idx = this.value;
        if (idx === '' || idx === null) return;
        const item = CATALOGO[idx];
        if (!item) return;

        inpMaqId.value = item.maquina_id || '';
        inpMatId.value = item.material_id || '';

        // Servicio/paquete: trae descripción completa, unidad y precio (si lo cargaste)
        if (item.fuente === 'producto') {
            inpDescripcion.value = item.descripcion || item.label;
            setUnidad(tr, item.unidad || 'm2');
            if (item.precio !== null && item.precio !== undefined) {
                inpPrecio.value = item.precio;
            }
            recalcularFila(tr);
            return;
        }

        // Combo Máquina × Material: precio CALCULADO (como hasta ahora)
        const clienteId = $('#select-cliente').val();
        if (!clienteId) {
            setUnidad(tr, item.unidad || 'm2');
            if (!inpDescripcion.value) inpDescripcion.value = item.descripcion;
            recalcularFila(tr);
            return;
        }
        fetchPrecio(tr, item.maquina_id, item.material_id, clienteId);
    });

    // Cambio de unidad → mostrar/ocultar campos de medida
    inpUnidad.addEventListener('change', () => toggleMedidas(tr));

    // Recalcular subtotal al cambiar cualquier valor numérico
    [inpAncho, inpAlto, inpLargo, inpCantidad, inpPrecio].forEach(inp => {
        if (inp) inp.addEventListener('input', () => recalcularFila(tr));
    });

    // Quitar fila
    btnQuitar.addEventListener('click', () => {
        tr.remove();
        recalcularTotal();
    });
}

// ── AJAX precio para maquina+material+cliente ────────────────────────────
function fetchPrecio(tr, maquinaId, materialId, clienteId) {
    const inpDescripcion = tr.querySelector('.inp-descripcion');
    fetch(`{{ route("presupuestos.precio-servicio") }}?maquina_id=${maquinaId}&material_id=${materialId}&cliente_id=${clienteId}`)
        .then(r => r.json())
        .then(data => {
            if (data.error) return;
            tr.querySelector('.inp-precio').value = data.precio_unitario;
            setUnidad(tr, data.unidad);
            // Autocompleta descripción si está vacío
            if (!inpDescripcion.value) inpDescripcion.value = data.descripcion;
            recalcularFila(tr);
        })
        .catch(() => {});
}

// ── Mostrar/ocultar campos de medida según unidad ────────────────────────
function setUnidad(tr, unidad) {
    const sel = tr.querySelector('.inp-unidad');
    sel.value = unidad;
    toggleMedidas(tr);
}

function toggleMedidas(tr) {
    const u = tr.querySelector('.inp-unidad').value;
    tr.querySelector('.wrap-m2').style.display    = u === 'm2'     ? '' : 'none';
    tr.querySelector('.wrap-ml').style.display    = u === 'ml'     ? '' : 'none';
    tr.querySelector('.wrap-unidad').style.display = u === 'unidad' ? '' : 'none';

    // required solo en los visibles
    const inpAncho  = tr.querySelector('.inp-ancho');
    const inpAlto   = tr.querySelector('.inp-alto');
    const inpLargo  = tr.querySelector('.inp-largo');
    if (inpAncho) inpAncho.required = (u === 'm2');
    if (inpAlto)  inpAlto.required  = (u === 'm2');
    if (inpLargo) inpLargo.required = (u === 'ml');

    recalcularFila(tr);
}

// ── Cálculo subtotal fila ────────────────────────────────────────────────
function recalcularFila(tr) {
    const u      = tr.querySelector('.inp-unidad').value;
    const cant   = parseFloat(tr.querySelector('.inp-cantidad').value) || 0;
    const precio = parseFloat(tr.querySelector('.inp-precio').value)   || 0;

    let medida = 0;
    if (u === 'm2') {
        const ancho = parseFloat(tr.querySelector('.inp-ancho')?.value) || 0;
        const alto  = parseFloat(tr.querySelector('.inp-alto')?.value)  || 0;
        medida = ancho * alto * cant;
    } else if (u === 'ml') {
        const largo = parseFloat(tr.querySelector('.inp-largo')?.value) || 0;
        medida = largo * cant;
    } else {
        medida = cant;
    }

    const subtotal = medida * precio;
    tr.querySelector('.lbl-subtotal').textContent = '$' + subtotal.toFixed(2);
    recalcularTotal();
}

// ── Total general ────────────────────────────────────────────────────────
function money(n) {
    return '$' + (n || 0).toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function recalcularTotal() {
    let sumTyped = 0;
    document.querySelectorAll('.lbl-subtotal').forEach(lbl => {
        sumTyped += parseFloat(lbl.textContent.replace('$', '')) || 0;
    });

    const conIva = document.getElementById('precio-con-iva').checked;
    const alic   = parseFloat(document.getElementById('alicuota-iva').value) || 0;
    const f      = 1 + alic / 100;

    // precio_unitario / total se guardan SIEMPRE como FINAL
    let total, neto, iva;
    if (conIva) { total = sumTyped;      neto = total / f; iva = total - neto; }
    else        { neto  = sumTyped;      iva  = neto * alic / 100; total = neto + iva; }

    // discrimina = cliente RI o Monotributo
    const disc = ['responsable_inscripto', 'monotributo'].includes(window.clienteCondicion || '');

    document.getElementById('iva-modo').textContent =
        disc ? '→ se discrimina (neto + IVA)' : '→ IVA contenido (no se discrimina)';

    const box = document.getElementById('resumen-totales');
    if (disc) {
        box.innerHTML =
            '<div style="font-size:13px;color:var(--txd);margin-bottom:2px">Subtotal (neto) <span class="mono" style="color:var(--tx);margin-left:10px">' + money(neto) + '</span></div>' +
            '<div style="font-size:13px;color:var(--txd);margin-bottom:6px">IVA (' + (alic % 1 ? alic.toString().replace('.', ',') : alic) + '%) <span class="mono" style="color:var(--tx);margin-left:10px">' + money(iva) + '</span></div>' +
            '<div style="border-top:1px solid var(--bm);padding-top:6px">TOTAL <strong class="mono" style="font-size:1.4rem;margin-left:12px">' + money(total) + '</strong></div>';
    } else {
        box.innerHTML =
            '<div>TOTAL <strong class="mono" style="font-size:1.4rem;margin-left:12px">' + money(total) + '</strong></div>' +
            '<div style="font-size:11px;color:var(--txd);margin-top:4px">IVA contenido (' + (alic % 1 ? alic.toString().replace('.', ',') : alic) + '%): ' + money(iva) + '</div>';
    }

}

document.addEventListener('change', e => {
    if (e.target.id === 'precio-con-iva' || e.target.id === 'alicuota-iva') recalcularTotal();
});

// ── Precargar una fila con datos (ítem libre, sin catálogo) ──────────────
function aplicarDatos(tr, d) {
    if (d.descripcion !== undefined) tr.querySelector('.inp-descripcion').value = d.descripcion;
    if (d.unidad) setUnidad(tr, d.unidad);
    if (d.cantidad !== undefined) tr.querySelector('.inp-cantidad').value = d.cantidad;
    if (d.precio !== undefined)   tr.querySelector('.inp-precio').value   = d.precio;
    const set = (cls, val) => { const el = tr.querySelector(cls); if (el && val !== undefined) el.value = val; };
    set('.inp-ancho', d.ancho); set('.inp-alto', d.alto); set('.inp-largo', d.largo);
    set('.inp-vehiculo', d.vehiculo_id);
    recalcularFila(tr);
}

// ── Filas iniciales: precargadas (ej. desde vehículos) o una vacía ───────
if (Array.isArray(PREFILL) && PREFILL.length) {
    PREFILL.forEach(d => agregarFila(d));
} else {
    agregarFila();
}

// ── Validación antes de enviar ───────────────────────────────────────────
document.getElementById('form-presupuesto').addEventListener('submit', function (e) {
    const filas = document.querySelectorAll('.item-row');
    if (filas.length === 0) {
        e.preventDefault();
        alert('Debe agregar al menos un ítem al presupuesto.');
        return;
    }
    // Verificar que cada fila tenga descripción
    let ok = true;
    filas.forEach(tr => {
        const desc = tr.querySelector('.inp-descripcion').value.trim();
        if (!desc) { ok = false; }
    });
    if (!ok) {
        e.preventDefault();
        alert('Cada ítem debe tener una descripción.');
    }
});

}); // end $(function)
</script>
@endsection

@extends('layouts.app')

@section('page-title', 'Nuevo presupuesto')

@section('topbar-actions')
    <a href="{{ route('presupuestos.index') }}" class="gbtn gbtn-ghost gbtn-sm">← Volver</a>
@endsection

@section('content')

<form method="POST" action="{{ route('presupuestos.store') }}" id="form-presupuesto">
@csrf

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
    <div class="gcard-ft" style="text-align:right;padding:16px 20px;border-top:1px solid var(--b)">
        <span class="txd" style="margin-right:16px">Total estimado</span>
        <strong class="mono" id="total-display" style="font-size:1.4rem">$0.00</strong>
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
function recalcularTotal() {
    let total = 0;
    document.querySelectorAll('.lbl-subtotal').forEach(lbl => {
        total += parseFloat(lbl.textContent.replace('$', '')) || 0;
    });
    document.getElementById('total-display').textContent = '$' + total.toFixed(2);
}

// ── Agregar una fila vacía al cargar ─────────────────────────────────────
agregarFila();

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

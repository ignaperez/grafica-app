@extends('layouts.app')

@section('page-title', 'Editar ' . $presupuesto->numeroFormateado())

@section('topbar-actions')
    <a href="{{ route('presupuestos.show', $presupuesto->id) }}" class="gbtn gbtn-ghost gbtn-sm">← Cancelar</a>
@endsection

@section('content')

<form method="POST" action="{{ route('presupuestos.update', $presupuesto->id) }}" id="form-presupuesto">
@csrf @method('PUT')

{{-- ── Cabecera ───────────────────────────────────────────────────────────── --}}
<div class="gcard" style="margin-bottom:16px">
    <div class="gcard-hd">
        <span class="gcard-title">Datos del presupuesto</span>
        <span class="txd" style="font-size:12px">{{ $presupuesto->numeroFormateado() }}</span>
    </div>
    <div class="gcard-bd">
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px">

            <div class="gfg" style="grid-column:1/-1">
                <label class="glabel">Cliente *</label>
                <select name="cliente_id" id="select-cliente" class="gselect" required style="width:100%">
                    <option value="{{ $presupuesto->cliente_id }}" selected>
                        {{ $presupuesto->cliente->nombre }}
                    </option>
                </select>
                @error('cliente_id')<div class="gerr">{{ $message }}</div>@enderror
            </div>

            <div class="gfg">
                <label class="glabel">Fecha *</label>
                <input type="date" name="fecha" class="ginput"
                       value="{{ old('fecha', $presupuesto->fecha->format('Y-m-d')) }}" required>
                @error('fecha')<div class="gerr">{{ $message }}</div>@enderror
            </div>

            <div class="gfg">
                <label class="glabel">Vencimiento</label>
                <input type="date" name="fecha_vencimiento" class="ginput"
                       value="{{ old('fecha_vencimiento', $presupuesto->fecha_vencimiento?->format('Y-m-d')) }}">
                @error('fecha_vencimiento')<div class="gerr">{{ $message }}</div>@enderror
            </div>

            <div class="gfg">
                {{-- placeholder --}}
            </div>

            <div class="gfg" style="grid-column:1/-1">
                <label class="glabel">Observaciones</label>
                <textarea name="observaciones" class="gtextarea" rows="2"
                          placeholder="Condiciones, notas para el cliente...">{{ old('observaciones', $presupuesto->observaciones) }}</textarea>
            </div>

            <div class="gfg" style="grid-column:1/-1">
                <label class="glabel">🔒 Nota interna (privada)</label>
                <textarea name="nota_interna" class="gtextarea" rows="2"
                          placeholder="Solo para uso interno. NO se imprime ni se muestra al cliente.">{{ old('nota_interna', $presupuesto->nota_interna) }}</textarea>
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
            <tbody id="items-body"></tbody>
        </table>
    </div>
    <div class="gcard-ft" style="padding:16px 20px;border-top:1px solid var(--b)">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:24px;flex-wrap:wrap">
            <div style="display:flex;flex-direction:column;gap:10px;font-size:13px">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                    <input type="checkbox" name="precio_con_iva" id="precio-con-iva" value="1"
                           {{ old('precio_con_iva', $presupuesto->precio_con_iva) ? 'checked' : '' }}
                           style="width:16px;height:16px;accent-color:var(--ac);cursor:pointer">
                    Los precios que cargo <strong>ya incluyen IVA</strong>
                </label>
                <div style="display:flex;align-items:center;gap:8px">
                    <span class="txd">Alícuota IVA</span>
                    @php $aliSel = (string) old('alicuota_iva', rtrim(rtrim(number_format((float) $presupuesto->alicuota(), 2, '.', ''), '0'), '.')); @endphp
                    <select name="alicuota_iva" id="alicuota-iva" class="gselect" style="width:auto;font-size:12px">
                        <option value="21"   {{ $aliSel === '21'   ? 'selected' : '' }}>21%</option>
                        <option value="10.5" {{ $aliSel === '10.5' ? 'selected' : '' }}>10,5%</option>
                        <option value="27"   {{ $aliSel === '27'   ? 'selected' : '' }}>27%</option>
                        <option value="0"    {{ $aliSel === '0'    ? 'selected' : '' }}>0%</option>
                    </select>
                    <span class="txd" id="iva-modo" style="font-size:11px"></span>
                </div>
            </div>
            <div style="text-align:right;min-width:230px" id="resumen-totales"></div>
        </div>
    </div>
</div>

<div style="text-align:right;margin-bottom:32px">
    <a href="{{ route('presupuestos.show', $presupuesto->id) }}" class="gbtn gbtn-ghost" style="margin-right:8px">Cancelar</a>
    <button type="submit" class="gbtn gbtn-primary">Guardar cambios</button>
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
        <div class="wrap-m2">
            <input type="number" step="0.01" min="0" class="ginput inp-ancho" name="items[__IDX__][ancho]"
                   placeholder="Ancho" style="font-size:11px;margin-bottom:4px">
            <input type="number" step="0.01" min="0" class="ginput inp-alto" name="items[__IDX__][alto]"
                   placeholder="Alto" style="font-size:11px">
        </div>
        <div class="wrap-ml" style="display:none">
            <input type="number" step="0.01" min="0" class="ginput inp-largo" name="items[__IDX__][largo]"
                   placeholder="Largo" style="font-size:12px">
        </div>
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
// ── Datos del catálogo y ítems existentes ─────────────────────────────────
const CATALOGO     = {!! json_encode(collect($catalogo)->values()) !!};
// Agrupar el catálogo por "grupo" una sola vez
const GRUPOS = {};
CATALOGO.forEach((item, i) => {
    item._idx = i;
    const g = item.grupo || 'Otros';
    (GRUPOS[g] = GRUPOS[g] || []).push(item);
});
const GRUPOS_ORD = Object.keys(GRUPOS).sort();
const ITEMS_PREVIOS = {!! json_encode($presupuesto->items->map(fn($it) => [
    'maquina_id'      => $it->maquina_id,
    'material_id'     => $it->material_id,
    'descripcion'     => $it->descripcion,
    'unidad'          => $it->unidad,
    'ancho'           => $it->ancho,
    'alto'            => $it->alto,
    'largo'           => $it->largo,
    'cantidad'        => $it->cantidad,
    // Si se cargó con precio NETO, el input muestra el neto (el guardado es final).
    'precio_unitario' => $presupuesto->precio_con_iva
        ? $it->precio_unitario
        : round($it->precio_unitario / (1 + $presupuesto->alicuota() / 100), 2),
])->values()) !!};

let rowIndex = 0;

$(function () {

// condición IVA del cliente (para el modo discriminado del resumen)
window.clienteCondicion = @json($presupuesto->cliente->condicion_iva ?? '');

// ── Select2 para cliente ──────────────────────────────────────────────────
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

$('#select-cliente').on('change', function () {
    const clienteId = $(this).val();
    if (!clienteId) return;
    document.querySelectorAll('.item-row').forEach(row => {
        const maqId = row.querySelector('.inp-maquina-id').value;
        const matId = row.querySelector('.inp-material-id').value;
        if (maqId && matId) fetchPrecio(row, maqId, matId, clienteId);
    });
});

// ── Agregar fila ──────────────────────────────────────────────────────────
document.getElementById('btn-agregar').addEventListener('click', () => agregarFila());

function agregarFila(datos = null) {
    const idx = rowIndex++;
    const tpl = document.getElementById('fila-template').innerHTML
        .replace(/__IDX__/g, idx);

    const tbody = document.getElementById('items-body');
    tbody.insertAdjacentHTML('beforeend', tpl);
    const tr = tbody.lastElementChild;

    poblarGrupos(tr);
    bindFilaEvents(tr);
    toggleMedidas(tr);

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
    if ($(selItem).hasClass('select2-hidden-accessible')) $(selItem).select2('destroy');
    $(selItem).select2({ width: '100%', placeholder: '— Ítem —' });
}

// ── Pre-cargar ítems existentes ───────────────────────────────────────────
function aplicarDatos(tr, datos) {
    // Descripción
    tr.querySelector('.inp-descripcion').value = datos.descripcion ?? '';

    // Unidad y medidas
    setUnidad(tr, datos.unidad ?? 'm2');
    if (datos.ancho)    tr.querySelector('.inp-ancho').value    = datos.ancho;
    if (datos.alto)     tr.querySelector('.inp-alto').value     = datos.alto;
    if (datos.largo)    tr.querySelector('.inp-largo').value    = datos.largo;
    if (datos.cantidad) tr.querySelector('.inp-cantidad').value = datos.cantidad;
    if (datos.precio_unitario) tr.querySelector('.inp-precio').value = datos.precio_unitario;

    // Hidden ids (para que cambiar el cliente recalcule los combos calculados)
    if (datos.maquina_id)  tr.querySelector('.inp-maquina-id').value  = datos.maquina_id;
    if (datos.material_id) tr.querySelector('.inp-material-id').value = datos.material_id;

    // El ítem ya viene con sus datos cargados; los selects Grupo/Ítem quedan
    // sin elegir (solo se usan para volver a traer un servicio del catálogo).
    recalcularFila(tr);
}

// ── Eventos de una fila ───────────────────────────────────────────────────
function bindFilaEvents(tr) {
    const selGrupo   = tr.querySelector('.sel-grupo');
    const selItem    = tr.querySelector('.sel-item');
    const inpDescripcion = tr.querySelector('.inp-descripcion');
    const inpPrecio  = tr.querySelector('.inp-precio');
    const inpMaqId   = tr.querySelector('.inp-maquina-id');
    const inpMatId   = tr.querySelector('.inp-material-id');

    // Elegir GRUPO → cargar sus ítems
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

        // Servicio/paquete: descripción completa + unidad + precio (si lo cargaste)
        if (item.fuente === 'producto') {
            inpDescripcion.value = item.descripcion || item.label;
            setUnidad(tr, item.unidad || 'm2');
            if (item.precio !== null && item.precio !== undefined) {
                inpPrecio.value = item.precio;
            }
            recalcularFila(tr);
            return;
        }

        // Combo Máquina × Material: precio CALCULADO
        const clienteId = $('#select-cliente').val();
        if (!clienteId) {
            setUnidad(tr, item.unidad || 'm2');
            if (!inpDescripcion.value) inpDescripcion.value = item.descripcion;
            recalcularFila(tr);
            return;
        }
        fetchPrecio(tr, item.maquina_id, item.material_id, clienteId);
    });

    tr.querySelector('.inp-unidad').addEventListener('change', () => toggleMedidas(tr));

    [tr.querySelector('.inp-ancho'), tr.querySelector('.inp-alto'),
     tr.querySelector('.inp-largo'), tr.querySelector('.inp-cantidad'),
     tr.querySelector('.inp-precio')].forEach(inp => {
        if (inp) inp.addEventListener('input', () => recalcularFila(tr));
    });

    tr.querySelector('.btn-quitar').addEventListener('click', () => {
        tr.remove();
        recalcularTotal();
    });
}

// ── AJAX precio ───────────────────────────────────────────────────────────
function fetchPrecio(tr, maquinaId, materialId, clienteId) {
    fetch(`{{ route("presupuestos.precio-servicio") }}?maquina_id=${maquinaId}&material_id=${materialId}&cliente_id=${clienteId}`)
        .then(r => r.json())
        .then(data => {
            if (data.error) return;
            tr.querySelector('.inp-precio').value = data.precio_unitario;
            setUnidad(tr, data.unidad);
            const inpDesc = tr.querySelector('.inp-descripcion');
            if (!inpDesc.value) inpDesc.value = data.descripcion;
            recalcularFila(tr);
        }).catch(() => {});
}

function setUnidad(tr, unidad) {
    tr.querySelector('.inp-unidad').value = unidad;
    toggleMedidas(tr);
}

function toggleMedidas(tr) {
    const u = tr.querySelector('.inp-unidad').value;
    tr.querySelector('.wrap-m2').style.display     = u === 'm2'     ? '' : 'none';
    tr.querySelector('.wrap-ml').style.display     = u === 'ml'     ? '' : 'none';
    tr.querySelector('.wrap-unidad').style.display  = u === 'unidad' ? '' : 'none';
    const inpAncho = tr.querySelector('.inp-ancho');
    const inpAlto  = tr.querySelector('.inp-alto');
    const inpLargo = tr.querySelector('.inp-largo');
    if (inpAncho) inpAncho.required = (u === 'm2');
    if (inpAlto)  inpAlto.required  = (u === 'm2');
    if (inpLargo) inpLargo.required = (u === 'ml');
    recalcularFila(tr);
}

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
        medida = (parseFloat(tr.querySelector('.inp-largo')?.value) || 0) * cant;
    } else {
        medida = cant;
    }
    tr.querySelector('.lbl-subtotal').textContent = '$' + (medida * precio).toFixed(2);
    recalcularTotal();
}

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

    let total, neto, iva;
    if (conIva) { total = sumTyped; neto = total / f; iva = total - neto; }
    else        { neto  = sumTyped; iva  = neto * alic / 100; total = neto + iva; }

    const disc = ['responsable_inscripto', 'monotributo'].includes(window.clienteCondicion || '');
    const aliTxt = (alic % 1 ? alic.toString().replace('.', ',') : alic) + '%';

    document.getElementById('iva-modo').textContent =
        disc ? '→ se discrimina (neto + IVA)' : '→ IVA contenido (no se discrimina)';

    const box = document.getElementById('resumen-totales');
    if (disc) {
        box.innerHTML =
            '<div style="font-size:13px;color:var(--txd);margin-bottom:2px">Subtotal (neto) <span class="mono" style="color:var(--tx);margin-left:10px">' + money(neto) + '</span></div>' +
            '<div style="font-size:13px;color:var(--txd);margin-bottom:6px">IVA (' + aliTxt + ') <span class="mono" style="color:var(--tx);margin-left:10px">' + money(iva) + '</span></div>' +
            '<div style="border-top:1px solid var(--bm);padding-top:6px">TOTAL <strong class="mono" style="font-size:1.4rem;margin-left:12px">' + money(total) + '</strong></div>';
    } else {
        box.innerHTML =
            '<div>TOTAL <strong class="mono" style="font-size:1.4rem;margin-left:12px">' + money(total) + '</strong></div>' +
            '<div style="font-size:11px;color:var(--txd);margin-top:4px">IVA contenido (' + aliTxt + '): ' + money(iva) + '</div>';
    }
}

document.addEventListener('change', e => {
    if (e.target.id === 'precio-con-iva' || e.target.id === 'alicuota-iva') recalcularTotal();
});

// ── Cargar ítems previos al iniciar ──────────────────────────────────────
ITEMS_PREVIOS.forEach(datos => agregarFila(datos));
if (ITEMS_PREVIOS.length === 0) agregarFila();

// ── Validación submit ─────────────────────────────────────────────────────
document.getElementById('form-presupuesto').addEventListener('submit', function (e) {
    const filas = document.querySelectorAll('.item-row');
    if (filas.length === 0) {
        e.preventDefault();
        alert('Debe agregar al menos un ítem al presupuesto.');
        return;
    }
    let ok = true;
    filas.forEach(tr => {
        if (!tr.querySelector('.inp-descripcion').value.trim()) ok = false;
    });
    if (!ok) {
        e.preventDefault();
        alert('Cada ítem debe tener una descripción.');
    }
});

}); // end $(function)
</script>
@endsection

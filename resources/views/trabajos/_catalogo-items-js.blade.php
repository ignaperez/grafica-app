<script>
const CATALOGO = {!! json_encode(collect($catalogo)->values()) !!};
const GRUPOS = {};
CATALOGO.forEach((item, i) => { item._idx = i; const g = item.grupo || 'Otros'; (GRUPOS[g] = GRUPOS[g] || []).push(item); });
const GRUPOS_ORD = Object.keys(GRUPOS).sort();
let idxFila = 0;

function calcularM2(fila) {
    const u = fila.querySelector('.inp-unidad').value;
    const cant = parseFloat(fila.querySelector('.inp-cantidad')?.value) || 0;
    let medida = 0;
    if (u === 'm2') medida = (parseFloat(fila.querySelector('.inp-ancho')?.value)||0) * (parseFloat(fila.querySelector('.inp-alto')?.value)||0) * cant;
    else if (u === 'ml') medida = (parseFloat(fila.querySelector('.inp-largo')?.value)||0) * cant;
    else medida = cant;
    const campo = fila.querySelector('.m2-resultado');
    if (campo) campo.value = medida > 0 ? medida.toFixed(2) : '-';
}

function toggleMedidas(fila) {
    const u = fila.querySelector('.inp-unidad').value;
    fila.querySelector('.wrap-m2').style.display     = u === 'm2' ? '' : 'none';
    fila.querySelector('.wrap-ml').style.display     = u === 'ml' ? '' : 'none';
    fila.querySelector('.wrap-unidad').style.display = u === 'unidad' ? '' : 'none';
    calcularM2(fila);
}

function poblarGrupos(fila) {
    const sel = fila.querySelector('.sel-grupo');
    GRUPOS_ORD.forEach(g => { const o = document.createElement('option'); o.value = g; o.textContent = g; sel.appendChild(o); });
    $(sel).select2({ width: '100%', placeholder: '— Grupo —' });
    $(fila.querySelector('.sel-item')).select2({ width: '100%', placeholder: '— elegí grupo —' });
}

function poblarItems(fila, grupo) {
    const sel = fila.querySelector('.sel-item');
    sel.innerHTML = '<option value="">— Ítem —</option>';
    (GRUPOS[grupo] || []).forEach(it => { const o = document.createElement('option'); o.value = it._idx; o.textContent = it.label; sel.appendChild(o); });
    sel.disabled = false;
    if ($(sel).hasClass('select2-hidden-accessible')) $(sel).select2('destroy');
    $(sel).select2({ width: '100%', placeholder: '— Ítem —' });
}

function agregarFilaCat() {
    const idx = idxFila++;
    const html = document.getElementById('tpl-fila').innerHTML.replaceAll('IDX', idx);
    const cont = document.getElementById('contenedor-trabajos');
    cont.insertAdjacentHTML('beforeend', html);
    const fila = cont.lastElementChild;
    fila.querySelector('.num-fila').textContent = idx + 1;

    poblarGrupos(fila);

    $(fila.querySelector('.sel-grupo')).on('change', function () { if (this.value) poblarItems(fila, this.value); });
    $(fila.querySelector('.sel-item')).on('change', function () {
        const it = CATALOGO[this.value];
        if (!it) return;
        fila.querySelector('.inp-tipo-id').value     = it.tipo_trabajo_id || '';
        fila.querySelector('.inp-maquina-id').value  = it.maquina_id || '';
        fila.querySelector('.inp-material-id').value = it.material_id || '';
        if (!fila.querySelector('.inp-descripcion').value) fila.querySelector('.inp-descripcion').value = it.descripcion || it.label;
        fila.querySelector('.inp-unidad').value = it.unidad || 'm2';
        toggleMedidas(fila);
    });

    fila.querySelector('.inp-unidad').addEventListener('change', () => toggleMedidas(fila));
    fila.querySelectorAll('.campo-medida').forEach(i => i.addEventListener('input', () => calcularM2(fila)));
    fila.querySelector('.eliminar-fila').addEventListener('click', () => { fila.remove(); renumerarCat(); });

    toggleMedidas(fila);
}

function renumerarCat() { document.querySelectorAll('.num-fila').forEach((s, i) => s.textContent = i + 1); }

$(function () {
    document.getElementById('btn-agregar').addEventListener('click', agregarFilaCat);
    agregarFilaCat();
});
</script>

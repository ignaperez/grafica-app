{{-- Selector de ítems por catálogo (Grupo → Ítem), SIN precio. Requiere: $catalogo.
     El JS va en trabajos._catalogo-items-js (incluido dentro de @section('scripts')). --}}

<div id="contenedor-trabajos"></div>

<div style="display:flex;gap:10px;margin-top:4px">
    <button type="button" id="btn-agregar" class="gbtn gbtn-ghost">+ Agregar ítem</button>
</div>

<template id="tpl-fila">
<div class="trabajo-fila gcard mb-3">
    <div class="gcard-hd">
        <span class="gcard-title">Ítem <span class="num-fila"></span></span>
        <button type="button" class="gbtn gbtn-danger gbtn-xs eliminar-fila">Quitar</button>
    </div>
    <div class="gcard-bd">
        <div class="row g-3">
            {{-- Grupo → Ítem (catálogo) --}}
            <div class="col-md-6">
                <div class="gfg">
                    <label class="glabel">Servicio (catálogo)</label>
                    <select class="gselect sel-grupo" style="margin-bottom:6px">
                        <option value="">— Grupo —</option>
                    </select>
                    <select class="gselect sel-item" disabled>
                        <option value="">— elegí grupo —</option>
                    </select>
                    <input type="hidden" name="trabajos[IDX][tipo_trabajo_id]" class="inp-tipo-id">
                    <input type="hidden" name="trabajos[IDX][maquina_id]"      class="inp-maquina-id">
                    <input type="hidden" name="trabajos[IDX][material_id]"     class="inp-material-id">
                </div>
            </div>

            {{-- Unidad + medida --}}
            <div class="col-md-2">
                <div class="gfg">
                    <label class="glabel">Unidad</label>
                    <select name="trabajos[IDX][unidad]" class="gselect inp-unidad">
                        <option value="m2">m²</option>
                        <option value="ml">ml</option>
                        <option value="unidad">u</option>
                    </select>
                </div>
            </div>
            <div class="col-md-2">
                <div class="wrap-m2">
                    <div class="gfg" style="margin-bottom:6px"><label class="glabel">Ancho (m)</label>
                        <input type="number" step="0.01" min="0" name="trabajos[IDX][ancho]" class="ginput campo-medida inp-ancho" placeholder="0.00"></div>
                    <div class="gfg" style="margin:0"><label class="glabel">Alto (m)</label>
                        <input type="number" step="0.01" min="0" name="trabajos[IDX][alto]" class="ginput campo-medida inp-alto" placeholder="0.00"></div>
                </div>
                <div class="wrap-ml" style="display:none">
                    <div class="gfg" style="margin:0"><label class="glabel">Largo (m)</label>
                        <input type="number" step="0.01" min="0" name="trabajos[IDX][largo]" class="ginput campo-medida inp-largo" placeholder="0.00"></div>
                </div>
                <div class="wrap-unidad" style="display:none"><span class="txd" style="font-size:11px">Sin medida</span></div>
            </div>
            <div class="col-md-1">
                <div class="gfg"><label class="glabel">Cant. *</label>
                    <input type="number" min="1" value="1" name="trabajos[IDX][cantidad]" class="ginput campo-medida inp-cantidad" required></div>
            </div>
            <div class="col-md-1">
                <div class="gfg"><label class="glabel">m²</label>
                    <input type="text" class="ginput m2-resultado" readonly style="background:#0d0d0d;color:var(--ac)" placeholder="-"></div>
            </div>

            {{-- Descripción + fecha --}}
            <div class="col-md-8">
                <div class="gfg"><label class="glabel">Descripción</label>
                    <input type="text" name="trabajos[IDX][descripcion]" class="ginput inp-descripcion" placeholder="Se completa del catálogo; podés editarla"></div>
            </div>
            <div class="col-md-4">
                <div class="gfg"><label class="glabel">Fecha de entrega</label>
                    <input type="date" name="trabajos[IDX][fecha_entrega]" class="ginput"></div>
            </div>

            <div class="col-12"><div style="border-top:1px solid var(--b);margin:4px 0 12px"></div></div>

            <div class="col-md-6">
                <div class="gfg"><label class="glabel">Archivo para imprimir</label>
                    <input type="file" name="trabajos[IDX][archivos_imprimir][]" class="ginput" multiple
                           accept=".pdf,.ai,.eps,.svg,.psd,.cdr,.indd,.jpg,.jpeg,.png,.tif,.tiff"></div>
            </div>
            <div class="col-md-6">
                <div class="gfg mb-0"><label class="glabel">Referencias</label>
                    <input type="file" name="trabajos[IDX][referencias][]" class="ginput" multiple
                           accept=".jpg,.jpeg,.png,.gif,.bmp,.webp,.tif,.tiff,.pdf,.ai,.eps,.svg"></div>
            </div>
        </div>
    </div>
</div>
</template>

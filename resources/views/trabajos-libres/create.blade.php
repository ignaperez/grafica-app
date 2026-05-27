@extends('layouts.app')

@section('page-title', 'Cargar trabajo(s)')

@section('topbar-actions')
    <a href="{{ route('trabajos-libres.index') }}" class="gbtn gbtn-ghost gbtn-sm">← Volver</a>
@endsection

@section('content')
<style>
    /* Date picker visible sobre fondo oscuro */
    .ginput-date {
        color-scheme: dark;
    }
</style>

    <form id="form-trabajos" action="{{ route('trabajos-libres.store') }}"
          method="POST" enctype="multipart/form-data">
        @csrf

        <div id="contenedor-trabajos"></div>

        <div style="display:flex;gap:10px;margin-top:16px">
            <button type="button" id="btn-agregar" class="gbtn gbtn-ghost">+ Agregar trabajo</button>
            <button type="submit" class="gbtn gbtn-primary">Guardar todos</button>
        </div>
    </form>

{{-- ── Template de fila (oculto, no se renderiza) ────────────── --}}
<template id="tpl-fila">
    <div class="trabajo-fila gcard mb-3">
        <div class="gcard-hd">
            <span class="gcard-title">Trabajo <span class="num-fila"></span></span>
            <button type="button" class="gbtn gbtn-danger gbtn-xs eliminar-fila">Quitar</button>
        </div>
        <div class="gcard-bd">
            <div class="row g-3">

                {{-- Cliente --}}
                <div class="col-md-4">
                    <div class="gfg">
                        <label class="glabel">Cliente *</label>
                        <select name="trabajos[IDX][cliente_id]"
                                class="gselect select2-cliente"
                                style="width:100%" required></select>
                    </div>
                </div>

                {{-- Tipo de trabajo --}}
                <div class="col-md-4">
                    <div class="gfg">
                        <label class="glabel">
                            Tipo de trabajo
                            <a href="{{ route('tipo-trabajos.create') }}" target="_blank"
                               style="font-size:11px;color:var(--ac);margin-left:6px">[+ nuevo]</a>
                        </label>
                        <select name="trabajos[IDX][tipo_trabajo_id]" class="gselect sel-tipo">
                            <option value="">— Sin especificar —</option>
                            @foreach($tipos as $t)
                                <option value="{{ $t->id }}">{{ $t->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Material --}}
                <div class="col-md-4">
                    <div class="gfg">
                        <label class="glabel">
                            Material
                            <a href="{{ route('materiales.create') }}" target="_blank"
                               style="font-size:11px;color:var(--ac);margin-left:6px">[+ nuevo]</a>
                        </label>
                        <select name="trabajos[IDX][material_id]" class="gselect">
                            <option value="">— Sin especificar —</option>
                            @foreach($materiales as $m)
                                <option value="{{ $m->id }}">{{ $m->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Máquina (se llena por JS según tipo de trabajo) --}}
                <div class="col-md-4">
                    <div class="gfg">
                        <label class="glabel">
                            Máquina
                            <a href="{{ route('maquinas.create') }}" target="_blank"
                               style="font-size:11px;color:var(--ac);margin-left:6px">[+ nueva]</a>
                        </label>
                        <select name="trabajos[IDX][maquina_id]" class="gselect sel-maquina">
                            <option value="">— Sin especificar —</option>
                        </select>
                    </div>
                </div>

                {{-- Ancho --}}
                <div class="col-md-2">
                    <div class="gfg">
                        <label class="glabel">Ancho (m)</label>
                        <input type="number" step="0.01" min="0"
                               name="trabajos[IDX][ancho]"
                               class="ginput campo-medida ancho"
                               placeholder="0.00">
                    </div>
                </div>

                {{-- Alto --}}
                <div class="col-md-2">
                    <div class="gfg">
                        <label class="glabel">Alto (m)</label>
                        <input type="number" step="0.01" min="0"
                               name="trabajos[IDX][alto]"
                               class="ginput campo-medida alto"
                               placeholder="0.00">
                    </div>
                </div>

                {{-- Cantidad --}}
                <div class="col-md-2">
                    <div class="gfg">
                        <label class="glabel">Cantidad *</label>
                        <input type="number" min="1" value="1"
                               name="trabajos[IDX][cantidad]"
                               class="ginput campo-medida cantidad" required>
                    </div>
                </div>

                {{-- m² --}}
                <div class="col-md-2">
                    <div class="gfg">
                        <label class="glabel">m² total</label>
                        <input type="text" class="ginput m2-resultado" readonly
                               style="background:#0d0d0d;color:var(--ac)" placeholder="-">
                    </div>
                </div>

                {{-- Fecha entrega --}}
                <div class="col-md-3">
                    <div class="gfg">
                        <label class="glabel">Fecha de entrega</label>
                        <input type="date" name="trabajos[IDX][fecha_entrega]"
                               class="ginput ginput-date">
                    </div>
                </div>

                {{-- Descripción --}}
                <div class="col-md-9">
                    <div class="gfg">
                        <label class="glabel">Descripción</label>
                        <input type="text" name="trabajos[IDX][descripcion]" class="ginput"
                               placeholder="Ej: Vinilo impreso ecosolvente 4 colores">
                    </div>
                </div>

                {{-- Separador archivos --}}
                <div class="col-12">
                    <div style="border-top:1px solid var(--b);margin:4px 0 12px;"></div>
                </div>

                {{-- Archivo para imprimir --}}
                <div class="col-md-6">
                    <div class="gfg">
                        <label class="glabel">Archivo para imprimir</label>
                        <input type="file" name="trabajos[IDX][archivos_imprimir][]"
                               class="ginput" multiple
                               accept=".pdf,.ai,.eps,.svg,.psd,.cdr,.indd,.jpg,.jpeg,.png,.tif,.tiff">
                        <div style="font-size:11px;color:var(--txd);margin-top:4px">
                            PDF · AI · EPS · SVG · PSD · CDR · imágenes — múltiples archivos
                        </div>
                    </div>
                </div>

                {{-- Referencias --}}
                <div class="col-md-6">
                    <div class="gfg">
                        <label class="glabel">Referencias (fotos, bocetos, etc.)</label>
                        <input type="file" name="trabajos[IDX][referencias][]"
                               class="ginput" multiple
                               accept=".jpg,.jpeg,.png,.gif,.bmp,.webp,.tif,.tiff,.pdf,.ai,.eps,.svg">
                        <div style="font-size:11px;color:var(--txd);margin-top:4px">
                            Imágenes · PDF — múltiples archivos
                        </div>
                    </div>
                </div>

                {{-- Observaciones --}}
                <div class="col-12">
                    <div class="gfg mb-0">
                        <label class="glabel">Observaciones</label>
                        <textarea name="trabajos[IDX][observaciones]" class="gtextarea" rows="2"></textarea>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

@endsection

@section('scripts')
<script>
    // ── Datos de máquinas para filtrado client-side ───────────────
    const MAQUINAS = {!! json_encode($maquinas->map(fn($m) => ['id' => $m->id, 'nombre' => $m->nombre, 'tipo_trabajo_id' => $m->tipo_trabajo_id])->values()) !!};

    function filtrarMaquinas(selTipo, selMaquina, valorActual = '') {
        const tipoId = parseInt(selTipo.value) || null;
        const filtradas = tipoId
            ? MAQUINAS.filter(m => !m.tipo_trabajo_id || m.tipo_trabajo_id === tipoId)
            : MAQUINAS;

        selMaquina.innerHTML = '<option value="">— Sin especificar —</option>';
        filtradas.forEach(m => {
            const opt = document.createElement('option');
            opt.value = m.id;
            opt.textContent = m.nombre;
            if (String(m.id) === String(valorActual)) opt.selected = true;
            selMaquina.appendChild(opt);
        });
    }

    let idx = 0;

    function calcularM2(fila) {
        const ancho    = parseFloat(fila.querySelector('.ancho')?.value)  || 0;
        const alto     = parseFloat(fila.querySelector('.alto')?.value)   || 0;
        const cantidad = parseInt(fila.querySelector('.cantidad')?.value) || 0;
        const campo    = fila.querySelector('.m2-resultado');
        if (!campo) return;
        campo.value = (ancho > 0 && alto > 0 && cantidad > 0)
            ? (ancho * alto * cantidad).toFixed(4) + ' m²'
            : '-';
    }

    function agregarFila() {
        const tpl   = document.getElementById('tpl-fila');
        const clone = tpl.content.cloneNode(true);
        const div   = clone.querySelector('.trabajo-fila');

        div.innerHTML = div.innerHTML.replaceAll('IDX', idx);
        div.querySelector('.num-fila').textContent = idx + 1;

        div.querySelector('.eliminar-fila').addEventListener('click', function () {
            const s2 = div.querySelector('.select2-cliente');
            if (s2 && $(s2).data('select2')) $(s2).select2('destroy');
            div.remove();
            renumerarFilas();
        });

        div.querySelectorAll('.campo-medida').forEach(input => {
            input.addEventListener('input', () => calcularM2(div));
        });

        // ── Filtrar máquinas por tipo de trabajo ──────────────────
        const selTipo    = div.querySelector('.sel-tipo');
        const selMaquina = div.querySelector('.sel-maquina');
        filtrarMaquinas(selTipo, selMaquina); // inicializar con todas
        selTipo.addEventListener('change', function () {
            filtrarMaquinas(selTipo, selMaquina);
        });

        document.getElementById('contenedor-trabajos').appendChild(div);

        $(div).find('.select2-cliente').select2({
            width: '100%',
            placeholder: 'Escribí 3 letras para buscar...',
            minimumInputLength: 3,
            language: {
                inputTooShort: () => 'Escribí al menos 3 letras',
                searching:     () => 'Buscando...',
                noResults:     () => 'Sin resultados',
            },
            ajax: {
                url: '{{ route("clientes.search") }}',
                dataType: 'json',
                delay: 250,
                data: params => ({ q: params.term }),
                processResults: data => ({ results: data }),
                cache: true,
            },
        });

        idx++;
    }

    function renumerarFilas() {
        document.querySelectorAll('.num-fila').forEach((span, i) => {
            span.textContent = i + 1;
        });
    }

    document.getElementById('btn-agregar').addEventListener('click', agregarFila);

    document.getElementById('form-trabajos').addEventListener('submit', function (e) {
        if (document.querySelectorAll('.trabajo-fila').length === 0) {
            e.preventDefault();
            alert('Agregá al menos un trabajo antes de guardar.');
        }
    });

    agregarFila();
</script>
@endsection

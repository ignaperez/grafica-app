@extends('layouts.app')

@section('page-title', 'Agregar trabajos — Orden #' . $orden->id)

@section('topbar-actions')
    <a href="{{ route('ordenes-trabajo.show', $orden->id) }}" class="gbtn gbtn-ghost gbtn-sm">
        ← Ver orden #{{ $orden->id }}
    </a>
@endsection

@section('content')
<style>
    input[type="date"].ginput { color-scheme: dark; }
</style>

{{-- Info de la orden --}}
<div class="gcard mb-4">
    <div class="gcard-bd" style="padding:14px 20px">
        <div class="row g-3" style="font-size:13px">
            <div class="col-md-4">
                <div class="txd" style="font-size:10px;letter-spacing:1px;text-transform:uppercase;margin-bottom:2px">Cliente</div>
                <div style="color:var(--tx);font-weight:600">{{ $orden->cliente->nombre ?? '-' }}</div>
            </div>
            <div class="col-md-4">
                <div class="txd" style="font-size:10px;letter-spacing:1px;text-transform:uppercase;margin-bottom:2px">Orden</div>
                <div class="mono" style="color:var(--ac)">#{{ $orden->id }}</div>
            </div>
            @if($orden->observaciones)
            <div class="col-md-4">
                <div class="txd" style="font-size:10px;letter-spacing:1px;text-transform:uppercase;margin-bottom:2px">Observaciones</div>
                <div style="color:var(--tx)">{{ $orden->observaciones }}</div>
            </div>
            @endif
        </div>
    </div>
</div>

<form id="form-trabajos" action="{{ route('trabajos.store-multiples') }}"
      method="POST" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="orden_trabajo_id" value="{{ $orden->id }}">

    <div id="contenedor-trabajos"></div>

    <div style="display:flex;gap:10px;margin-top:16px">
        <button type="button" id="btn-agregar" class="gbtn gbtn-ghost">+ Agregar trabajo</button>
        <button type="submit" class="gbtn gbtn-primary">Guardar todos</button>
    </div>
</form>

{{-- ── Template de fila ──────────────────────────────────────── --}}
<template id="tpl-fila">
    <div class="trabajo-fila gcard mb-3">
        <div class="gcard-hd">
            <span class="gcard-title">Trabajo <span class="num-fila"></span></span>
            <button type="button" class="gbtn gbtn-danger gbtn-xs eliminar-fila">Quitar</button>
        </div>
        <div class="gcard-bd">
            <div class="row g-3">

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

                {{-- Máquina (se filtra por tipo de trabajo) --}}
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
                <div class="col-md-4">
                    <div class="gfg">
                        <label class="glabel">Fecha de entrega</label>
                        <input type="date" name="trabajos[IDX][fecha_entrega]" class="ginput">
                    </div>
                </div>

                {{-- Descripción --}}
                <div class="col-12">
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
                    <div class="gfg mb-0">
                        <label class="glabel">Referencias (fotos, bocetos)</label>
                        <input type="file" name="trabajos[IDX][referencias][]"
                               class="ginput" multiple
                               accept=".jpg,.jpeg,.png,.gif,.bmp,.webp,.tif,.tiff,.pdf,.ai,.eps,.svg">
                        <div style="font-size:11px;color:var(--txd);margin-top:4px">
                            Imágenes · PDF — múltiples archivos
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

@endsection

@section('scripts')
<script>
    const MAQUINAS = @json($maquinas->map(fn($m) => [
        'id'              => $m->id,
        'nombre'          => $m->nombre,
        'tipo_trabajo_id' => $m->tipo_trabajo_id,
    ]));

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
            div.remove();
            renumerarFilas();
        });

        div.querySelectorAll('.campo-medida').forEach(input => {
            input.addEventListener('input', () => calcularM2(div));
        });

        // Filtrar máquinas por tipo de trabajo
        const selTipo    = div.querySelector('.sel-tipo');
        const selMaquina = div.querySelector('.sel-maquina');
        filtrarMaquinas(selTipo, selMaquina);
        selTipo.addEventListener('change', function () {
            filtrarMaquinas(selTipo, selMaquina);
        });

        document.getElementById('contenedor-trabajos').appendChild(div);
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

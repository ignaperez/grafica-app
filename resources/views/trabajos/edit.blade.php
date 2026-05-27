@extends('layouts.app')

@section('page-title', 'Editar Trabajo #' . $trabajo->id)

@section('topbar-actions')
    @if($trabajo->orden)
        <a href="{{ route('ordenes-trabajo.show', $trabajo->orden->id) }}" class="gbtn gbtn-ghost gbtn-sm">
            ← Orden #{{ $trabajo->orden->id }}
        </a>
    @else
        <a href="{{ route('trabajos-libres.index') }}" class="gbtn gbtn-ghost gbtn-sm">← Trabajos</a>
    @endif
@endsection

@section('content')

{{-- ─── Datos del trabajo ─────────────────────────────────────── --}}
<div class="gcard mb-4">
    <div class="gcard-hd">
        <span class="gcard-title">Datos del trabajo</span>
        <span class="txd mono" style="font-size:12px">#{{ $trabajo->id }}</span>
    </div>
    <div class="gcard-bd">

        <form action="{{ route('trabajos.update', $trabajo->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3">

                {{-- Cliente --}}
                <div class="col-md-4">
                    <div class="gfg">
                        <label class="glabel">Cliente *</label>
                        <select name="cliente_id" class="gselect select2-cliente" style="width:100%" required>
                            @if($trabajo->cliente)
                                <option value="{{ $trabajo->cliente->id }}" selected>
                                    {{ $trabajo->cliente->nombre }}
                                </option>
                            @endif
                        </select>
                    </div>
                </div>

                {{-- Tipo de trabajo --}}
                <div class="col-md-4">
                    <div class="gfg">
                        <label class="glabel">Tipo de trabajo</label>
                        <select name="tipo_trabajo_id" id="sel-tipo" class="gselect">
                            <option value="">— Sin especificar —</option>
                            @foreach($tipos as $t)
                                <option value="{{ $t->id }}"
                                    {{ $trabajo->tipo_trabajo_id == $t->id ? 'selected' : '' }}>
                                    {{ $t->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Material --}}
                <div class="col-md-4">
                    <div class="gfg">
                        <label class="glabel">Material</label>
                        <select name="material_id" class="gselect">
                            <option value="">— Sin especificar —</option>
                            @foreach($materiales as $m)
                                <option value="{{ $m->id }}"
                                    {{ $trabajo->material_id == $m->id ? 'selected' : '' }}>
                                    {{ $m->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Máquina --}}
                <div class="col-md-4">
                    <div class="gfg">
                        <label class="glabel">Máquina</label>
                        <select name="maquina_id" id="sel-maquina" class="gselect">
                            <option value="">— Sin especificar —</option>
                        </select>
                    </div>
                </div>

                {{-- Ancho --}}
                <div class="col-md-2">
                    <div class="gfg">
                        <label class="glabel">Ancho (m)</label>
                        <input type="number" step="0.01" min="0" name="ancho"
                               class="ginput campo-medida" id="ancho"
                               value="{{ old('ancho', $trabajo->ancho) }}">
                    </div>
                </div>

                {{-- Alto --}}
                <div class="col-md-2">
                    <div class="gfg">
                        <label class="glabel">Alto (m)</label>
                        <input type="number" step="0.01" min="0" name="alto"
                               class="ginput campo-medida" id="alto"
                               value="{{ old('alto', $trabajo->alto) }}">
                    </div>
                </div>

                {{-- Cantidad --}}
                <div class="col-md-2">
                    <div class="gfg">
                        <label class="glabel">Cantidad</label>
                        <input type="number" min="1" name="cantidad"
                               class="ginput campo-medida" id="cantidad"
                               value="{{ old('cantidad', $trabajo->cantidad) }}">
                    </div>
                </div>

                {{-- m² --}}
                <div class="col-md-2">
                    <div class="gfg">
                        <label class="glabel">m² total</label>
                        <input type="text" id="m2-resultado" class="ginput" readonly
                               style="background:#0d0d0d;color:var(--ac)"
                               value="{{ $trabajo->ancho && $trabajo->alto ? number_format($trabajo->ancho * $trabajo->alto * $trabajo->cantidad, 4) . ' m²' : '-' }}">
                    </div>
                </div>

                {{-- Fecha entrega --}}
                <div class="col-md-3">
                    <div class="gfg">
                        <label class="glabel">Fecha de entrega</label>
                        <input type="date" name="fecha_entrega" class="ginput"
                               value="{{ old('fecha_entrega', $trabajo->fecha_entrega?->format('Y-m-d')) }}">
                    </div>
                </div>

                {{-- Descripción --}}
                <div class="col-md-9">
                    <div class="gfg">
                        <label class="glabel">Descripción</label>
                        <input type="text" name="descripcion" class="ginput"
                               value="{{ old('descripcion', $trabajo->descripcion) }}"
                               placeholder="Ej: Vinilo impreso ecosolvente 4 colores">
                    </div>
                </div>

            </div>

            <div class="mt-3">
                <button type="submit" class="gbtn gbtn-primary">Guardar cambios</button>
            </div>
        </form>

    </div>
</div>

{{-- ─── Archivos adjuntos ──────────────────────────────────────── --}}
<div class="row g-4">

    {{-- Para imprimir --}}
    <div class="col-md-6">
        <div class="gcard">
            <div class="gcard-hd">
                <span class="gcard-title">Archivos para imprimir</span>
                <span class="txd" style="font-size:11px">PDF · AI · EPS · SVG · PSD · CDR</span>
            </div>
            <div class="gcard-bd">

                @include('trabajos.partials.lista-archivos', [
                    'archivos' => $trabajo->archivosImprimir,
                    'tipo'     => 'imprimir',
                ])

                <form action="{{ route('trabajo-archivos.store', $trabajo->id) }}"
                      method="POST" enctype="multipart/form-data" class="mt-3">
                    @csrf
                    <input type="hidden" name="tipo" value="imprimir">
                    <div class="gfg mb-2">
                        <label class="glabel">Agregar archivo(s)</label>
                        <input type="file" name="archivos[]" class="ginput" multiple
                               accept=".pdf,.ai,.eps,.svg,.psd,.cdr,.indd,.jpg,.jpeg,.png,.tif,.tiff">
                    </div>
                    <button type="submit" class="gbtn gbtn-ghost gbtn-sm">Subir</button>
                </form>

            </div>
        </div>
    </div>

    {{-- Referencias --}}
    <div class="col-md-6">
        <div class="gcard">
            <div class="gcard-hd">
                <span class="gcard-title">Referencias</span>
                <span class="txd" style="font-size:11px">JPG · PNG · PDF · cualquier imagen</span>
            </div>
            <div class="gcard-bd">

                @include('trabajos.partials.lista-archivos', [
                    'archivos' => $trabajo->referencias,
                    'tipo'     => 'referencia',
                ])

                <form action="{{ route('trabajo-archivos.store', $trabajo->id) }}"
                      method="POST" enctype="multipart/form-data" class="mt-3">
                    @csrf
                    <input type="hidden" name="tipo" value="referencia">
                    <div class="gfg mb-2">
                        <label class="glabel">Agregar referencia(s)</label>
                        <input type="file" name="archivos[]" class="ginput" multiple
                               accept=".jpg,.jpeg,.png,.gif,.bmp,.webp,.tif,.tiff,.pdf,.ai,.eps,.svg">
                    </div>
                    <button type="submit" class="gbtn gbtn-ghost gbtn-sm">Subir</button>
                </form>

            </div>
        </div>
    </div>

</div>

@endsection

@section('scripts')
<script>
    // ── Datos de máquinas (para filtrar por tipo) ─────────────────
    const MAQUINAS = {!! json_encode($maquinas->map(fn($m) => ['id' => $m->id, 'nombre' => $m->nombre, 'tipo_trabajo_id' => $m->tipo_trabajo_id])->values()) !!};

    function filtrarMaquinas(tipoId, valorActual) {
        const sel = document.getElementById('sel-maquina');
        const filtradas = tipoId
            ? MAQUINAS.filter(m => !m.tipo_trabajo_id || m.tipo_trabajo_id == tipoId)
            : MAQUINAS;

        sel.innerHTML = '<option value="">— Sin especificar —</option>';
        filtradas.forEach(m => {
            const opt = document.createElement('option');
            opt.value = m.id;
            opt.textContent = m.nombre;
            if (String(m.id) === String(valorActual)) opt.selected = true;
            sel.appendChild(opt);
        });
    }

    // Inicializar al cargar: filtrar según tipo actual, mantener máquina actual
    const selTipo = document.getElementById('sel-tipo');
    filtrarMaquinas(selTipo.value, '{{ $trabajo->maquina_id }}');

    selTipo.addEventListener('change', function () {
        filtrarMaquinas(this.value, '');
    });

    // ── Calcular m² ───────────────────────────────────────────────
    function calcularM2() {
        const ancho    = parseFloat(document.getElementById('ancho')?.value)    || 0;
        const alto     = parseFloat(document.getElementById('alto')?.value)     || 0;
        const cantidad = parseInt(document.getElementById('cantidad')?.value)   || 0;
        const campo    = document.getElementById('m2-resultado');
        campo.value = (ancho > 0 && alto > 0 && cantidad > 0)
            ? (ancho * alto * cantidad).toFixed(4) + ' m²'
            : '-';
    }
    document.querySelectorAll('.campo-medida').forEach(el => {
        el.addEventListener('input', calcularM2);
    });

    // Select2 AJAX para cliente
    $('.select2-cliente').select2({
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
</script>
@endsection

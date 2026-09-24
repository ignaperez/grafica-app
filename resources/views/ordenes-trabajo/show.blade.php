@extends('layouts.app')

@section('page-title', 'Orden #' . $orden->id)

@section('topbar-actions')
    <div style="display:flex;gap:8px;align-items:center">
        <a href="{{ route('ordenes-trabajo.print', $orden->id) }}" target="_blank"
           class="gbtn gbtn-ghost gbtn-sm">🖨 Imprimir</a>

        @if(auth()->user()->puedeModulo('presupuestos') && $orden->trabajos->count())
        <form method="POST" action="{{ route('presupuestos.desde-trabajos') }}" style="display:inline"
              onsubmit="return confirm('¿Crear un presupuesto con los trabajos de esta orden?')">
            @csrf
            <input type="hidden" name="orden_id" value="{{ $orden->id }}">
            <button class="gbtn gbtn-primary gbtn-sm" title="Presupuestar esta orden">⚡ Presupuestar</button>
        </form>
        @endif

        {{-- Cambiar estado --}}
        <form method="POST" action="{{ route('ordenes-trabajo.estado', $orden->id) }}"
              style="display:flex;gap:6px;align-items:center">
            @csrf @method('PATCH')
            <select name="estado" class="gselect" style="width:160px;padding:5px 10px;font-size:12px"
                    onchange="this.form.submit()">
                @foreach(['borrador','en_produccion','lista','entregada','cancelada'] as $e)
                    <option value="{{ $e }}" {{ $orden->estado === $e ? 'selected' : '' }}>
                        {{ ucfirst(str_replace('_',' ',$e)) }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>
@endsection

@section('content')

{{-- ── Barra de progreso ─────────────────────────────────── --}}
@if($total > 0)
<div class="gcard mb-4">
    <div class="gcard-bd" style="padding:16px 20px">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
            <span style="font-size:13px;color:var(--tx)">Progreso de la orden</span>
            <span style="font-size:13px;font-family:var(--mono);color:{{ $porcentaje == 100 ? '#4caf6e' : 'var(--ac)' }}">
                {{ $terminados }}/{{ $total }} trabajos &nbsp;·&nbsp; {{ $porcentaje }}%
            </span>
        </div>
        <div class="gprog">
            <div class="gprog-fill {{ $porcentaje == 100 ? 'done' : '' }}"
                 style="width:{{ $porcentaje }}%;transition:width .4s ease"></div>
        </div>
    </div>
</div>
@endif

{{-- ── Cabecera de la orden ──────────────────────────────── --}}
<div class="gcard mb-4">
    <div class="gcard-hd">
        <span class="gcard-title">Orden #{{ $orden->id }}</span>
        <div style="display:flex;align-items:center;gap:8px">
            <span class="badge-estado be-{{ $orden->estado }}">{{ ucfirst(str_replace('_',' ',$orden->estado)) }}</span>
            <button type="button" class="gbtn gbtn-ghost gbtn-xs" id="btn-toggle-edit"
                    onclick="toggleEditOrden()">✎ Editar</button>
        </div>
    </div>
    <div class="gcard-bd">
        {{-- Vista de datos --}}
        <div id="vista-datos" class="row g-3" style="font-size:13.5px">
            <div class="col-md-3">
                <div class="txd" style="font-size:10px;letter-spacing:1px;text-transform:uppercase;margin-bottom:3px">Cliente</div>
                <div style="color:var(--tx);font-weight:500">{{ $orden->cliente->nombre ?? '-' }}</div>
            </div>
            <div class="col-md-3">
                <div class="txd" style="font-size:10px;letter-spacing:1px;text-transform:uppercase;margin-bottom:3px">Fecha de ingreso</div>
                <div style="color:var(--tx)">
                    {{ $orden->fecha_recibido ? \Carbon\Carbon::parse($orden->fecha_recibido)->format('d/m/Y') : '-' }}
                </div>
            </div>
            <div class="col-md-6">
                <div class="txd" style="font-size:10px;letter-spacing:1px;text-transform:uppercase;margin-bottom:3px">Observaciones</div>
                <div style="color:var(--tx)">{{ $orden->observaciones ?: '—' }}</div>
            </div>
        </div>

        {{-- Form de edición (oculto por defecto) --}}
        <form id="form-edit-orden" method="POST"
              action="{{ route('ordenes-trabajo.metadata', $orden->id) }}"
              style="display:none;margin-top:16px">
            @csrf @method('PATCH')
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="gfg mb-0">
                        <label class="glabel">Cliente *</label>
                        {{-- Select vacío salvo el actual: Select2 busca por AJAX al escribir --}}
                        <select name="cliente_id" class="gselect" id="sel-cliente-orden" required>
                            @if($orden->cliente_id)
                                <option value="{{ $orden->cliente_id }}" selected>{{ $orden->cliente->nombre }}</option>
                            @endif
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="gfg mb-0">
                        <label class="glabel">Fecha de ingreso</label>
                        <input type="date" name="fecha_recibido" class="ginput"
                               value="{{ $orden->fecha_recibido ? \Carbon\Carbon::parse($orden->fecha_recibido)->format('Y-m-d') : '' }}">
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="gfg mb-0">
                        <label class="glabel">Observaciones / Título del trabajo</label>
                        <input type="text" name="observaciones" class="ginput"
                               value="{{ old('observaciones', $orden->observaciones) }}"
                               placeholder="Ej: Ploteo edificio Swiss Medical — Planta baja">
                    </div>
                </div>
                <div class="col-12" style="display:flex;gap:8px">
                    <button type="submit" class="gbtn gbtn-primary gbtn-sm">Guardar</button>
                    <button type="button" class="gbtn gbtn-ghost gbtn-sm" onclick="toggleEditOrden()">Cancelar</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ── Trabajos ──────────────────────────────────────────── --}}
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
    <span style="font-size:11px;letter-spacing:2px;text-transform:uppercase;color:#444">
        Trabajos ({{ $total }})
    </span>
    <div style="display:flex;gap:8px">
        <a href="{{ route('trabajos.create-para-orden', $orden->id) }}" class="gbtn gbtn-ghost gbtn-sm">
            + Cargar trabajo
        </a>
        <a href="{{ route('vehiculos-ploteo.create', ['orden_id' => $orden->id]) }}" class="gbtn gbtn-ghost gbtn-sm">
            🚗 Vehículo
        </a>
    </div>
</div>

@forelse($orden->trabajos as $i => $t)
<div class="gcard mb-3" id="trabajo-{{ $t->id }}">
    <div class="gcard-hd">
        <div style="display:flex;align-items:center;gap:10px">
            <span class="mono txd" style="font-size:11px">#{{ $t->id }}</span>
            <span style="font-size:13px;font-weight:500;color:var(--tx)">
                {{ $t->descripcion ?? ('Trabajo ' . ($i + 1)) }}
            </span>
        </div>
        <div style="display:flex;align-items:center;gap:8px">
            <span class="badge-estado be-{{ $t->estado }}">
                {{ ucfirst(str_replace('_',' ',$t->estado)) }}
            </span>
            @if($t->estado !== 'terminado')
                <form method="POST" action="{{ route('trabajos.terminar', $t->id) }}" style="margin:0">
                    @csrf
                    <button type="submit" class="gbtn gbtn-ghost gbtn-xs"
                            onclick="return confirm('¿Marcar como terminado?')">
                        ✓ Terminado
                    </button>
                </form>
            @else
                <form method="POST" action="{{ route('trabajos.estado', $t->id) }}" style="margin:0">
                    @csrf @method('PATCH')
                    <input type="hidden" name="estado" value="pendiente">
                    <button type="submit" class="gbtn gbtn-ghost gbtn-xs"
                            style="color:#e05555"
                            onclick="return confirm('¿Revertir a Pendiente?')">
                        ↩ Revertir
                    </button>
                </form>
            @endif
            <a href="{{ route('trabajos.edit', $t->id) }}" class="gbtn gbtn-ghost gbtn-xs">Editar</a>
        </div>
    </div>
    <div class="gcard-bd">
        <div class="row g-3">

            {{-- Datos del trabajo --}}
            <div class="col-12">
                <div class="row g-3" style="font-size:13px">

                    @php
                        $datos = [];
                        if ($t->tipoTrabajo)  $datos['Tipo de trabajo'] = $t->tipoTrabajo->nombre;
                        if ($t->material)     $datos['Material']        = $t->material->nombre;
                        if ($t->maquina)      $datos['Máquina']         = $t->maquina->nombre;
                        if ($t->producto)     $datos['Servicio']        = $t->producto->nombre;
                        $datos['Unidad']   = $t->unidadLabel();
                        if ($m = $t->medidaUnitariaTexto()) $datos['Medidas'] = $m;
                        if ($t->medidas)      $datos['Medidas (texto)'] = $t->medidas;
                        $datos['Cantidad'] = $t->cantidad;
                        if ($t->fecha_carga)   $datos['Cargado']  = $t->fecha_carga->format('d/m/Y H:i');
                        if ($t->fecha_entrega) $datos['Entrega']  = $t->fecha_entrega->format('d/m/Y');
                        // El cliente de la orden ya está arriba: solo se repite si difiere.
                        if ($t->cliente_id && $t->cliente_id !== $orden->cliente_id)
                            $datos['Cliente'] = $t->cliente->nombre ?? '-';
                    @endphp

                    @foreach($datos as $etiqueta => $valor)
                        <div class="col-6 col-sm-4 col-lg-3">
                            <div class="txd" style="font-size:10px;letter-spacing:1px;text-transform:uppercase;margin-bottom:2px">{{ $etiqueta }}</div>
                            <div style="color:var(--tx);word-break:break-word">{{ $valor }}</div>
                        </div>
                    @endforeach

                    <div class="col-6 col-sm-4 col-lg-3">
                        <div class="txd" style="font-size:10px;letter-spacing:1px;text-transform:uppercase;margin-bottom:2px">Total</div>
                        <div style="color:var(--ac);font-family:var(--mono);font-weight:600">{{ $t->medidaTotalTexto() }}</div>
                    </div>

                </div>

                @if(trim((string) $t->descripcion) !== '')
                <div style="margin-top:14px">
                    <div class="txd" style="font-size:10px;letter-spacing:1px;text-transform:uppercase;margin-bottom:4px">Descripción</div>
                    <div style="color:var(--tx);font-size:13px;line-height:1.55;white-space:pre-line">{{ $t->descripcion }}</div>
                </div>
                @endif
            </div>

            {{-- Archivos: referencias y para imprimir, los dos con miniatura --}}
            @foreach([['referencias', 'Referencias', $t->referencias], ['imprimir', 'Archivos para imprimir', $t->archivosImprimir]] as $grupo)
                @if($grupo[2]->isNotEmpty())
                <div class="col-12">
                    <div class="txd" style="font-size:10px;letter-spacing:1px;text-transform:uppercase;margin-bottom:8px">
                        {{ $grupo[1] }} ({{ $grupo[2]->count() }})
                    </div>
                    <div style="display:flex;flex-wrap:wrap;gap:10px">
                        @foreach($grupo[2] as $arch)
                            <a href="{{ $arch->url }}" target="_blank" title="{{ $arch->nombre_original }}"
                               style="width:132px;text-decoration:none;flex-shrink:0">
                                <div style="border:1px solid var(--bm);border-radius:8px;overflow:hidden;background:#0d0d0d">
                                    @if($arch->es_imagen)
                                        <img src="{{ $arch->url }}" alt="{{ $arch->nombre_original }}"
                                             style="width:100%;height:110px;object-fit:cover;display:block">
                                    @else
                                        <div style="height:110px;display:flex;flex-direction:column;align-items:center;
                                                    justify-content:center;gap:4px;font-family:var(--mono)">
                                            <span style="font-size:15px;font-weight:700;color:var(--ac);letter-spacing:1px">
                                                {{ strtoupper($arch->extension) ?: 'ARCH' }}
                                            </span>
                                            <span class="txd" style="font-size:9px">sin vista previa</span>
                                        </div>
                                    @endif
                                </div>
                                <div style="font-size:10.5px;color:var(--txd);margin-top:4px;line-height:1.3;
                                            overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                                    {{ $arch->nombre_original }}
                                </div>
                                <div class="mono" style="font-size:9.5px;color:#555">{{ $arch->tamanio_formateado }}</div>
                            </a>
                        @endforeach
                    </div>
                </div>
                @endif
            @endforeach

        </div>
    </div>
</div>
@empty
    <div class="gcard">
        <div class="gcard-bd" style="text-align:center;color:var(--txd);padding:32px">
            Sin trabajos cargados.
        </div>
    </div>
@endforelse

@endsection

@section('scripts')
<script>
function toggleEditOrden() {
    const vista  = document.getElementById('vista-datos');
    const form   = document.getElementById('form-edit-orden');
    const btn    = document.getElementById('btn-toggle-edit');
    const editando = form.style.display !== 'none';
    vista.style.display = editando ? ''         : 'none';
    form.style.display  = editando ? 'none'     : '';
    btn.textContent     = editando ? '✎ Editar' : '✕ Cancelar';

    // Select2 recién cuando el form es visible: dentro de un contenedor oculto
    // no puede calcular el ancho y queda colapsado.
    if (! editando) initSelectCliente();
}

let selClienteListo = false;
function initSelectCliente() {
    if (selClienteListo) return;
    selClienteListo = true;

    $('#sel-cliente-orden').select2({
        ajax: {
            url: '{{ route("clientes.search") }}',
            dataType: 'json',
            delay: 250,
            data:           params => ({ q: params.term }),
            processResults: data   => ({ results: data }),
            cache: true,
        },
        minimumInputLength: 1,
        placeholder: 'Escribí el nombre del cliente...',
        width: '100%',
        dropdownParent: $('#form-edit-orden'),
    });
}
</script>
@endsection

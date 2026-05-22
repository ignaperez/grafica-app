@extends('layouts.app')

@section('page-title', 'Orden #' . $orden->id)

@section('topbar-actions')
    <div style="display:flex;gap:8px;align-items:center">
        <a href="{{ route('ordenes-trabajo.print', $orden->id) }}" target="_blank"
           class="gbtn gbtn-ghost gbtn-sm">🖨 Imprimir</a>

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
                        <label class="glabel">Fecha de ingreso</label>
                        <input type="date" name="fecha_recibido" class="ginput"
                               value="{{ $orden->fecha_recibido ? \Carbon\Carbon::parse($orden->fecha_recibido)->format('Y-m-d') : '' }}">
                    </div>
                </div>
                <div class="col-md-8">
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

            {{-- Datos principales --}}
            <div class="col-md-8">
                <div class="row g-2" style="font-size:13px">

                    @if($t->tipoTrabajo)
                    <div class="col-sm-4">
                        <div class="txd" style="font-size:10px;letter-spacing:1px;text-transform:uppercase;margin-bottom:2px">Tipo trabajo</div>
                        <div style="color:var(--tx)">{{ $t->tipoTrabajo->nombre }}</div>
                    </div>
                    @endif

                    @if($t->material)
                    <div class="col-sm-4">
                        <div class="txd" style="font-size:10px;letter-spacing:1px;text-transform:uppercase;margin-bottom:2px">Material</div>
                        <div style="color:var(--tx)">{{ $t->material->nombre }}</div>
                    </div>
                    @endif

                    @if($t->maquina)
                    <div class="col-sm-4">
                        <div class="txd" style="font-size:10px;letter-spacing:1px;text-transform:uppercase;margin-bottom:2px">Máquina</div>
                        <div style="color:var(--tx)">{{ $t->maquina->nombre }}</div>
                    </div>
                    @endif

                    @if($t->ancho || $t->alto)
                    <div class="col-sm-4">
                        <div class="txd" style="font-size:10px;letter-spacing:1px;text-transform:uppercase;margin-bottom:2px">Medidas</div>
                        <div style="color:var(--tx);font-family:var(--mono)">
                            {{ $t->ancho }}m × {{ $t->alto }}m
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="txd" style="font-size:10px;letter-spacing:1px;text-transform:uppercase;margin-bottom:2px">m² total</div>
                        <div style="color:var(--ac);font-family:var(--mono)">
                            {{ number_format($t->ancho * $t->alto * $t->cantidad, 2) }} m²
                        </div>
                    </div>
                    @endif

                    <div class="col-sm-4">
                        <div class="txd" style="font-size:10px;letter-spacing:1px;text-transform:uppercase;margin-bottom:2px">Cantidad</div>
                        <div style="color:var(--tx);font-family:var(--mono)">{{ $t->cantidad }}</div>
                    </div>

                    @if($t->fecha_entrega)
                    <div class="col-sm-4">
                        <div class="txd" style="font-size:10px;letter-spacing:1px;text-transform:uppercase;margin-bottom:2px">Entrega</div>
                        <div style="color:var(--tx)">{{ $t->fecha_entrega->format('d/m/Y') }}</div>
                    </div>
                    @endif

                </div>
            </div>

            {{-- Archivos --}}
            <div class="col-md-4">

                {{-- Referencias --}}
                @if($t->referencias->isNotEmpty())
                <div style="margin-bottom:10px">
                    <div class="txd" style="font-size:10px;letter-spacing:1px;text-transform:uppercase;margin-bottom:6px">
                        Referencias
                    </div>
                    <div style="display:flex;flex-wrap:wrap;gap:6px">
                        @foreach($t->referencias as $ref)
                            @php
                                $ext = strtolower(pathinfo($ref->nombre_original, PATHINFO_EXTENSION));
                                $esImagen = in_array($ext, ['jpg','jpeg','png','gif','bmp','webp','tif','tiff']);
                            @endphp
                            <a href="{{ $ref->url }}" target="_blank" title="{{ $ref->nombre_original }}"
                               style="display:block;border-radius:6px;overflow:hidden;
                                      border:1px solid var(--bm);flex-shrink:0">
                                @if($esImagen)
                                    <img src="{{ $ref->url }}" alt="{{ $ref->nombre_original }}"
                                         style="width:64px;height:64px;object-fit:cover;display:block">
                                @else
                                    <div style="width:64px;height:64px;display:flex;align-items:center;
                                                justify-content:center;background:#0d0d0d;
                                                font-size:9px;font-weight:700;font-family:var(--mono);
                                                color:var(--txd);letter-spacing:1px;text-transform:uppercase">
                                        {{ strtoupper($ext) }}
                                    </div>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Archivos para imprimir --}}
                @if($t->archivosImprimir->isNotEmpty())
                <div>
                    <div class="txd" style="font-size:10px;letter-spacing:1px;text-transform:uppercase;margin-bottom:6px">
                        Para imprimir ({{ $t->archivosImprimir->count() }})
                    </div>
                    @foreach($t->archivosImprimir as $arch)
                        <a href="{{ $arch->url }}" target="_blank"
                           style="display:flex;align-items:center;gap:6px;font-size:12px;
                                  color:var(--txd);text-decoration:none;margin-bottom:3px"
                           title="{{ $arch->nombre_original }}">
                            <span style="font-family:var(--mono);color:var(--ac);font-size:10px">↓</span>
                            <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:160px">
                                {{ $arch->nombre_original }}
                            </span>
                        </a>
                    @endforeach
                </div>
                @endif

            </div>
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
}
</script>
@endsection

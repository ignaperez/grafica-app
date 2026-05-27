@extends('layouts.app')

@section('page-title', 'Editar Material')

@section('topbar-actions')
    <a href="{{ route('materiales.index') }}" class="gbtn gbtn-ghost gbtn-sm">← Volver</a>
@endsection

@section('content')

@php
    $maqActivas = $material->maquinas->keyBy('id');
@endphp

<form action="{{ route('materiales.update', $material->id) }}" method="POST">
@csrf
@method('PUT')

{{-- ─── Datos del material ──────────────────────────────────── --}}
<div class="gcard mb-4" style="max-width:680px">
    <div class="gcard-hd">
        <span class="gcard-title">{{ $material->nombre }}</span>
    </div>
    <div class="gcard-bd">

        <div class="gfg">
            <label class="glabel">Nombre *</label>
            <input type="text" name="nombre" class="ginput" required
                   value="{{ old('nombre', $material->nombre) }}">
            @error('nombre') <div class="gerr">{{ $message }}</div> @enderror
        </div>

        <div class="gfg">
            <label class="glabel">Descripción</label>
            <input type="text" name="descripcion" class="ginput"
                   value="{{ old('descripcion', $material->descripcion) }}">
        </div>

        <div class="gfg">
            <label class="glabel">Unidad de venta *</label>
            <select name="unidad" class="gselect" required>
                <option value="m2"     {{ old('unidad', $material->unidad) == 'm2'     ? 'selected':'' }}>m² — metro cuadrado (ancho × alto)</option>
                <option value="ml"     {{ old('unidad', $material->unidad) == 'ml'     ? 'selected':'' }}>ml — metro lineal</option>
                <option value="unidad" {{ old('unidad', $material->unidad) == 'unidad' ? 'selected':'' }}>Unidad — precio por pieza</option>
            </select>
        </div>

        <div style="margin:16px 0 8px;font-size:12px;color:var(--txd);text-transform:uppercase;letter-spacing:.06em">
            Costo del material (materia prima)
        </div>

        <div id="campo-costo" style="max-width:200px">
            <div class="gfg" id="wrap-m2" style="{{ old('unidad', $material->unidad) != 'm2' ? 'display:none' : '' }}">
                <label class="glabel">Costo por m²</label>
                <input type="number" name="costo_m2" class="ginput" min="0" step="0.01" value="{{ old('costo_m2', $material->costo_m2) }}">
            </div>
            <div class="gfg" id="wrap-ml" style="{{ old('unidad', $material->unidad) != 'ml' ? 'display:none' : '' }}">
                <label class="glabel">Costo por ml</label>
                <input type="number" name="costo_ml" class="ginput" min="0" step="0.01" value="{{ old('costo_ml', $material->costo_ml) }}">
            </div>
            <div class="gfg" id="wrap-unidad" style="{{ old('unidad', $material->unidad) != 'unidad' ? 'display:none' : '' }}">
                <label class="glabel">Costo por unidad</label>
                <input type="number" name="costo_unidad" class="ginput" min="0" step="0.01" value="{{ old('costo_unidad', $material->costo_unidad) }}">
            </div>
        </div>

        <div style="margin:16px 0 8px">
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                <input type="hidden" name="activo" value="0">
                <input type="checkbox" name="activo" value="1"
                       {{ old('activo', $material->activo) ? 'checked' : '' }}
                       style="accent-color:var(--ac);width:16px;height:16px">
                <span class="glabel" style="margin:0">Material activo</span>
            </label>
        </div>

    </div>
</div>

{{-- ─── Máquinas compatibles ─────────────────────────────────── --}}
<div class="gcard mb-4">
    <div class="gcard-hd">
        <span class="gcard-title">¿En qué máquinas se puede usar?</span>
        <span class="txd" style="font-size:12px">{{ $maqActivas->count() }} máquina(s) asignada(s)</span>
    </div>
    <div class="gcard-bd" style="padding:0">

        @if($maquinas->isEmpty())
            <div style="padding:20px;color:var(--txd);font-size:13px">
                No hay máquinas cargadas.
                <a href="{{ route('maquinas.create') }}" style="color:var(--ac)">Crear máquina</a>
            </div>
        @else
        <table class="gtable">
            <thead>
                <tr>
                    <th style="width:40px"></th>
                    <th>Máquina</th>
                    <th class="txd" style="font-size:11px">Proceso</th>
                </tr>
            </thead>
            <tbody>
                @foreach($maquinas as $maq)
                @php $activa = $maqActivas->has($maq->id); @endphp
                <tr class="maq-row" style="{{ $activa ? '' : 'opacity:.5' }}">
                    <td style="text-align:center">
                        <input type="checkbox" name="maquinas[]" value="{{ $maq->id }}"
                               class="maq-check" {{ $activa ? 'checked' : '' }}
                               style="accent-color:var(--ac);width:16px;height:16px;cursor:pointer">
                    </td>
                    <td style="font-weight:{{ $activa ? '600' : '400' }};color:var(--tx)">
                        {{ $maq->nombre }}
                    </td>
                    <td class="txd" style="font-size:12px">{{ $maq->tipoTrabajo?->nombre ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div style="padding:10px 16px;font-size:11px;color:var(--txd)">
            La mano de obra de colocación se configura globalmente en
            <a href="{{ route('configuracion.edit') }}" style="color:var(--ac)">Configuración</a>
            y se puede pisar por lista de precios.
        </div>
        @endif

    </div>
</div>

<div style="display:flex;gap:8px">
    <button type="submit" class="gbtn gbtn-primary">Guardar cambios</button>
    <a href="{{ route('materiales.index') }}" class="gbtn gbtn-ghost">Cancelar</a>
</div>

</form>

@endsection

@section('scripts')
<script>
    // Mostrar solo el campo de costo que corresponde a la unidad elegida
    const selUnidad = document.querySelector('select[name="unidad"]');
    function actualizarCosto() {
        const u = selUnidad.value;
        document.getElementById('wrap-m2').style.display     = u === 'm2'     ? '' : 'none';
        document.getElementById('wrap-ml').style.display     = u === 'ml'     ? '' : 'none';
        document.getElementById('wrap-unidad').style.display = u === 'unidad' ? '' : 'none';
    }
    selUnidad.addEventListener('change', actualizarCosto);
    actualizarCosto();

    // Checkbox máquinas
    document.querySelectorAll('.maq-check').forEach(function (chk) {
        chk.addEventListener('change', function () {
            const row = this.closest('.maq-row');
            row.style.opacity = this.checked ? '1' : '0.5';
        });
    });
</script>
@endsection

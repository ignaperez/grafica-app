@extends('layouts.app')

@section('page-title', 'Editar Máquina')

@section('topbar-actions')
    <a href="{{ route('maquinas.index') }}" class="gbtn gbtn-ghost gbtn-sm">← Volver</a>
@endsection

@section('content')
<div class="gcard" style="max-width:560px">
    <div class="gcard-hd">
        <span class="gcard-title">{{ $maquina->nombre }}</span>
    </div>
    <div class="gcard-bd">
        <form action="{{ route('maquinas.update', $maquina->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="gfg">
                <label class="glabel">Nombre *</label>
                <input type="text" name="nombre" class="ginput" required
                       value="{{ old('nombre', $maquina->nombre) }}">
                @error('nombre') <div class="gerr">{{ $message }}</div> @enderror
            </div>

            <div class="gfg">
                <label class="glabel">Proceso (tipo de trabajo)</label>
                <select name="tipo_trabajo_id" class="gselect sel-tipo">
                    <option value="">— Sin asignar —</option>
                    @foreach($tipos as $tipo)
                        <option value="{{ $tipo->id }}"
                            {{ old('tipo_trabajo_id', $maquina->tipo_trabajo_id) == $tipo->id ? 'selected' : '' }}>
                            {{ $tipo->nombre }}
                        </option>
                    @endforeach
                </select>
                @error('tipo_trabajo_id') <div class="gerr">{{ $message }}</div> @enderror
            </div>

            <div class="gfg">
                <label class="glabel">Descripción</label>
                <input type="text" name="descripcion" class="ginput"
                       value="{{ old('descripcion', $maquina->descripcion) }}">
            </div>

            <div style="margin: 16px 0 8px; font-size:12px; color:var(--txd); text-transform:uppercase; letter-spacing:.06em;">
                Costos de impresión / proceso
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px;">
                <div class="gfg">
                    <label class="glabel">Costo por m²</label>
                    <input type="number" name="costo_m2" class="ginput"
                           min="0" step="0.01" value="{{ old('costo_m2', $maquina->costo_m2) }}">
                    @error('costo_m2') <div class="gerr">{{ $message }}</div> @enderror
                </div>
                <div class="gfg">
                    <label class="glabel">Costo por ml</label>
                    <input type="number" name="costo_ml" class="ginput"
                           min="0" step="0.01" value="{{ old('costo_ml', $maquina->costo_ml) }}">
                    @error('costo_ml') <div class="gerr">{{ $message }}</div> @enderror
                </div>
                <div class="gfg">
                    <label class="glabel">Costo por unidad</label>
                    <input type="number" name="costo_unidad" class="ginput"
                           min="0" step="0.01" value="{{ old('costo_unidad', $maquina->costo_unidad) }}">
                    @error('costo_unidad') <div class="gerr">{{ $message }}</div> @enderror
                </div>
            </div>

            <div style="margin: 16px 0 8px;">
                <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                    <input type="hidden" name="activo" value="0">
                    <input type="checkbox" name="activo" value="1"
                           {{ old('activo', $maquina->activo) ? 'checked' : '' }}
                           style="accent-color:var(--ac); width:16px; height:16px;">
                    <span class="glabel" style="margin:0;">Máquina activa</span>
                </label>
            </div>

            <div style="display:flex; gap:8px; margin-top:8px;">
                <button type="submit" class="gbtn gbtn-primary">Guardar cambios</button>
                <a href="{{ route('maquinas.index') }}" class="gbtn gbtn-ghost">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $('.sel-tipo').select2({ width: 'resolve', placeholder: '— Sin asignar —' });
</script>
@endsection

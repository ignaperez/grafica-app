@extends('layouts.app')

@section('page-title', 'Editar Material')

@section('topbar-actions')
    <a href="{{ route('materiales.index') }}" class="gbtn gbtn-ghost gbtn-sm">← Volver</a>
@endsection

@section('content')
<div class="gcard" style="max-width:560px">
    <div class="gcard-hd">
        <span class="gcard-title">{{ $material->nombre }}</span>
    </div>
    <div class="gcard-bd">
        <form action="{{ route('materiales.update', $material->id) }}" method="POST">
            @csrf
            @method('PUT')

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

            <div style="margin: 16px 0 8px; font-size:12px; color:var(--txd); text-transform:uppercase; letter-spacing:.06em;">
                Costos del material
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px;">
                <div class="gfg">
                    <label class="glabel">Costo por m²</label>
                    <input type="number" name="costo_m2" class="ginput"
                           min="0" step="0.01" value="{{ old('costo_m2', $material->costo_m2) }}">
                    @error('costo_m2') <div class="gerr">{{ $message }}</div> @enderror
                </div>
                <div class="gfg">
                    <label class="glabel">Costo por ml</label>
                    <input type="number" name="costo_ml" class="ginput"
                           min="0" step="0.01" value="{{ old('costo_ml', $material->costo_ml) }}">
                    @error('costo_ml') <div class="gerr">{{ $message }}</div> @enderror
                </div>
                <div class="gfg">
                    <label class="glabel">Costo por unidad</label>
                    <input type="number" name="costo_unidad" class="ginput"
                           min="0" step="0.01" value="{{ old('costo_unidad', $material->costo_unidad) }}">
                    @error('costo_unidad') <div class="gerr">{{ $message }}</div> @enderror
                </div>
            </div>

            <div style="margin: 16px 0 8px;">
                <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                    <input type="hidden" name="activo" value="0">
                    <input type="checkbox" name="activo" value="1"
                           {{ old('activo', $material->activo) ? 'checked' : '' }}
                           style="accent-color:var(--ac); width:16px; height:16px;">
                    <span class="glabel" style="margin:0;">Material activo</span>
                </label>
            </div>

            <div style="display:flex; gap:8px; margin-top:8px;">
                <button type="submit" class="gbtn gbtn-primary">Guardar cambios</button>
                <a href="{{ route('materiales.index') }}" class="gbtn gbtn-ghost">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection

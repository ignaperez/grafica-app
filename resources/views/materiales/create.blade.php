@extends('layouts.app')

@section('page-title', 'Nuevo Material')

@section('topbar-actions')
    <a href="{{ route('materiales.index') }}" class="gbtn gbtn-ghost gbtn-sm">← Volver</a>
@endsection

@section('content')
<div class="gcard" style="max-width:560px">
    <div class="gcard-hd">
        <span class="gcard-title">Nuevo material</span>
    </div>
    <div class="gcard-bd">
        <form action="{{ route('materiales.store') }}" method="POST">
            @csrf

            <div class="gfg">
                <label class="glabel">Nombre *</label>
                <input type="text" name="nombre" class="ginput" required
                       placeholder="Ej: Lona front brillo 13oz, Vinilo adhesivo, Acrílico 3mm"
                       value="{{ old('nombre') }}">
                @error('nombre') <div class="gerr">{{ $message }}</div> @enderror
            </div>

            <div class="gfg">
                <label class="glabel">Descripción</label>
                <input type="text" name="descripcion" class="ginput"
                       placeholder="Opcional"
                       value="{{ old('descripcion') }}">
            </div>

            <div style="margin: 16px 0 8px; font-size:12px; color:var(--txd); text-transform:uppercase; letter-spacing:.06em;">
                Costos del material
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px;">
                <div class="gfg">
                    <label class="glabel">Costo por m²</label>
                    <input type="number" name="costo_m2" class="ginput"
                           min="0" step="0.01" value="{{ old('costo_m2', '0') }}">
                    @error('costo_m2') <div class="gerr">{{ $message }}</div> @enderror
                </div>
                <div class="gfg">
                    <label class="glabel">Costo por ml</label>
                    <input type="number" name="costo_ml" class="ginput"
                           min="0" step="0.01" value="{{ old('costo_ml', '0') }}">
                    @error('costo_ml') <div class="gerr">{{ $message }}</div> @enderror
                </div>
                <div class="gfg">
                    <label class="glabel">Costo por unidad</label>
                    <input type="number" name="costo_unidad" class="ginput"
                           min="0" step="0.01" value="{{ old('costo_unidad', '0') }}">
                    @error('costo_unidad') <div class="gerr">{{ $message }}</div> @enderror
                </div>
            </div>

            <div style="display:flex; gap:8px; margin-top:8px;">
                <button type="submit" class="gbtn gbtn-primary">Guardar</button>
                <a href="{{ route('materiales.index') }}" class="gbtn gbtn-ghost">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('page-title', 'Nueva Lista de Precios')

@section('topbar-actions')
    <a href="{{ route('listas-precios.index') }}" class="gbtn gbtn-ghost gbtn-sm">← Volver</a>
@endsection

@section('content')
<div class="gcard" style="max-width:520px">
    <div class="gcard-hd">
        <span class="gcard-title">Nueva lista de precios</span>
    </div>
    <div class="gcard-bd">
        <form action="{{ route('listas-precios.store') }}" method="POST">
            @csrf

            <div class="gfg">
                <label class="glabel">Nombre *</label>
                <input type="text" name="nombre" class="ginput" required
                       placeholder="Ej: Consumidor Final, Gremio, Estado"
                       value="{{ old('nombre') }}">
                @error('nombre') <div class="gerr">{{ $message }}</div> @enderror
            </div>

            <div class="gfg">
                <label class="glabel">Descripción</label>
                <textarea name="descripcion" class="gtextarea" rows="3">{{ old('descripcion') }}</textarea>
                @error('descripcion') <div class="gerr">{{ $message }}</div> @enderror
            </div>

            <div class="gfg">
                <label class="glabel">Multiplicador *</label>
                <input type="number" name="multiplicador" class="ginput" required
                       min="0" step="0.01" value="{{ old('multiplicador', '1.00') }}"
                       style="max-width:160px">
                <div class="txm" style="margin-top:4px; font-size:12px;">
                    1.00 = precio normal &nbsp;·&nbsp; 1.50 = 50% más caro &nbsp;·&nbsp; 0.80 = 20% descuento
                </div>
                @error('multiplicador') <div class="gerr">{{ $message }}</div> @enderror
            </div>

            <div style="display:flex; gap:8px; margin-top:8px;">
                <button type="submit" class="gbtn gbtn-primary">Guardar lista</button>
                <a href="{{ route('listas-precios.index') }}" class="gbtn gbtn-ghost">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection

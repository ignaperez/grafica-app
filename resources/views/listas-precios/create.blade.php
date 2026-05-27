@extends('layouts.app')

@section('page-title', 'Nueva Lista de Precios')

@section('topbar-actions')
    <a href="{{ route('listas-precios.index') }}" class="gbtn gbtn-ghost gbtn-sm">← Volver</a>
@endsection

@section('content')
<div class="gcard mb-4" style="max-width:580px">
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
                <textarea name="descripcion" class="gtextarea" rows="2">{{ old('descripcion') }}</textarea>
            </div>

            <div class="gfg">
                <label class="glabel">Multiplicador *</label>
                <input type="number" name="multiplicador" class="ginput" required
                       min="0" step="0.01" value="{{ old('multiplicador', '1.00') }}"
                       style="max-width:160px">
                <div class="txd" style="margin-top:4px;font-size:12px">
                    1.00 = precio normal &nbsp;·&nbsp; 1.50 = 50% más &nbsp;·&nbsp; 0.80 = 20% descuento
                </div>
                @error('multiplicador') <div class="gerr">{{ $message }}</div> @enderror
            </div>

            {{-- MO override --}}
            <div style="margin:20px 0 8px;font-size:12px;color:var(--txd);text-transform:uppercase;letter-spacing:.06em">
                Mano de obra (colocación) — opcional
            </div>
            <div class="txd" style="font-size:12px;margin-bottom:12px">
                Dejá en blanco para usar el valor global
                (<span class="mono" style="color:var(--ac)">${{ number_format($moGlobal['m2'],2) }}/m²</span>).
                Completá para pisar con un valor distinto para esta lista.
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px">
                <div class="gfg">
                    <label class="glabel">MO por m²</label>
                    <input type="number" name="mo_m2" step="0.01" min="0"
                           class="ginput" placeholder="{{ $moGlobal['m2'] }}"
                           value="{{ old('mo_m2') }}">
                </div>
                <div class="gfg">
                    <label class="glabel">MO por ml</label>
                    <input type="number" name="mo_ml" step="0.01" min="0"
                           class="ginput" placeholder="{{ $moGlobal['ml'] }}"
                           value="{{ old('mo_ml') }}">
                </div>
                <div class="gfg">
                    <label class="glabel">MO por unidad</label>
                    <input type="number" name="mo_unidad" step="0.01" min="0"
                           class="ginput" placeholder="{{ $moGlobal['unidad'] }}"
                           value="{{ old('mo_unidad') }}">
                </div>
            </div>

            <div style="display:flex;gap:8px;margin-top:16px">
                <button type="submit" class="gbtn gbtn-primary">Guardar lista</button>
                <a href="{{ route('listas-precios.index') }}" class="gbtn gbtn-ghost">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection

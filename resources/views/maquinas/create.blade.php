@extends('layouts.app')

@section('page-title', 'Nueva Máquina')

@section('topbar-actions')
    <a href="{{ route('maquinas.index') }}" class="gbtn gbtn-ghost gbtn-sm">← Volver</a>
@endsection

@section('content')

<form action="{{ route('maquinas.store') }}" method="POST">
@csrf

<div class="gcard mb-4" style="max-width:680px">
    <div class="gcard-hd">
        <span class="gcard-title">Nueva máquina</span>
    </div>
    <div class="gcard-bd">

        <div class="gfg">
            <label class="glabel">Nombre *</label>
            <input type="text" name="nombre" class="ginput" required
                   placeholder="Ej: Roland VS-300, China 4P, UV 6P"
                   value="{{ old('nombre') }}">
            @error('nombre') <div class="gerr">{{ $message }}</div> @enderror
        </div>

        <div class="gfg">
            <label class="glabel">Proceso (tipo de trabajo)</label>
            <select name="tipo_trabajo_id" class="gselect sel-tipo">
                <option value="">— Sin asignar —</option>
                @foreach($tipos as $tipo)
                    <option value="{{ $tipo->id }}" {{ old('tipo_trabajo_id') == $tipo->id ? 'selected' : '' }}>
                        {{ $tipo->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="gfg">
            <label class="glabel">Descripción</label>
            <input type="text" name="descripcion" class="ginput"
                   placeholder="Opcional" value="{{ old('descripcion') }}">
        </div>

        <div style="margin:16px 0 8px;font-size:12px;color:var(--txd);text-transform:uppercase;letter-spacing:.06em">
            Costo del proceso (máquina / tinta)
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px">
            <div class="gfg">
                <label class="glabel">Por m²</label>
                <input type="number" name="costo_m2" class="ginput"
                       min="0" step="0.01" value="{{ old('costo_m2', '0') }}">
            </div>
            <div class="gfg">
                <label class="glabel">Por ml</label>
                <input type="number" name="costo_ml" class="ginput"
                       min="0" step="0.01" value="{{ old('costo_ml', '0') }}">
            </div>
            <div class="gfg">
                <label class="glabel">Por unidad</label>
                <input type="number" name="costo_unidad" class="ginput"
                       min="0" step="0.01" value="{{ old('costo_unidad', '0') }}">
            </div>
        </div>

        <div style="display:flex;gap:8px;margin-top:8px">
            <button type="submit" class="gbtn gbtn-primary">Guardar máquina</button>
            <a href="{{ route('maquinas.index') }}" class="gbtn gbtn-ghost">Cancelar</a>
        </div>

    </div>
</div>

</form>

<div class="gcard" style="max-width:680px;border-color:var(--bm)">
    <div class="gcard-bd" style="padding:12px 16px">
        <div class="txd" style="font-size:12px">
            💡 Los materiales compatibles se asignan desde el ABM de Materiales —
            al crear o editar un material elegís en qué máquinas se puede usar.
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    $('.sel-tipo').select2({ width: 'resolve', placeholder: '— Sin asignar —' });
</script>
@endsection

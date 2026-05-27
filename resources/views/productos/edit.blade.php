@extends('layouts.app')

@section('page-title', 'Editar servicio')

@section('topbar-actions')
    <a href="{{ route('productos.index') }}" class="gbtn gbtn-ghost gbtn-sm">← Volver</a>
@endsection

@section('content')

<form action="{{ route('productos.update', $producto->id) }}" method="POST">
@csrf
@method('PUT')

<div class="gcard mb-4">
    <div class="gcard-hd">
        <span class="gcard-title">{{ $producto->nombre }}</span>
        <span class="txd mono" style="font-size:12px">#{{ $producto->id }}</span>
    </div>
    <div class="gcard-bd">
        <div class="row g-3">

            {{-- Nombre --}}
            <div class="col-md-6">
                <div class="gfg">
                    <label class="glabel">Nombre *</label>
                    <input type="text" name="nombre" class="ginput"
                           value="{{ old('nombre', $producto->nombre) }}" required>
                    @error('nombre')<div class="gerr">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- Unidad --}}
            <div class="col-md-3">
                <div class="gfg">
                    <label class="glabel">Unidad de medida *</label>
                    <select name="unidad" class="gselect" required>
                        <option value="m2"     {{ old('unidad', $producto->unidad) == 'm2'     ? 'selected':'' }}>m² (ancho × alto × cantidad)</option>
                        <option value="ml"     {{ old('unidad', $producto->unidad) == 'ml'     ? 'selected':'' }}>ml (metro lineal × cantidad)</option>
                        <option value="unidad" {{ old('unidad', $producto->unidad) == 'unidad' ? 'selected':'' }}>Unidad (× cantidad)</option>
                    </select>
                </div>
            </div>

            {{-- Activo --}}
            <div class="col-md-3">
                <div class="gfg">
                    <label class="glabel">Estado</label>
                    <select name="activo" class="gselect">
                        <option value="1" {{ old('activo', $producto->activo ? '1' : '0') == '1' ? 'selected' : '' }}>Activo</option>
                        <option value="0" {{ old('activo', $producto->activo ? '1' : '0') == '0' ? 'selected' : '' }}>Inactivo</option>
                    </select>
                </div>
            </div>

            {{-- Tipo de trabajo --}}
            <div class="col-md-4">
                <div class="gfg">
                    <label class="glabel">Proceso / Tipo de trabajo</label>
                    <select name="tipo_trabajo_id" class="gselect">
                        <option value="">— Sin especificar —</option>
                        @foreach($tipos as $t)
                            <option value="{{ $t->id }}"
                                {{ old('tipo_trabajo_id', $producto->tipo_trabajo_id) == $t->id ? 'selected' : '' }}>
                                {{ $t->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('tipo_trabajo_id')<div class="gerr">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- Material --}}
            <div class="col-md-4">
                <div class="gfg">
                    <label class="glabel">Material base</label>
                    <select name="material_id" class="gselect">
                        <option value="">— Sin especificar —</option>
                        @foreach($materiales as $m)
                            <option value="{{ $m->id }}"
                                {{ old('material_id', $producto->material_id) == $m->id ? 'selected' : '' }}>
                                {{ $m->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('material_id')<div class="gerr">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- Mano de obra --}}
            <div class="col-md-4">
                <div class="gfg">
                    <label class="glabel">Costo mano de obra</label>
                    <div style="position:relative">
                        <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--txd)">$</span>
                        <input type="number" step="0.01" min="0" name="costo_mano_obra"
                               class="ginput" style="padding-left:24px"
                               value="{{ old('costo_mano_obra', $producto->costo_mano_obra) }}">
                    </div>
                    @error('costo_mano_obra')<div class="gerr">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- Descripción --}}
            <div class="col-12">
                <div class="gfg">
                    <label class="glabel">Descripción / notas internas</label>
                    <textarea name="descripcion" class="gtextarea" rows="2">{{ old('descripcion', $producto->descripcion) }}</textarea>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- Cómo se compone el precio --}}
@if($producto->material || $producto->tipoTrabajo)
<div class="gcard mb-4" style="border-color:var(--bm)">
    <div class="gcard-hd"><span class="gcard-title" style="font-size:13px">Costos actuales del servicio</span></div>
    <div class="gcard-bd">
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;font-size:13px">
            @php
                $campo = match($producto->unidad) { 'ml' => 'costo_ml', 'unidad' => 'costo_unidad', default => 'costo_m2' };
                $costoMat = $producto->material ? $producto->material->{$campo} : 0;
                $moTotal  = $producto->costo_mano_obra ?? 0;
            @endphp
            <div>
                <div class="txd" style="font-size:10px;letter-spacing:1px;text-transform:uppercase;margin-bottom:4px">Material ({{ $campo }})</div>
                <div class="mono" style="font-size:16px;color:var(--tx)">${{ number_format($costoMat, 2) }}</div>
                <div class="txd" style="font-size:11px">{{ $producto->material?->nombre ?? '—' }}</div>
            </div>
            <div>
                <div class="txd" style="font-size:10px;letter-spacing:1px;text-transform:uppercase;margin-bottom:4px">Mano de obra</div>
                <div class="mono" style="font-size:16px;color:var(--tx)">${{ number_format($producto->costo_mano_obra, 2) }}</div>
            </div>
            <div>
                <div class="txd" style="font-size:10px;letter-spacing:1px;text-transform:uppercase;margin-bottom:4px">Subtotal (sin máquina)</div>
                <div class="mono" style="font-size:16px;color:var(--ac)">${{ number_format($costoMat + $producto->costo_mano_obra, 2) }}</div>
                <div class="txd" style="font-size:11px">+ costo máquina según trabajo</div>
            </div>
        </div>
    </div>
</div>
@endif

<div style="display:flex;gap:10px">
    <button type="submit" class="gbtn gbtn-primary">Guardar cambios</button>
    <a href="{{ route('productos.index') }}" class="gbtn gbtn-ghost">Cancelar</a>
</div>

</form>

@endsection

@extends('layouts.app')

@section('page-title', 'Nuevo servicio')

@section('topbar-actions')
    <a href="{{ route('productos.index') }}" class="gbtn gbtn-ghost gbtn-sm">← Volver</a>
@endsection

@section('content')

<form action="{{ route('productos.store') }}" method="POST">
@csrf

<div class="gcard mb-4">
    <div class="gcard-hd">
        <span class="gcard-title">Datos del servicio</span>
    </div>
    <div class="gcard-bd">
        <div class="row g-3">

            {{-- Nombre --}}
            <div class="col-md-6">
                <div class="gfg">
                    <label class="glabel">Nombre *</label>
                    <input type="text" name="nombre" class="ginput"
                           value="{{ old('nombre') }}" required
                           placeholder="Ej: Impresión solvente lona">
                    @error('nombre')<div class="gerr">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- Unidad --}}
            <div class="col-md-3">
                <div class="gfg">
                    <label class="glabel">Unidad de medida *</label>
                    <select name="unidad" class="gselect" required>
                        <option value="m2"     {{ old('unidad','m2') == 'm2'     ? 'selected':'' }}>m² (ancho × alto × cantidad)</option>
                        <option value="ml"     {{ old('unidad')      == 'ml'     ? 'selected':'' }}>ml (metro lineal × cantidad)</option>
                        <option value="unidad" {{ old('unidad')      == 'unidad' ? 'selected':'' }}>Unidad (× cantidad)</option>
                    </select>
                    @error('unidad')<div class="gerr">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- Activo --}}
            <div class="col-md-3">
                <div class="gfg">
                    <label class="glabel">Estado</label>
                    <select name="activo" class="gselect">
                        <option value="1" {{ old('activo', '1') == '1' ? 'selected' : '' }}>Activo</option>
                        <option value="0" {{ old('activo') == '0' ? 'selected' : '' }}>Inactivo</option>
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
                            <option value="{{ $t->id }}" {{ old('tipo_trabajo_id') == $t->id ? 'selected' : '' }}>
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
                            <option value="{{ $m->id }}" {{ old('material_id') == $m->id ? 'selected' : '' }}>
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
                    <label class="glabel">Costo mano de obra (instalación, acabado, etc.)</label>
                    <div style="position:relative">
                        <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--txd)">$</span>
                        <input type="number" step="0.01" min="0" name="costo_mano_obra"
                               class="ginput" style="padding-left:24px"
                               value="{{ old('costo_mano_obra', 0) }}"
                               placeholder="0.00">
                    </div>
                    @error('costo_mano_obra')<div class="gerr">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- Descripción --}}
            <div class="col-12">
                <div class="gfg">
                    <label class="glabel">Descripción / notas internas</label>
                    <textarea name="descripcion" class="gtextarea" rows="2"
                              placeholder="Características, especificaciones, condiciones de uso...">{{ old('descripcion') }}</textarea>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- Info de cómo se compone el precio --}}
<div class="gcard mb-4" style="border-color:var(--bm)">
    <div class="gcard-bd" style="padding:14px 20px">
        <div class="txd" style="font-size:12px;line-height:1.7">
            <strong style="color:var(--tx)">¿Cómo se calcula el precio final?</strong><br>
            <span class="mono" style="color:var(--ac)">(costo_material + costo_maquina + mano_de_obra) × cantidad × multiplicador_lista</span><br>
            Los costos de material y máquina se configuran en sus respectivos ABM. El multiplicador viene de la lista de precios del cliente.
        </div>
    </div>
</div>

<div style="display:flex;gap:10px">
    <button type="submit" class="gbtn gbtn-primary">Guardar servicio</button>
    <a href="{{ route('productos.index') }}" class="gbtn gbtn-ghost">Cancelar</a>
</div>

</form>

@endsection

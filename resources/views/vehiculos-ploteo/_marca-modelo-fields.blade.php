{{-- Campos Marca (select + alta) y Modelo (dependiente + alta). Requiere $marcas; $vehiculo opcional. --}}
@php $vehSel = $vehiculo ?? null; @endphp
<div class="col-md-4">
    <div class="gfg">
        <label class="glabel">Marca *</label>
        <div style="display:flex;gap:6px;align-items:stretch">
            <select name="marca_id" id="veh-marca" class="gselect" required style="flex:1">
                <option value="">— Marca —</option>
                @foreach($marcas as $m)
                    <option value="{{ $m->id }}" {{ (string) old('marca_id', $vehSel->marca_id ?? '') === (string) $m->id ? 'selected' : '' }}>{{ $m->nombre }}</option>
                @endforeach
            </select>
            <button type="button" class="gbtn gbtn-ghost gbtn-sm" onclick="abrirNuevaMarca()" title="Agregar marca" style="padding:0 12px">+</button>
        </div>
        @error('marca_id')<div class="gerr">{{ $message }}</div>@enderror
    </div>
</div>
<div class="col-md-4">
    <div class="gfg">
        <label class="glabel">Modelo *</label>
        <div style="display:flex;gap:6px;align-items:stretch">
            <select name="modelo_id" id="veh-modelo" class="gselect" required style="flex:1"
                    data-selected="{{ old('modelo_id', $vehSel->modelo_id ?? '') }}">
                <option value="">— Elegí una marca primero —</option>
            </select>
            <button type="button" class="gbtn gbtn-ghost gbtn-sm" onclick="abrirNuevoModelo()" title="Agregar modelo" style="padding:0 12px">+</button>
        </div>
        @error('modelo_id')<div class="gerr">{{ $message }}</div>@enderror
    </div>
</div>

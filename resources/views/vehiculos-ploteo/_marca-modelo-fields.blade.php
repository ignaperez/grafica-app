{{-- Campos Marca (select + alta) y Modelo (dependiente + alta). Requiere $marcas; $vehiculo opcional. --}}
@php $vehSel = $vehiculo ?? null; @endphp
<div class="col-md-4">
    <div class="gfg">
        <label class="glabel">Marca *</label>
        <div style="display:flex;gap:6px;align-items:stretch">
            <select name="marca_id" id="veh-marca" class="gselect" required style="flex:1">
                <option value="">— Marca —</option>
                @foreach($marcas as $m)
                    <option value="{{ $m->id }}" data-libre="{{ $m->modelo_libre ? 1 : 0 }}" {{ (string) old('marca_id', $vehSel->marca_id ?? '') === (string) $m->id ? 'selected' : '' }}>{{ $m->nombre }}</option>
                @endforeach
            </select>
            <button type="button" class="gbtn gbtn-ghost gbtn-sm" onclick="abrirNuevaMarca()" title="Agregar marca" style="padding:0 12px">+</button>
        </div>
        @error('marca_id')<div class="gerr">{{ $message }}</div>@enderror
    </div>
</div>
<div class="col-md-4">
    {{-- Modelo del catálogo --}}
    <div class="gfg" id="veh-modelo-catalogo">
        <label class="glabel">Modelo *</label>
        <div style="display:flex;gap:6px;align-items:stretch">
            <select name="modelo_id" id="veh-modelo" class="gselect" style="flex:1"
                    data-selected="{{ old('modelo_id', $vehSel->modelo_id ?? '') }}">
                <option value="">— Elegí una marca primero —</option>
            </select>
            <button type="button" class="gbtn gbtn-ghost gbtn-sm" onclick="abrirNuevoModelo()" title="Agregar modelo" style="padding:0 12px">+</button>
        </div>
        @error('modelo_id')<div class="gerr">{{ $message }}</div>@enderror
    </div>

    {{-- Texto libre: para marcas sin catálogo (lancha, trailer, lo que sea) --}}
    <div class="gfg" id="veh-modelo-libre" style="display:none">
        <label class="glabel">Modelo / descripción *</label>
        <input type="text" name="modelo_libre" id="veh-modelo-libre-input" class="ginput" maxlength="100"
               placeholder="Ej: Lancha Quicksilver 450"
               value="{{ old('modelo_libre', ($vehSel && ! $vehSel->modelo_id) ? $vehSel->modelo : '') }}">
        <div class="txd" style="font-size:11px;margin-top:3px">Esta marca no tiene catálogo de modelos: escribilo a mano.</div>
        @error('modelo_libre')<div class="gerr">{{ $message }}</div>@enderror
    </div>
</div>

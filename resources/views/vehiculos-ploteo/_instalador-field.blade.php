{{--
    Asignación del vehículo a un colocador tercerizado.
    Espera $asignado = el User instalador ya asignado (o null).
    No se muestra al propio instalador: él no asigna, recibe.
--}}
@unless(auth()->user()->esInstalador())
<div class="gfg">
    <label class="glabel">Colocador asignado</label>
    {{-- Select vacío salvo el actual: Select2 busca por AJAX al escribir --}}
    <select name="instalador_id" class="gselect" id="sel-instalador">
        <option value="">— Sin asignar —</option>
        @if($asignado)
            <option value="{{ $asignado->id }}" selected>{{ $asignado->name }} · {{ $asignado->email }}</option>
        @endif
    </select>
    <div class="txd" style="font-size:11px;margin-top:3px">
        El colocador ve en su listado solo los vehículos que tiene asignados, y carga ahí las fotos.
    </div>
    @error('instalador_id')<div class="gerr">{{ $message }}</div>@enderror
</div>
@endunless

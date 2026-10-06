@php
    $seleccionados = $seleccionados ?? [];
    // Rol actualmente elegido. Vacío en el alta hasta que se elija uno: ahí se
    // muestran todos habilitados y el JS los va atenuando al cambiar el select.
    $rolActual = $rolActual ?? '';
@endphp
<div class="gcard" style="margin-top:12px">
    <div class="gcard-hd">
        <span class="gcard-title">Módulos habilitados</span>
        <span class="txd" style="font-size:11px">Qué secciones puede ver este usuario</span>
    </div>
    <div class="gcard-bd">
        {{-- marca que el form envió la sección (aunque no se tilde nada) --}}
        <input type="hidden" name="modulos_marcado" value="1">

        <div class="txd" style="font-size:11.5px;margin-bottom:10px;line-height:1.5">
            El rol es el techo y el módulo recorta: nunca suma. Los módulos atenuados
            no están disponibles para el rol elegido.
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px" id="modulos-grid">
            @foreach(\App\Models\User::MODULOS as $key => $label)
                @php
                    $aplica  = $rolActual === '' || \App\Models\User::moduloAplicaA($key, $rolActual);
                    $marcado = in_array($key, $seleccionados, true);
                    $nota    = $rolActual !== '' ? \App\Models\User::moduloNota($key, $rolActual) : null;
                @endphp
                <label class="mod-item {{ $aplica ? '' : 'mod-na' }}" data-modulo="{{ $key }}"
                       @unless($aplica) title="Disponible solo para: {{ \App\Models\User::rolesDeModuloLabel($key) }}" @endunless>
                    <input type="checkbox" name="modulos[]" value="{{ $key }}"
                           {{ $marcado ? 'checked' : '' }} {{ $aplica ? '' : 'disabled' }}>
                    <span class="mod-label">
                        {{ $label }}
                        {{-- El span va SIEMPRE (aunque vacío): el JS reescribe su
                             texto al cambiar el rol, y si no existe no lo crea. --}}
                        <span class="mod-nota">@if(! $aplica)solo {{ \App\Models\User::rolesDeModuloLabel($key) }}@elseif($nota){{ $nota }}@endif</span>
                    </span>
                </label>

                {{-- Un checkbox deshabilitado no envía nada. Si el módulo estaba
                     guardado, se preserva acá para no perderlo en silencio al
                     guardar (y recuperarlo si se le vuelve a dar el rol). --}}
                @if(! $aplica && $marcado)
                    <input type="hidden" name="modulos[]" value="{{ $key }}" data-preserva="{{ $key }}">
                @endif
            @endforeach
        </div>
    </div>
</div>

<style>
    .mod-item{display:flex;align-items:flex-start;gap:9px;padding:8px 10px;border:1px solid var(--b);
              border-radius:8px;cursor:pointer;font-size:13px;transition:opacity .12s,border-color .12s}
    .mod-item input{margin-top:2px;flex-shrink:0}
    .mod-label{display:block;line-height:1.35}
    .mod-nota{display:block;font-size:10.5px;color:var(--txd);margin-top:2px}
    .mod-item.mod-na{opacity:.4;cursor:not-allowed;border-style:dashed}
</style>

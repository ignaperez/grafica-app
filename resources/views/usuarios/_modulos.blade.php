@php $seleccionados = $seleccionados ?? []; @endphp
<div class="gcard" style="margin-top:12px">
    <div class="gcard-hd">
        <span class="gcard-title">Módulos habilitados</span>
        <span class="txd" style="font-size:11px">Qué secciones puede ver este usuario</span>
    </div>
    <div class="gcard-bd">
        {{-- marca que el form envió la sección (aunque no se tilde nada) --}}
        <input type="hidden" name="modulos_marcado" value="1">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px" id="modulos-grid">
            @foreach(\App\Models\User::MODULOS as $key => $label)
            <label style="display:flex;align-items:center;gap:9px;padding:8px 10px;border:1px solid var(--b);border-radius:8px;cursor:pointer;font-size:13px">
                <input type="checkbox" name="modulos[]" value="{{ $key }}"
                       {{ in_array($key, $seleccionados, true) ? 'checked' : '' }}>
                {{ $label }}
            </label>
            @endforeach
        </div>
    </div>
</div>

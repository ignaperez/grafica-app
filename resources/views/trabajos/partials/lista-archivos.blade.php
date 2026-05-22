@php
$iconos = [
    'img' => ['color' => '#4a8fd4', 'label' => 'IMG'],
    'pdf' => ['color' => '#e05555', 'label' => 'PDF'],
    'vec' => ['color' => '#d4900a', 'label' => 'VEC'],
    'psd' => ['color' => '#4caf6e', 'label' => 'PSD'],
    'arc' => ['color' => '#666',    'label' => 'ARC'],
];
@endphp

@forelse($archivos as $archivo)
    @php $ic = $iconos[$archivo->icono] ?? $iconos['arc']; @endphp
    <div style="display:flex;align-items:center;gap:10px;padding:9px 0;border-bottom:1px solid #1e1e1e">

        {{-- Icono tipo --}}
        <span style="flex-shrink:0;width:36px;height:36px;border-radius:6px;
                     background:{{ $ic['color'] }}18;border:1px solid {{ $ic['color'] }}40;
                     display:flex;align-items:center;justify-content:center;
                     font-size:9px;font-weight:700;color:{{ $ic['color'] }};
                     font-family:var(--mono);letter-spacing:1px">
            {{ $ic['label'] }}
        </span>

        {{-- Nombre y peso --}}
        <div style="flex:1;min-width:0">
            <a href="{{ $archivo->url }}" target="_blank"
               style="font-size:13px;color:var(--tx);text-decoration:none;display:block;
                      overflow:hidden;text-overflow:ellipsis;white-space:nowrap"
               title="{{ $archivo->nombre_original }}">
                {{ $archivo->nombre_original }}
            </a>
            <span class="txd" style="font-size:11px">{{ $archivo->tamanio_formateado }}</span>
        </div>

        {{-- Botón eliminar --}}
        <form action="{{ route('trabajo-archivos.destroy', $archivo->id) }}"
              method="POST" style="flex-shrink:0"
              onsubmit="return confirm('¿Eliminar este archivo?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="gbtn gbtn-danger gbtn-xs">✕</button>
        </form>

    </div>
@empty
    <p class="txd" style="font-size:13px;margin:0">Sin archivos cargados.</p>
@endforelse

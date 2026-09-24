@extends('layouts.app')

@section('page-title', $vehiculo->patente . ' — Cargar fotos')

@section('topbar-actions')
    <a href="{{ route('vehiculos-ploteo.print', $vehiculo->id) }}" target="_blank" class="gbtn gbtn-ghost gbtn-sm">🖨 Imprimir</a>
    @include('vehiculos-ploteo._terminado-boton', ['v' => $vehiculo])
    <a href="{{ route('vehiculos-ploteo.show', $vehiculo->id) }}" class="gbtn gbtn-ghost gbtn-sm">← Volver</a>
@endsection

@section('content')
<style>
    .foto-drop {
        display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px;
        border: 1px dashed var(--bm); border-radius: 10px; background: #0d0d0d;
        min-height: 110px; cursor: pointer; padding: 10px; text-align: center;
    }
    .foto-drop:hover { border-color: var(--ac); }
    .foto-drop input { display: none; }
    .foto-drop-icon { font-size: 22px; }
    .foto-drop-txt  { font-size: 11px; color: var(--txd); line-height: 1.3; word-break: break-word; }
    .foto-actual    { width: 100%; height: 88px; object-fit: cover; border-radius: 8px;
                      border: 1px solid var(--bm); display: block; margin-bottom: 6px; cursor: zoom-in; }
    .grid-fotos     { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; }
    @media (max-width: 720px) { .grid-fotos { grid-template-columns: repeat(2, 1fr); } }

    .ref-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 10px; }
    @media (max-width: 640px) { .ref-grid { grid-template-columns: repeat(2, 1fr); } }
    .ref-img { width: 100%; aspect-ratio: 4/3; object-fit: cover; border-radius: 8px;
               border: 1px solid var(--bm); display: block; cursor: zoom-in; }
    .ref-doc { display: flex; flex-direction: column; align-items: center; justify-content: center;
               width: 100%; aspect-ratio: 4/3; border-radius: 8px; border: 1px solid var(--bm);
               background: #0d0d0d; text-decoration: none; font-family: var(--mono);
               color: var(--ac); font-weight: 700; }

    #lightbox { display:none; position:fixed; inset:0; background:rgba(0,0,0,.92); z-index:9999;
                align-items:center; justify-content:center; cursor:zoom-out; }
    #lightbox.open { display:flex; }
    #lightbox img { max-width:92vw; max-height:92vh; object-fit:contain; border-radius:10px; }
</style>

{{-- ── Datos del vehículo (solo lectura para el colocador) ────────── --}}
<div class="gcard mb-3">
    <div class="gcard-hd"><span class="gcard-title">Vehículo asignado</span></div>
    <div class="gcard-bd">
        <div class="row g-3" style="font-size:13.5px">
            <div class="col-6 col-md-3">
                <div class="txd" style="font-size:10px;letter-spacing:1px;text-transform:uppercase;margin-bottom:4px">Patente</div>
                <div style="font-family:var(--mono);font-size:18px;font-weight:700;letter-spacing:3px;color:var(--tx)">
                    {{ $vehiculo->patente }}
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="txd" style="font-size:10px;letter-spacing:1px;text-transform:uppercase;margin-bottom:4px">Vehículo</div>
                <div style="font-weight:500;color:var(--tx)">{{ $vehiculo->marca }} {{ $vehiculo->modelo }}</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="txd" style="font-size:10px;letter-spacing:1px;text-transform:uppercase;margin-bottom:4px">Ploteo</div>
                <div style="font-weight:500;color:var(--tx)">
                    {{ $vehiculo->tipo_ploteo === 'completo' ? 'Completo' : 'Parcial' }}
                    @if($vehiculo->tipo_ploteo !== 'completo' && $vehiculo->sector)
                        — {{ \App\Models\VehiculoPloteo::sectores()[$vehiculo->sector] ?? $vehiculo->sector }}
                    @endif
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="txd" style="font-size:10px;letter-spacing:1px;text-transform:uppercase;margin-bottom:4px">Estado</div>
                <div>
                    @if($vehiculo->terminado())
                        <span style="color:#3fb96a;font-weight:600">✓ Terminado</span>
                        <div class="txd" style="font-size:10.5px">{{ $vehiculo->terminado_at->format('d/m/Y H:i') }}</div>
                    @else
                        <span style="color:#d9a441;font-weight:600">○ Pendiente</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Referencias: lo que hay que hacer ──────────────────────────── --}}
@php $refs = $vehiculo->referencias; @endphp
@if($refs->isNotEmpty())
<div class="gcard mb-3">
    <div class="gcard-hd"><span class="gcard-title">Referencias ({{ $refs->count() }})</span></div>
    <div class="gcard-bd">
        <div class="ref-grid">
            @foreach($refs as $ref)
                @if($ref->es_imagen)
                    <img src="{{ $ref->url }}" alt="{{ $ref->nombre_original }}"
                         class="ref-img" onclick="openLightbox(this.src)">
                @else
                    <a href="{{ $ref->url }}" target="_blank" class="ref-doc">
                        {{ strtoupper($ref->extension) ?: 'ARCH' }}
                    </a>
                @endif
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- ── Lo que carga el colocador ──────────────────────────────────── --}}
<form method="POST" action="{{ route('vehiculos-ploteo.update', $vehiculo->id) }}" enctype="multipart/form-data">
    @csrf @method('PUT')

    @foreach([
        ['Fotos ANTES de plotear',   ['foto_antes_frente'=>'Frente','foto_antes_atras'=>'Atrás','foto_antes_izq'=>'Izquierda','foto_antes_der'=>'Derecha']],
        ['Fotos DESPUÉS de plotear', ['foto_despues_frente'=>'Frente','foto_despues_atras'=>'Atrás','foto_despues_izq'=>'Izquierda','foto_despues_der'=>'Derecha']],
    ] as $bloque)
    <div class="gcard mb-3">
        <div class="gcard-hd"><span class="gcard-title">{{ $bloque[0] }}</span></div>
        <div class="gcard-bd">
            <div class="grid-fotos">
                @foreach($bloque[1] as $campo => $label)
                <div>
                    @if($vehiculo->$campo)
                        <img src="{{ route('vehiculos-ploteo.foto', [$vehiculo->id, $campo]) }}"
                             class="foto-actual" alt="{{ $label }}" onclick="openLightbox(this.src)">
                    @endif
                    <label class="foto-drop" for="{{ $campo }}">
                        <span class="foto-drop-icon">📷</span>
                        <span class="foto-drop-txt" id="{{ $campo }}-txt">
                            {{ $label }}{{ $vehiculo->$campo ? ' · reemplazar' : '' }}
                        </span>
                        <input type="file" id="{{ $campo }}" name="{{ $campo }}" accept="image/*"
                               onchange="document.getElementById('{{ $campo }}-txt').textContent = this.files[0]?.name ?? '{{ $label }}'">
                    </label>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endforeach

    {{-- Observaciones: lo único de texto que puede tocar --}}
    <div class="gcard mb-3">
        <div class="gcard-hd"><span class="gcard-title">Comentarios</span></div>
        <div class="gcard-bd">
            <div class="gfg mb-0">
                <textarea name="observaciones" class="gtextarea" rows="4"
                          placeholder="Algo para avisar sobre este vehículo...">{{ old('observaciones', $vehiculo->observaciones) }}</textarea>
                @error('observaciones')<div class="gerr">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>

    <button type="submit" class="gbtn gbtn-primary" style="width:100%;padding:12px">Guardar</button>
</form>

<div id="lightbox" onclick="closeLightbox()"><img id="lightbox-img" src="" alt=""></div>
@endsection

@section('scripts')
<script>
function openLightbox(src) {
    document.getElementById('lightbox-img').src = src;
    document.getElementById('lightbox').classList.add('open');
}
function closeLightbox() {
    document.getElementById('lightbox').classList.remove('open');
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeLightbox(); });
</script>
@endsection

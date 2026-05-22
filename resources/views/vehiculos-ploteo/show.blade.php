@extends('layouts.app')

@section('page-title', $vehiculo->patente . ' — ' . $vehiculo->marca . ' ' . $vehiculo->modelo)

@section('topbar-actions')
    <div style="display:flex;gap:8px">
        <a href="{{ route('vehiculos-ploteo.edit', $vehiculo->id) }}" class="gbtn gbtn-ghost gbtn-sm">✎ Editar</a>
        <a href="{{ route('vehiculos-ploteo.index') }}" class="gbtn gbtn-ghost gbtn-sm">← Volver</a>
    </div>
@endsection

@section('content')
<style>
.foto-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:10px; }
@media(max-width:640px){ .foto-grid { grid-template-columns:repeat(2,1fr); } }
.foto-card { position:relative; border-radius:10px; overflow:hidden; background:#0d0d0d; border:1px solid #1e1e1e; aspect-ratio:4/3; }
.foto-card img { width:100%; height:100%; object-fit:cover; display:block; cursor:zoom-in; transition:transform .2s; }
.foto-card img:hover { transform:scale(1.03); }
.foto-empty { display:flex; align-items:center; justify-content:center; height:100%; color:#2a2a2a; font-size:28px; flex-direction:column; gap:6px; }
.foto-empty span { font-size:10px; color:#222; letter-spacing:1px; text-transform:uppercase; }
.foto-label { position:absolute; bottom:0; left:0; right:0; padding:5px 8px; background:rgba(0,0,0,.6); font-size:10px; color:#888; letter-spacing:.5px; }
.foto-del { position:absolute; top:6px; right:6px; background:rgba(0,0,0,.7); border:none; border-radius:6px; color:#e05555; font-size:14px; width:26px; height:26px; cursor:pointer; display:none; align-items:center; justify-content:center; }
.foto-card:hover .foto-del { display:flex; }

/* Lightbox */
#lightbox { display:none; position:fixed; inset:0; background:rgba(0,0,0,.92); z-index:9999; align-items:center; justify-content:center; cursor:zoom-out; }
#lightbox.open { display:flex; }
#lightbox img { max-width:90vw; max-height:90vh; object-fit:contain; border-radius:10px; }
</style>

{{-- Datos --}}
<div class="gcard mb-3">
    <div class="gcard-hd">
        <span class="gcard-title">Vehículo</span>
        @if($vehiculo->orden)
            <a href="{{ route('ordenes-trabajo.show', $vehiculo->orden_trabajo_id) }}"
               class="gbtn gbtn-ghost gbtn-xs">
                Orden #{{ str_pad($vehiculo->orden_trabajo_id,4,'0',STR_PAD_LEFT) }} →
            </a>
        @endif
    </div>
    <div class="gcard-bd">
        <div class="row g-3" style="font-size:13.5px">
            <div class="col-6 col-md-3">
                <div class="txd" style="font-size:10px;letter-spacing:1px;text-transform:uppercase;margin-bottom:4px">Patente</div>
                <div style="font-family:var(--mono);font-size:18px;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:var(--tx)">
                    {{ $vehiculo->patente }}
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="txd" style="font-size:10px;letter-spacing:1px;text-transform:uppercase;margin-bottom:4px">Vehículo</div>
                <div style="font-weight:500;color:var(--tx)">{{ $vehiculo->marca }} {{ $vehiculo->modelo }}</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="txd" style="font-size:10px;letter-spacing:1px;text-transform:uppercase;margin-bottom:4px">Cliente</div>
                <div style="font-weight:500;color:var(--tx)">{{ $vehiculo->cliente->nombre ?? '—' }}</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="txd" style="font-size:10px;letter-spacing:1px;text-transform:uppercase;margin-bottom:4px">Fecha de ploteo</div>
                <div>{{ $vehiculo->fecha_ploteo ? $vehiculo->fecha_ploteo->isoFormat('D MMM YYYY') : '—' }}</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="txd" style="font-size:10px;letter-spacing:1px;text-transform:uppercase;margin-bottom:4px">Tipo de ploteo</div>
                <div style="font-weight:500;color:var(--tx)">
                    @if($vehiculo->tipo_ploteo === 'completo')
                        <span style="color:var(--green)">● Completo</span>
                    @else
                        <span style="color:var(--amber)">● Parcial</span>
                        @if($vehiculo->sector)
                            — {{ \App\Models\VehiculoPloteo::sectores()[$vehiculo->sector] ?? $vehiculo->sector }}
                        @endif
                    @endif
                </div>
            </div>
            @if($vehiculo->observaciones)
            <div class="col-12 col-md-6">
                <div class="txd" style="font-size:10px;letter-spacing:1px;text-transform:uppercase;margin-bottom:4px">Observaciones</div>
                <div>{{ $vehiculo->observaciones }}</div>
            </div>
            @endif
        </div>
        {{-- Referencia --}}
        @if($vehiculo->refe)
        @php $ext = pathinfo($vehiculo->refe, PATHINFO_EXTENSION); @endphp
        <div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--b)">
            <div class="txd" style="font-size:10px;letter-spacing:1px;text-transform:uppercase;margin-bottom:10px">Referencia (Refe)</div>
            @if(in_array(strtolower($ext), ['jpg','jpeg','png','webp','gif']))
                <img src="{{ Storage::disk('public')->url($vehiculo->refe) }}"
                     style="max-width:320px;border-radius:8px;border:1px solid #1e1e1e;cursor:zoom-in"
                     onclick="openLightbox(this.src)">
            @else
                <a href="{{ Storage::disk('public')->url($vehiculo->refe) }}" target="_blank"
                   class="gbtn gbtn-ghost gbtn-sm">
                    📄 Ver referencia ({{ strtoupper($ext) }})
                </a>
            @endif
            <form method="POST" action="{{ route('vehiculos-ploteo.destroy-foto', $vehiculo->id) }}"
                  style="display:inline-block;margin-left:8px">
                @csrf @method('DELETE')
                <input type="hidden" name="campo" value="refe">
                <button type="submit" class="gbtn gbtn-danger gbtn-xs"
                        onclick="return confirm('¿Eliminar referencia?')">× Quitar</button>
            </form>
        </div>
        @endif
    </div>
</div>

{{-- Fotos antes --}}
<div class="gcard mb-3">
    <div class="gcard-hd"><span class="gcard-title">Antes de plotear</span></div>
    <div class="gcard-bd">
        <div class="foto-grid">
            @foreach(['foto_antes_frente'=>'Frente','foto_antes_atras'=>'Atrás','foto_antes_izq'=>'Izquierda','foto_antes_der'=>'Derecha'] as $campo => $label)
            <div class="foto-card">
                @if($vehiculo->$campo)
                    <img src="{{ Storage::disk('public')->url($vehiculo->$campo) }}"
                         alt="{{ $label }}" onclick="openLightbox(this.src)">
                    <div class="foto-label">{{ $label }}</div>
                    <form method="POST" action="{{ route('vehiculos-ploteo.destroy-foto', $vehiculo->id) }}">
                        @csrf @method('DELETE')
                        <input type="hidden" name="campo" value="{{ $campo }}">
                        <button type="submit" class="foto-del" title="Eliminar"
                                onclick="return confirm('¿Eliminar esta foto?')">×</button>
                    </form>
                @else
                    <div class="foto-empty">📷<span>{{ $label }}</span></div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Fotos después --}}
<div class="gcard mb-3">
    <div class="gcard-hd"><span class="gcard-title">Después de plotear</span></div>
    <div class="gcard-bd">
        <div class="foto-grid">
            @foreach(['foto_despues_frente'=>'Frente','foto_despues_atras'=>'Atrás','foto_despues_izq'=>'Izquierda','foto_despues_der'=>'Derecha'] as $campo => $label)
            <div class="foto-card">
                @if($vehiculo->$campo)
                    <img src="{{ Storage::disk('public')->url($vehiculo->$campo) }}"
                         alt="{{ $label }}" onclick="openLightbox(this.src)">
                    <div class="foto-label">{{ $label }}</div>
                    <form method="POST" action="{{ route('vehiculos-ploteo.destroy-foto', $vehiculo->id) }}">
                        @csrf @method('DELETE')
                        <input type="hidden" name="campo" value="{{ $campo }}">
                        <button type="submit" class="foto-del" title="Eliminar"
                                onclick="return confirm('¿Eliminar esta foto?')">×</button>
                    </form>
                @else
                    <div class="foto-empty">📷<span>{{ $label }}</span></div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Danger zone --}}
<div style="text-align:right;margin-top:8px">
    <form method="POST" action="{{ route('vehiculos-ploteo.destroy', $vehiculo->id) }}"
          onsubmit="return confirm('¿Eliminar este vehículo?')">
        @csrf @method('DELETE')
        <button type="submit" class="gbtn gbtn-danger gbtn-sm">Eliminar vehículo</button>
    </form>
</div>

{{-- Lightbox --}}
<div id="lightbox" onclick="closeLightbox()">
    <img id="lightbox-img" src="" alt="">
</div>
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
document.addEventListener('keydown', function(e){ if(e.key==='Escape') closeLightbox(); });
</script>
@endsection

@extends('layouts.app')

@section('page-title', $vehiculo->patente . ' — ' . $vehiculo->marca . ' ' . $vehiculo->modelo)

@section('topbar-actions')
    <div style="display:flex;gap:8px">
        @if(auth()->user()->puedeModulo('presupuestos'))
            @if($vehiculo->presupuestado())
                @if($vehiculo->presupuesto_id)
                    <a href="{{ route('presupuestos.show', $vehiculo->presupuesto_id) }}" class="gbtn gbtn-ghost gbtn-sm"
                       style="color:#3fb96a;border-color:#1c3a29"
                       title="Ver presupuesto">✓ Presupuestado{{ $vehiculo->presupuesto ? ' · '.$vehiculo->presupuesto->numeroFormateado() : '' }}</a>
                @else
                    <span class="gbtn gbtn-ghost gbtn-sm" style="color:#3fb96a;border-color:#1c3a29;cursor:default">✓ Presupuestado (manual)</span>
                @endif
                <form method="POST" action="{{ route('vehiculos-ploteo.desmarcar-presupuestado', $vehiculo->id) }}" style="display:inline"
                      onsubmit="return confirm('¿Quitar la marca de presupuestado?')">
                    @csrf @method('DELETE')
                    <button class="gbtn gbtn-ghost gbtn-sm" title="Quitar la marca de presupuestado">✕ Deshacer</button>
                </form>
            @else
                <form method="POST" action="{{ route('presupuestos.desde-vehiculos') }}" style="display:inline"
                      @unless($vehiculo->cliente_id) onsubmit="alert('Asigná un cliente al vehículo antes de presupuestar.'); return false;" @endunless>
                    @csrf
                    <input type="hidden" name="vehiculo_ids[]" value="{{ $vehiculo->id }}">
                    <button class="gbtn gbtn-primary gbtn-sm" title="Crear presupuesto con este vehículo">⚡ Presupuestar</button>
                </form>
                <form method="POST" action="{{ route('vehiculos-ploteo.marcar-presupuestado', $vehiculo->id) }}" style="display:inline">
                    @csrf
                    <button class="gbtn gbtn-ghost gbtn-sm" title="Marcar como presupuestado sin crear uno (para casos ya presupuestados aparte)">✓ Marcar presupuestado</button>
                </form>
            @endif
        @endif
        @include('vehiculos-ploteo._terminado-boton', ['v' => $vehiculo])
        <a href="{{ route('vehiculos-ploteo.print', $vehiculo->id) }}" target="_blank"
           class="gbtn gbtn-ghost gbtn-sm" title="Ficha para el taller (A4)">🖨 Imprimir</a>
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

/* Referencias: grilla que en el teléfono baja a 2 columnas */
.ref-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(150px,1fr)); gap:12px; }
@media(max-width:640px){ .ref-grid { grid-template-columns:repeat(2,1fr); } }
.ref-img { width:100%; aspect-ratio:4/3; object-fit:cover; display:block; border-radius:8px;
           border:1px solid var(--bm); background:#0d0d0d; cursor:zoom-in; }
.ref-doc { display:flex; flex-direction:column; align-items:center; justify-content:center; gap:3px;
           width:100%; aspect-ratio:4/3; border-radius:8px; border:1px solid var(--bm);
           background:#0d0d0d; text-decoration:none; }
.ref-doc-ext { font-family:var(--mono); font-size:17px; font-weight:700; color:var(--ac); letter-spacing:1px; }
.ref-doc-txt { font-size:10px; color:var(--txd); }
.ref-nombre { font-size:11px; color:var(--txd); margin-top:4px; line-height:1.3;
              overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.ref-peso { font-family:var(--mono); font-size:9.5px; color:#555; }

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
            @if($vehiculo->presupuesto_id && $vehiculo->presupuesto)
            <div class="col-6 col-md-3">
                <div class="txd" style="font-size:10px;letter-spacing:1px;text-transform:uppercase;margin-bottom:4px">Presupuesto</div>
                <a href="{{ route('presupuestos.show', $vehiculo->presupuesto_id) }}"
                   style="color:var(--ac);font-family:var(--mono)">{{ $vehiculo->presupuesto->numeroFormateado() }}</a>
            </div>
            @endif

            <div class="col-6 col-md-3">
                <div class="txd" style="font-size:10px;letter-spacing:1px;text-transform:uppercase;margin-bottom:4px">Estado</div>
                <div>
                    @if($vehiculo->terminado())
                        <span style="color:#3fb96a;font-weight:600">✓ Terminado</span>
                        <div class="txd" style="font-size:11px">{{ $vehiculo->terminado_at->format('d/m/Y H:i') }}</div>
                    @else
                        <span style="color:#d9a441;font-weight:600">○ Pendiente</span>
                    @endif
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="txd" style="font-size:10px;letter-spacing:1px;text-transform:uppercase;margin-bottom:4px">Colocador</div>
                <div style="font-weight:500;color:{{ $vehiculo->instalador_id ? 'var(--tx)' : 'var(--txm)' }}">
                    {{ $vehiculo->instalador->name ?? 'Sin asignar' }}
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="txd" style="font-size:10px;letter-spacing:1px;text-transform:uppercase;margin-bottom:4px">Cargado</div>
                <div>{{ $vehiculo->created_at?->format('d/m/Y H:i') ?? '—' }}</div>
            </div>

            @if($vehiculo->observaciones)
            <div class="col-12 col-md-6">
                <div class="txd" style="font-size:10px;letter-spacing:1px;text-transform:uppercase;margin-bottom:4px">Observaciones</div>
                <div>{{ $vehiculo->observaciones }}</div>
            </div>
            @endif
        </div>
        {{-- Referencias: lo primero que mira el que plotea --}}
        @php $refs = $vehiculo->referencias; @endphp
        @if($refs->isNotEmpty() || $vehiculo->refe)
        <div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--b)">
            <div class="txd" style="font-size:10px;letter-spacing:1px;text-transform:uppercase;margin-bottom:10px">
                Referencias ({{ $refs->count() + ($vehiculo->refe && $refs->isEmpty() ? 1 : 0) }})
            </div>
            <div class="ref-grid">
                @foreach($refs as $ref)
                <div class="ref-item">
                    @if($ref->es_imagen)
                        <img src="{{ $ref->url }}" alt="{{ $ref->nombre_original }}"
                             class="ref-img" onclick="openLightbox(this.src)">
                    @else
                        <a href="{{ $ref->url }}" target="_blank" class="ref-doc">
                            <span class="ref-doc-ext">{{ strtoupper($ref->extension) ?: 'ARCH' }}</span>
                            <span class="ref-doc-txt">abrir</span>
                        </a>
                    @endif
                    <div class="ref-nombre" title="{{ $ref->nombre_original }}">{{ $ref->nombre_original }}</div>
                    <div class="ref-peso">{{ $ref->tamanio_formateado }}</div>
                    <form method="POST" action="{{ route('vehiculos-ploteo.archivo-destroy', $ref->id) }}" class="no-print">
                        @csrf @method('DELETE')
                        <button type="submit" class="gbtn gbtn-danger gbtn-xs" style="width:100%;margin-top:3px"
                                onclick="return confirm('¿Eliminar esta referencia?')">×</button>
                    </form>
                </div>
                @endforeach

                {{-- Refe vieja (columna única) por si quedó alguna sin migrar --}}
                @if($vehiculo->refe && $refs->isEmpty())
                @php $extRefe = strtolower(pathinfo($vehiculo->refe, PATHINFO_EXTENSION)); @endphp
                <div class="ref-item">
                    @if(in_array($extRefe, ['jpg','jpeg','png','webp','gif']))
                        <img src="{{ route('vehiculos-ploteo.foto', [$vehiculo->id, 'refe']) }}"
                             class="ref-img" onclick="openLightbox(this.src)">
                    @else
                        <a href="{{ route('vehiculos-ploteo.foto', [$vehiculo->id, 'refe']) }}" target="_blank" class="ref-doc">
                            <span class="ref-doc-ext">{{ strtoupper($extRefe) ?: 'ARCH' }}</span>
                            <span class="ref-doc-txt">abrir</span>
                        </a>
                    @endif
                    <div class="ref-nombre">Referencia</div>
                </div>
                @endif
            </div>
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
                    <img src="{{ route('vehiculos-ploteo.foto', [$vehiculo->id, $campo]) }}"
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
                    <img src="{{ route('vehiculos-ploteo.foto', [$vehiculo->id, $campo]) }}"
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

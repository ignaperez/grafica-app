@extends('layouts.app')

@section('page-title', 'Editar — ' . $vehiculo->patente)

@section('topbar-actions')
    <a href="{{ route('vehiculos-ploteo.show', $vehiculo->id) }}" class="gbtn gbtn-ghost gbtn-sm">← Volver</a>
@endsection

@section('content')
<form method="POST" action="{{ route('vehiculos-ploteo.update', $vehiculo->id) }}" enctype="multipart/form-data">
@csrf @method('PUT')

<div class="row g-3">
    <div class="col-lg-8">
        <div class="gcard mb-3">
            <div class="gcard-hd"><span class="gcard-title">Datos del vehículo</span></div>
            <div class="gcard-bd">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="gfg">
                            <label class="glabel">Patente *</label>
                            <input type="text" name="patente" id="veh-patente" class="ginput"
                                   value="{{ old('patente', $vehiculo->patente) }}"
                                   oninput="normPatente(this)" data-ignore="{{ $vehiculo->id }}"
                                   style="text-transform:uppercase;letter-spacing:2px;font-family:var(--mono)"
                                   required>
                            @error('patente')<div class="gerr">{{ $message }}</div>@enderror
                            <div id="patente-aviso" style="display:none;margin-top:6px;font-size:12px;color:#e0960a"></div>
                        </div>
                    </div>
                    @include('vehiculos-ploteo._marca-modelo-fields')
                    <div class="col-md-4">
                        <div class="gfg">
                            <label class="glabel">Fecha de ploteo</label>
                            <input type="date" name="fecha_ploteo" class="ginput"
                                   value="{{ old('fecha_ploteo', $vehiculo->fecha_ploteo?->format('Y-m-d')) }}">
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="gfg">
                            <label class="glabel">Observaciones</label>
                            <input type="text" name="observaciones" class="ginput"
                                   value="{{ old('observaciones', $vehiculo->observaciones) }}">
                        </div>
                    </div>
                    {{-- Tipo de ploteo --}}
                    <div class="col-12">
                        <div class="gfg mb-0">
                            <label class="glabel">Tipo de ploteo *</label>
                            <div style="display:flex;gap:12px;margin-top:4px">
                                @foreach(['completo'=>'Ploteo completo','parcial'=>'Parcial'] as $val => $lbl)
                                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:var(--tx)">
                                    <input type="radio" name="tipo_ploteo" value="{{ $val }}"
                                           {{ old('tipo_ploteo', $vehiculo->tipo_ploteo) === $val ? 'checked' : '' }}
                                           onchange="toggleSector(this.value)"
                                           style="accent-color:var(--ac)">
                                    {{ $lbl }}
                                </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6" id="sector-wrap"
                         style="{{ old('tipo_ploteo', $vehiculo->tipo_ploteo) === 'parcial' ? '' : 'display:none' }}">
                        <div class="gfg mb-0">
                            <label class="glabel">Sector</label>
                            <select name="sector" class="gselect">
                                <option value="">— Seleccioná —</option>
                                @foreach(\App\Models\VehiculoPloteo::sectores() as $val => $lbl)
                                    <option value="{{ $val }}"
                                        {{ old('sector', $vehiculo->sector) === $val ? 'selected' : '' }}>
                                        {{ $lbl }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @foreach([
            'antes'   => ['foto_antes_frente'=>'Frente','foto_antes_atras'=>'Atrás','foto_antes_izq'=>'Izquierda','foto_antes_der'=>'Derecha'],
            'después' => ['foto_despues_frente'=>'Frente','foto_despues_atras'=>'Atrás','foto_despues_izq'=>'Izquierda','foto_despues_der'=>'Derecha'],
        ] as $momento => $fotos)
        <div class="gcard mb-3">
            <div class="gcard-hd"><span class="gcard-title">Fotos — {{ ucfirst($momento) }} de plotear</span></div>
            <div class="gcard-bd">
                <div class="row g-3">
                    @foreach($fotos as $campo => $label)
                    <div class="col-6 col-md-3">
                        <div class="gfg mb-0">
                            <label class="glabel">{{ $label }}</label>
                            @if($vehiculo->$campo)
                                <img src="{{ route('vehiculos-ploteo.foto', [$vehiculo->id, $campo]) }}"
                                     style="width:100%;border-radius:8px;border:1px solid #1e1e1e;
                                            object-fit:cover;height:90px;margin-bottom:6px">
                            @endif
                            <label class="foto-drop" for="{{ $campo }}">
                                <span class="foto-drop-icon">{{ $vehiculo->$campo ? '🔄' : '📷' }}</span>
                                <span class="foto-drop-txt">{{ $vehiculo->$campo ? 'Reemplazar' : 'Subir foto' }}</span>
                                <input type="file" id="{{ $campo }}" name="{{ $campo }}"
                                       accept="image/*" class="foto-input" data-preview="{{ $campo }}-prev">
                            </label>
                            <img id="{{ $campo }}-prev" class="foto-preview" style="display:none">
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="col-lg-4">
        <div class="gcard mb-3">
            <div class="gcard-hd"><span class="gcard-title">Cliente</span></div>
            <div class="gcard-bd">
                <div class="gfg mb-0">
                    <label class="glabel">Cliente *</label>
                    <select name="cliente_id" class="gselect select2-cliente" required>
                        <option value="">Seleccioná un cliente…</option>
                        @foreach($clientes as $c)
                            <option value="{{ $c->id }}"
                                {{ old('cliente_id', $vehiculo->cliente_id) == $c->id ? 'selected' : '' }}>
                                {{ $c->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('cliente_id')<div class="gerr">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <div class="gcard mb-3">
            <div class="gcard-hd"><span class="gcard-title">Orden de trabajo</span></div>
            <div class="gcard-bd">
                <div class="gfg mb-0">
                    <label class="glabel">Asignar a orden (opcional)</label>
                    <select name="orden_trabajo_id" class="gselect select2-orden">
                        <option value="">Sin orden</option>
                        @foreach($ordenes as $o)
                            <option value="{{ $o->id }}"
                                {{ old('orden_trabajo_id', $vehiculo->orden_trabajo_id) == $o->id ? 'selected' : '' }}>
                                #{{ str_pad($o->id,4,'0',STR_PAD_LEFT) }} — {{ $o->cliente->nombre ?? 'Sin cliente' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="gcard mb-3">
            <div class="gcard-hd"><span class="gcard-title">Referencias</span></div>
            <div class="gcard-bd">
                @if($vehiculo->referencias->isNotEmpty())
                <div style="margin-bottom:12px">
                    <div class="txd" style="font-size:10px;letter-spacing:1px;text-transform:uppercase;margin-bottom:8px">
                        Ya cargadas ({{ $vehiculo->referencias->count() }})
                    </div>
                    <div style="display:flex;flex-wrap:wrap;gap:8px">
                        @foreach($vehiculo->referencias as $ref)
                        <div style="width:104px">
                            <a href="{{ $ref->url }}" target="_blank" style="text-decoration:none">
                                <div style="border:1px solid var(--bm);border-radius:8px;overflow:hidden;background:#0d0d0d">
                                    @if($ref->es_imagen)
                                        <img src="{{ $ref->url }}" style="width:100%;height:76px;object-fit:cover;display:block">
                                    @else
                                        <div style="height:76px;display:flex;align-items:center;justify-content:center;
                                                    font-family:var(--mono);color:var(--ac);font-size:13px;font-weight:700">
                                            {{ strtoupper($ref->extension) ?: 'ARCH' }}
                                        </div>
                                    @endif
                                </div>
                            </a>
                            <form method="POST" action="{{ route('vehiculos-ploteo.archivo-destroy', $ref->id) }}" style="margin-top:3px">
                                @csrf @method('DELETE')
                                <button type="submit" class="gbtn gbtn-danger gbtn-xs" style="width:100%"
                                        onclick="return confirm('¿Eliminar esta referencia?')">× Quitar</button>
                            </form>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="gfg mb-0">
                    <label class="glabel">Agregar imágenes o PDF (podés elegir varias)</label>
                    <label class="foto-drop" for="referencias" style="min-height:70px">
                        <span class="foto-drop-icon">📄</span>
                        <span class="foto-drop-txt" id="referencias-txt">Subir imágenes / PDF</span>
                        <input type="file" id="referencias" name="referencias[]" multiple accept="image/*,.pdf"
                               onchange="document.getElementById('referencias-txt').textContent =
                                   this.files.length ? this.files.length + ' archivo(s) seleccionado(s)' : 'Subir imágenes / PDF'">
                    </label>
                </div>
            </div>
        </div>

        <button type="submit" class="gbtn gbtn-primary" style="width:100%">Actualizar</button>
    </div>
</div>
</form>

@include('vehiculos-ploteo._marca-modelo-modals')
@endsection

@section('scripts')
<style>
.foto-drop { display:flex; flex-direction:column; align-items:center; justify-content:center; gap:6px; border:1px dashed #2a2a2a; border-radius:8px; padding:12px 8px; cursor:pointer; transition:border-color .15s,background .15s; background:#0a0a0a; min-height:70px; }
.foto-drop:hover { border-color:var(--ac); background:#110a07; }
.foto-drop-icon { font-size:18px; line-height:1; }
.foto-drop-txt  { font-size:11px; color:#444; text-align:center; }
.foto-input     { display:none; }
.foto-preview   { width:100%; border-radius:8px; margin-top:6px; border:1px solid #1e1e1e; object-fit:cover; height:80px; }
</style>
<script>
document.querySelectorAll('.foto-input').forEach(function(input) {
    input.addEventListener('change', function() {
        var prev = document.getElementById(this.dataset.preview);
        if (this.files && this.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) { prev.src = e.target.result; prev.style.display = 'block'; };
            reader.readAsDataURL(this.files[0]);
        }
    });
});
$('.select2-cliente').select2({ width: 'resolve', placeholder: 'Seleccioná un cliente…' });
$('.select2-orden').select2({ width: 'resolve', placeholder: 'Sin orden' });
function toggleSector(val) {
    document.getElementById('sector-wrap').style.display = val === 'parcial' ? '' : 'none';
}
</script>
@include('vehiculos-ploteo._marca-modelo-js')
@endsection

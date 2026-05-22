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
                            <input type="text" name="patente" class="ginput"
                                   value="{{ old('patente', $vehiculo->patente) }}"
                                   style="text-transform:uppercase;letter-spacing:2px;font-family:var(--mono)"
                                   required>
                            @error('patente')<div class="gerr">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="gfg">
                            <label class="glabel">Marca *</label>
                            <input type="text" name="marca" class="ginput"
                                   value="{{ old('marca', $vehiculo->marca) }}" required>
                            @error('marca')<div class="gerr">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="gfg">
                            <label class="glabel">Modelo *</label>
                            <input type="text" name="modelo" class="ginput"
                                   value="{{ old('modelo', $vehiculo->modelo) }}" required>
                            @error('modelo')<div class="gerr">{{ $message }}</div>@enderror
                        </div>
                    </div>
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
                                <img src="{{ Storage::disk('public')->url($vehiculo->$campo) }}"
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
            <div class="gcard-hd"><span class="gcard-title">Referencia (Refe)</span></div>
            <div class="gcard-bd">
                @if($vehiculo->refe)
                    @php $ext = pathinfo($vehiculo->refe, PATHINFO_EXTENSION); @endphp
                    @if(in_array(strtolower($ext), ['jpg','jpeg','png','webp','gif']))
                        <img src="{{ Storage::disk('public')->url($vehiculo->refe) }}"
                             style="width:100%;border-radius:8px;border:1px solid #1e1e1e;object-fit:cover;max-height:120px;margin-bottom:8px">
                    @else
                        <a href="{{ Storage::disk('public')->url($vehiculo->refe) }}" target="_blank"
                           class="gbtn gbtn-ghost gbtn-sm" style="width:100%;justify-content:center;margin-bottom:8px">
                            📄 Ver referencia actual
                        </a>
                    @endif
                @endif
                <div class="gfg mb-0">
                    <label class="glabel">{{ $vehiculo->refe ? 'Reemplazar' : 'Subir PDF / imagen' }}</label>
                    <label class="foto-drop" for="refe" style="min-height:70px">
                        <span class="foto-drop-icon">📄</span>
                        <span class="foto-drop-txt" id="refe-txt">{{ $vehiculo->refe ? 'Reemplazar archivo' : 'Subir PDF / imagen' }}</span>
                        <input type="file" id="refe" name="refe" accept="image/*,.pdf"
                               onchange="document.getElementById('refe-txt').textContent = this.files[0]?.name ?? 'Subir PDF / imagen'">
                    </label>
                </div>
            </div>
        </div>

        <button type="submit" class="gbtn gbtn-primary" style="width:100%">Actualizar</button>
    </div>
</div>
</form>
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
@endsection

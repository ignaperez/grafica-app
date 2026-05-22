@extends('layouts.app')

@section('page-title', 'Nuevo vehículo')

@section('topbar-actions')
    <a href="{{ route('vehiculos-ploteo.index') }}" class="gbtn gbtn-ghost gbtn-sm">← Volver</a>
@endsection

@section('content')
<form method="POST" action="{{ route('vehiculos-ploteo.store') }}" enctype="multipart/form-data">
@csrf

<div class="row g-3">
    {{-- Datos del vehículo --}}
    <div class="col-lg-8">
        <div class="gcard mb-3">
            <div class="gcard-hd"><span class="gcard-title">Datos del vehículo</span></div>
            <div class="gcard-bd">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="gfg">
                            <label class="glabel">Patente *</label>
                            <input type="text" name="patente" class="ginput"
                                   value="{{ old('patente') }}"
                                   placeholder="AB 123 CD"
                                   style="text-transform:uppercase;letter-spacing:2px;font-family:var(--mono)"
                                   required>
                            @error('patente')<div class="gerr">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="gfg">
                            <label class="glabel">Marca *</label>
                            <input type="text" name="marca" class="ginput"
                                   value="{{ old('marca') }}" placeholder="Ford, Chevrolet…" required>
                            @error('marca')<div class="gerr">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="gfg">
                            <label class="glabel">Modelo *</label>
                            <input type="text" name="modelo" class="ginput"
                                   value="{{ old('modelo') }}" placeholder="Transit, S10…" required>
                            @error('modelo')<div class="gerr">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="gfg">
                            <label class="glabel">Fecha de ploteo</label>
                            <input type="date" name="fecha_ploteo" class="ginput"
                                   value="{{ old('fecha_ploteo', date('Y-m-d')) }}">
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="gfg">
                            <label class="glabel">Observaciones</label>
                            <input type="text" name="observaciones" class="ginput"
                                   value="{{ old('observaciones') }}"
                                   placeholder="Vinilo blanco, corte…">
                        </div>
                    </div>
                    {{-- Tipo de ploteo --}}
                    <div class="col-12">
                        <div class="gfg mb-0">
                            <label class="glabel">Tipo de ploteo *</label>
                            <div style="display:flex;gap:12px;margin-top:4px">
                                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:var(--tx)">
                                    <input type="radio" name="tipo_ploteo" value="completo"
                                           {{ old('tipo_ploteo','completo') === 'completo' ? 'checked' : '' }}
                                           onchange="toggleSector(this.value)"
                                           style="accent-color:var(--ac)">
                                    Ploteo completo
                                </label>
                                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:var(--tx)">
                                    <input type="radio" name="tipo_ploteo" value="parcial"
                                           {{ old('tipo_ploteo') === 'parcial' ? 'checked' : '' }}
                                           onchange="toggleSector(this.value)"
                                           style="accent-color:var(--ac)">
                                    Parcial
                                </label>
                            </div>
                        </div>
                    </div>
                    {{-- Sector (visible solo si parcial) --}}
                    <div class="col-md-6" id="sector-wrap" style="{{ old('tipo_ploteo') === 'parcial' ? '' : 'display:none' }}">
                        <div class="gfg mb-0">
                            <label class="glabel">Sector</label>
                            <select name="sector" class="gselect">
                                <option value="">— Seleccioná —</option>
                                @foreach(\App\Models\VehiculoPloteo::sectores() as $val => $lbl)
                                    <option value="{{ $val }}" {{ old('sector') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Fotos antes --}}
        <div class="gcard mb-3">
            <div class="gcard-hd"><span class="gcard-title">Fotos — Antes de plotear</span></div>
            <div class="gcard-bd">
                <div class="row g-3">
                    @foreach(['foto_antes_frente'=>'Frente','foto_antes_atras'=>'Atrás','foto_antes_izq'=>'Izquierda','foto_antes_der'=>'Derecha'] as $campo => $label)
                    <div class="col-6 col-md-3">
                        <div class="gfg mb-0">
                            <label class="glabel">{{ $label }}</label>
                            <label class="foto-drop" for="{{ $campo }}">
                                <span class="foto-drop-icon">📷</span>
                                <span class="foto-drop-txt">Subir foto</span>
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

        {{-- Fotos después --}}
        <div class="gcard mb-3">
            <div class="gcard-hd"><span class="gcard-title">Fotos — Después de plotear</span></div>
            <div class="gcard-bd">
                <div class="row g-3">
                    @foreach(['foto_despues_frente'=>'Frente','foto_despues_atras'=>'Atrás','foto_despues_izq'=>'Izquierda','foto_despues_der'=>'Derecha'] as $campo => $label)
                    <div class="col-6 col-md-3">
                        <div class="gfg mb-0">
                            <label class="glabel">{{ $label }}</label>
                            <label class="foto-drop" for="{{ $campo }}">
                                <span class="foto-drop-icon">📷</span>
                                <span class="foto-drop-txt">Subir foto</span>
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
    </div>

    {{-- Sidebar: cliente + orden --}}
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
                                {{ old('cliente_id', $orden?->cliente_id) == $c->id ? 'selected' : '' }}>
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
                                {{ (old('orden_trabajo_id', $orden?->id) == $o->id) ? 'selected' : '' }}>
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
                <div class="gfg mb-0">
                    <label class="glabel">PDF o imagen con las vistas</label>
                    <label class="foto-drop" for="refe" style="min-height:80px">
                        <span class="foto-drop-icon">📄</span>
                        <span class="foto-drop-txt" id="refe-txt">Subir PDF / imagen</span>
                        <input type="file" id="refe" name="refe"
                               accept="image/*,.pdf"
                               onchange="document.getElementById('refe-txt').textContent = this.files[0]?.name ?? 'Subir PDF / imagen'">
                    </label>
                </div>
            </div>
        </div>

        <button type="submit" class="gbtn gbtn-primary" style="width:100%">Guardar vehículo</button>
    </div>
</div>
</form>
@endsection

@section('scripts')
<style>
.foto-drop {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 6px;
    border: 1px dashed #2a2a2a;
    border-radius: 8px;
    padding: 16px 8px;
    cursor: pointer;
    transition: border-color .15s, background .15s;
    background: #0a0a0a;
    min-height: 90px;
}
.foto-drop:hover { border-color: var(--ac); background: #110a07; }
.foto-drop-icon { font-size: 22px; line-height:1; }
.foto-drop-txt  { font-size: 11px; color: #444; text-align:center; }
.foto-input     { display: none; }
.foto-preview   { width:100%; border-radius:8px; margin-top:8px; border:1px solid #1e1e1e; object-fit:cover; height:90px; }
</style>
<script>
document.querySelectorAll('.foto-input').forEach(function(input) {
    input.addEventListener('change', function() {
        var prev = document.getElementById(this.dataset.preview);
        if (this.files && this.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                prev.src = e.target.result;
                prev.style.display = 'block';
            };
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

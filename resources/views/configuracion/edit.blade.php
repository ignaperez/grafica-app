@extends('layouts.app')

@section('page-title', 'Configuración del sistema')

@section('content')

<form action="{{ route('configuracion.update') }}" method="POST" enctype="multipart/form-data" style="width:100%;min-width:0">
@csrf
@method('PUT')

{{-- ── Logo ── --}}
<div class="gcard" style="max-width:680px;margin-bottom:16px;overflow:hidden">
    <div class="gcard-hd">
        <span class="gcard-title">Logo de la empresa</span>
    </div>
    <div class="gcard-bd" style="display:flex;align-items:center;gap:20px;flex-wrap:wrap;min-width:0">
        <div style="width:120px;height:72px;background:#0a0a0a;border:1px solid var(--b);border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;overflow:hidden">
            @if($empresa['logo'] && \Illuminate\Support\Facades\Storage::disk('public')->exists($empresa['logo']))
                <img id="logo-preview" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($empresa['logo']) }}"
                     style="max-width:112px;max-height:64px;width:auto;height:auto;display:block">
            @else
                <img id="logo-preview" src="" style="max-width:112px;max-height:64px;display:none">
                <span id="logo-placeholder" style="font-size:11px;color:var(--txd)">Sin logo</span>
            @endif
        </div>
        <div style="flex:1;min-width:0">
            <div class="gfg" style="margin-bottom:6px">
                <label class="glabel">Subir nuevo logo</label>
                <input type="file" name="empresa_logo" id="empresa_logo" accept="image/*"
                       class="ginput" style="padding:6px 10px;cursor:pointer;width:100%;min-width:0">
                @error('empresa_logo')<div class="gerr">{{ $message }}</div>@enderror
            </div>
            <div class="txd" style="font-size:11px;line-height:1.5">
                PNG, JPG, SVG o WebP — máx. 2 MB.<br>
                Recomendado: fondo transparente, proporción horizontal.
            </div>
        </div>
    </div>
</div>

{{-- ── Datos de la empresa (read-only — administrados por super-admin) ── --}}
<div class="gcard" style="max-width:680px;margin-bottom:16px">
    <div class="gcard-hd">
        <span class="gcard-title">Datos de la empresa</span>
        <span class="txd" style="font-size:11px">Administrados por el sistema — contactá al administrador para modificarlos</span>
    </div>
    <div class="gcard-bd">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">

            <div class="gfg" style="grid-column:1/-1">
                <label class="glabel">Razón Social</label>
                <div class="ginput" style="opacity:.6;cursor:default;background:#0a0a0a">{{ $empresa['nombre'] ?: '—' }}</div>
            </div>

            <div class="gfg">
                <label class="glabel">CUIT</label>
                <div class="ginput" style="opacity:.6;cursor:default;background:#0a0a0a">{{ $empresa['cuit'] ?: '—' }}</div>
            </div>

            <div class="gfg">
                <label class="glabel">E-mail</label>
                <div class="ginput" style="opacity:.6;cursor:default;background:#0a0a0a">{{ $empresa['email'] ?: '—' }}</div>
            </div>

            <div class="gfg" style="grid-column:1/-1">
                <label class="glabel">Dirección</label>
                <div class="ginput" style="opacity:.6;cursor:default;background:#0a0a0a">{{ $empresa['direccion'] ?: '—' }}</div>
            </div>

            <div class="gfg">
                <label class="glabel">Teléfono</label>
                <div class="ginput" style="opacity:.6;cursor:default;background:#0a0a0a">{{ $empresa['telefono'] ?: '—' }}</div>
            </div>

        </div>
    </div>
</div>

{{-- ── Datos fiscales y adicionales (editables) ── --}}
<div class="gcard" style="max-width:680px;margin-bottom:16px">
    <div class="gcard-hd">
        <span class="gcard-title">Datos fiscales y adicionales</span>
    </div>
    <div class="gcard-bd">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">

            <div class="gfg" style="grid-column:1/-1">
                <label class="glabel">Propietario / Responsable</label>
                <input type="text" name="empresa_propietario" class="ginput" maxlength="120"
                       value="{{ old('empresa_propietario', $empresa['propietario']) }}"
                       placeholder="Nombre y apellido">
            </div>

            <div class="gfg" style="grid-column:1/-1">
                <label class="glabel">Condición frente al IVA</label>
                <select name="empresa_condicion_iva" class="ginput">
                    <option value="">— Sin especificar —</option>
                    @foreach([
                        'responsable_inscripto' => 'IVA Responsable Inscripto',
                        'monotributo'           => 'Monotributista',
                        'exento'                => 'IVA Exento',
                        'consumidor_final'      => 'Consumidor Final',
                    ] as $val => $label)
                        <option value="{{ $val }}" {{ old('empresa_condicion_iva', $empresa['condicion_iva']) === $val ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                <div class="txd" style="font-size:11px;margin-top:4px">Aparece en el encabezado de las facturas.</div>
            </div>

            <div class="gfg">
                <label class="glabel">Ingresos Brutos</label>
                <input type="text" name="empresa_iibb" class="ginput" maxlength="60"
                       value="{{ old('empresa_iibb', $empresa['iibb']) }}"
                       placeholder="20-12345678-9 ó Convenio Multilateral">
            </div>

            <div class="gfg">
                <label class="glabel">Inicio de Actividades</label>
                <input type="text" name="empresa_inicio_actividades" class="ginput" maxlength="20"
                       value="{{ old('empresa_inicio_actividades', $empresa['inicio_actividades']) }}"
                       placeholder="01/01/2020">
            </div>

        </div>
    </div>
</div>

{{-- ── Mano de obra ── --}}
<div class="gcard" style="max-width:680px;margin-bottom:16px">
    <div class="gcard-hd">
        <span class="gcard-title">Mano de obra — valores globales</span>
    </div>
    <div class="gcard-bd">
        <div class="txd" style="font-size:12px;margin-bottom:16px;line-height:1.6">
            Tarifa por defecto de <strong style="color:var(--tx)">colocación / instalación</strong>.
            Se aplica a todos los servicios del catálogo.<br>
            Cada <strong style="color:var(--tx)">lista de precios</strong> puede pisarla con un valor distinto.
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px">
            <div class="gfg">
                <label class="glabel">Por m²</label>
                <div style="position:relative">
                    <span style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--txd)">$</span>
                    <input type="number" name="mo_m2" step="0.01" min="0"
                           class="ginput" style="padding-left:22px"
                           value="{{ old('mo_m2', $mo['m2']) }}">
                </div>
                @error('mo_m2')<div class="gerr">{{ $message }}</div>@enderror
            </div>
            <div class="gfg">
                <label class="glabel">Por ml</label>
                <div style="position:relative">
                    <span style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--txd)">$</span>
                    <input type="number" name="mo_ml" step="0.01" min="0"
                           class="ginput" style="padding-left:22px"
                           value="{{ old('mo_ml', $mo['ml']) }}">
                </div>
            </div>
            <div class="gfg">
                <label class="glabel">Por unidad</label>
                <div style="position:relative">
                    <span style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--txd)">$</span>
                    <input type="number" name="mo_unidad" step="0.01" min="0"
                           class="ginput" style="padding-left:22px"
                           value="{{ old('mo_unidad', $mo['unidad']) }}">
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Guardar ── --}}
<div style="max-width:680px;text-align:right;margin-bottom:32px">
    <button type="submit" class="gbtn gbtn-primary">Guardar configuración</button>
</div>

</form>

@endsection

@section('scripts')
<script>
document.getElementById('empresa_logo').addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function (e) {
        const img = document.getElementById('logo-preview');
        const placeholder = document.getElementById('logo-placeholder');
        img.src = e.target.result;
        img.style.display = 'block';
        if (placeholder) placeholder.style.display = 'none';
    };
    reader.readAsDataURL(file);
});
</script>
@endsection

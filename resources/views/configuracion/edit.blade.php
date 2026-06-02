@extends('layouts.app')

@section('page-title', 'Configuración del sistema')

@section('content')

<form action="{{ route('configuracion.update') }}" method="POST">
@csrf
@method('PUT')

{{-- ── Datos de la empresa ── --}}
<div class="gcard" style="max-width:680px;margin-bottom:16px">
    <div class="gcard-hd">
        <span class="gcard-title">Datos de la empresa</span>
    </div>
    <div class="gcard-bd">
        <div class="txd" style="font-size:12px;margin-bottom:16px;line-height:1.6">
            Esta información aparece en el encabezado de los <strong style="color:var(--tx)">presupuestos impresos</strong>.
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">

            <div class="gfg" style="grid-column:1/-1">
                <label class="glabel">Nombre de la empresa *</label>
                <input type="text" name="empresa_nombre" class="ginput" maxlength="120"
                       value="{{ old('empresa_nombre', $empresa['nombre']) }}"
                       placeholder="{{ config('app.name') }}">
                @error('empresa_nombre')<div class="gerr">{{ $message }}</div>@enderror
            </div>

            <div class="gfg">
                <label class="glabel">Propietario / Responsable</label>
                <input type="text" name="empresa_propietario" class="ginput" maxlength="120"
                       value="{{ old('empresa_propietario', $empresa['propietario']) }}"
                       placeholder="Nombre y apellido">
            </div>

            <div class="gfg">
                <label class="glabel">CUIT</label>
                <input type="text" name="empresa_cuit" class="ginput" maxlength="30"
                       value="{{ old('empresa_cuit', $empresa['cuit']) }}"
                       placeholder="20-12345678-9">
            </div>

            <div class="gfg" style="grid-column:1/-1">
                <label class="glabel">Dirección</label>
                <input type="text" name="empresa_direccion" class="ginput" maxlength="200"
                       value="{{ old('empresa_direccion', $empresa['direccion']) }}"
                       placeholder="Calle 123, Ciudad, Provincia">
            </div>

            <div class="gfg">
                <label class="glabel">Teléfono</label>
                <input type="text" name="empresa_telefono" class="ginput" maxlength="60"
                       value="{{ old('empresa_telefono', $empresa['telefono']) }}"
                       placeholder="+54 9 11 1234-5678">
            </div>

            <div class="gfg">
                <label class="glabel">E-mail</label>
                <input type="email" name="empresa_email" class="ginput" maxlength="120"
                       value="{{ old('empresa_email', $empresa['email']) }}"
                       placeholder="contacto@miempresa.com">
                @error('empresa_email')<div class="gerr">{{ $message }}</div>@enderror
            </div>

            <div class="gfg">
                <label class="glabel">Condición frente al IVA</label>
                <input type="text" name="empresa_condicion_iva" class="ginput" maxlength="80"
                       value="{{ old('empresa_condicion_iva', $empresa['condicion_iva']) }}"
                       placeholder="Monotributista / IVA Responsable Inscripto / IVA Exento">
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

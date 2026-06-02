@extends('layouts.app')

@section('page-title', 'Cargar nuevo CAI')

@section('content')

<div style="max-width:560px">
<div class="gcard">
    <div class="gcard-hd">
        <span class="gcard-title">Nuevo CAI de remitos</span>
    </div>
    <div class="gcard-bd">

        <form action="{{ route('remito-cais.store') }}" method="POST">
            @csrf

            <div style="background:rgba(255,255,255,.04);border:1px solid var(--bm);border-radius:6px;padding:12px 14px;margin-bottom:20px;font-size:13px;color:var(--txd);line-height:1.6">
                Completá estos datos con los que te entregó ARCA al tramitar la autorización de impresión.
                Una vez cargado, los próximos remitos usarán automáticamente los números de este rango.
            </div>

            <div class="gfg">
                <label class="glabel">Código CAI <span style="color:var(--ac)">*</span></label>
                <input type="text" name="codigo" class="ginput mono @error('codigo') is-invalid @enderror"
                       value="{{ old('codigo') }}"
                       placeholder="Ej: 12345678901234"
                       maxlength="14"
                       style="letter-spacing:.08em;font-size:15px">
                @error('codigo') <div class="gerr">{{ $message }}</div> @enderror
                <div style="font-size:11px;color:var(--txd);margin-top:4px">14 dígitos sin espacios ni guiones</div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div class="gfg">
                    <label class="glabel">Punto de Venta <span style="color:var(--ac)">*</span></label>
                    <input type="number" name="punto_venta" class="ginput @error('punto_venta') is-invalid @enderror"
                           value="{{ old('punto_venta', 1) }}" min="1" max="9999">
                    @error('punto_venta') <div class="gerr">{{ $message }}</div> @enderror
                </div>
                <div class="gfg">
                    <label class="glabel">Vencimiento <span style="color:var(--ac)">*</span></label>
                    <input type="date" name="vencimiento" class="ginput @error('vencimiento') is-invalid @enderror"
                           value="{{ old('vencimiento') }}">
                    @error('vencimiento') <div class="gerr">{{ $message }}</div> @enderror
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div class="gfg">
                    <label class="glabel">Número desde <span style="color:var(--ac)">*</span></label>
                    <input type="number" name="numero_desde" class="ginput @error('numero_desde') is-invalid @enderror"
                           value="{{ old('numero_desde', 1) }}" min="1">
                    @error('numero_desde') <div class="gerr">{{ $message }}</div> @enderror
                </div>
                <div class="gfg">
                    <label class="glabel">Número hasta <span style="color:var(--ac)">*</span></label>
                    <input type="number" name="numero_hasta" class="ginput @error('numero_hasta') is-invalid @enderror"
                           value="{{ old('numero_hasta') }}" min="2">
                    @error('numero_hasta') <div class="gerr">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="gfg">
                <label class="glabel">Notas (opcional)</label>
                <textarea name="notas" class="gtextarea" rows="2"
                          placeholder="Ej: Tramitado el 01/06/2026, usado para remitos de impresión">{{ old('notas') }}</textarea>
            </div>

            <div style="display:flex;gap:10px;margin-top:4px">
                <button type="submit" class="gbtn gbtn-primary">Guardar CAI</button>
                <a href="{{ route('remito-cais.index') }}" class="gbtn gbtn-ghost">Cancelar</a>
            </div>

        </form>

    </div>
</div>
</div>

@endsection

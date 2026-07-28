@extends('layouts.app')

@section('page-title', 'Cargar trabajo(s)')

@section('topbar-actions')
    <a href="{{ route('trabajos-libres.index') }}" class="gbtn gbtn-ghost gbtn-sm">← Volver</a>
@endsection

@section('content')
<style> input[type="date"].ginput { color-scheme: dark; } </style>

@if($errors->any())
<div style="margin-bottom:14px;padding:11px 14px;background:#1f0a0a;border:1px solid #3d1a1a;border-radius:8px;color:#e05555;font-size:13px">
    {{ $errors->first() }}
</div>
@endif

<form method="POST" action="{{ route('trabajos-libres.store') }}" enctype="multipart/form-data" id="form-trabajos">
    @csrf

    {{-- Cliente (uno para todos los ítems) --}}
    <div class="gcard mb-4">
        <div class="gcard-hd"><span class="gcard-title">Cliente</span></div>
        <div class="gcard-bd">
            <div class="gfg" style="margin:0;max-width:420px">
                <label class="glabel">Cliente *</label>
                <div style="display:flex;gap:8px;align-items:flex-start">
                    <select name="cliente_id" id="sel-cliente" class="gselect" required style="flex:1">
                        <option value=""></option>
                    </select>
                    <button type="button" class="gbtn gbtn-ghost gbtn-sm" style="white-space:nowrap"
                            onclick="abrirNuevoCliente()" title="Dar de alta un cliente nuevo">+ Nuevo</button>
                </div>
            </div>
        </div>
    </div>

    <div class="seg-hint" style="font-size:12px;color:var(--txd);margin-bottom:12px">
        Elegí cada ítem del <strong>catálogo</strong> (el mismo que en presupuestos), con medidas y cantidad. Sin precios — eso lo pone Ventas al presupuestar.
    </div>

    @include('trabajos._catalogo-items')

    @include('clientes._quick-add')

    <div style="display:flex;gap:10px;margin-top:16px">
        <button type="submit" class="gbtn gbtn-primary">Guardar</button>
    </div>
</form>

@endsection

@section('scripts')
@include('trabajos._catalogo-items-js')
<script>
$(function () {
    $('#sel-cliente').select2({
        placeholder: 'Escribí el nombre del cliente...',
        minimumInputLength: 1, allowClear: true, width: 'resolve',
        ajax: {
            url: '{{ route("clientes.search") }}', dataType: 'json', delay: 250,
            data: params => ({ q: params.term }), processResults: data => ({ results: data }), cache: true,
        }
    });
    document.getElementById('form-trabajos').addEventListener('submit', function (e) {
        if (!$('#sel-cliente').val()) { e.preventDefault(); alert('Elegí un cliente.'); return; }
        if (document.querySelectorAll('.trabajo-fila').length === 0) { e.preventDefault(); alert('Agregá al menos un ítem.'); }
    });
});
</script>
@endsection

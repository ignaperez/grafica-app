@extends('layouts.app')

@section('page-title', 'Agregar trabajos — Orden #' . $orden->id)

@section('topbar-actions')
    <a href="{{ route('ordenes-trabajo.show', $orden->id) }}" class="gbtn gbtn-ghost gbtn-sm">
        ← Ver orden #{{ $orden->id }}
    </a>
@endsection

@section('content')
<style> input[type="date"].ginput { color-scheme: dark; } </style>

{{-- Info de la orden --}}
<div class="gcard mb-4">
    <div class="gcard-bd" style="padding:14px 20px;display:flex;gap:32px;flex-wrap:wrap;font-size:13px">
        <div>
            <div class="txd" style="font-size:10px;letter-spacing:1px;text-transform:uppercase">Cliente</div>
            <div style="color:var(--tx);font-weight:600">{{ $orden->cliente->nombre ?? '-' }}</div>
        </div>
        <div>
            <div class="txd" style="font-size:10px;letter-spacing:1px;text-transform:uppercase">Orden</div>
            <div class="mono" style="color:var(--ac)">#{{ $orden->id }}</div>
        </div>
        @if($orden->observaciones)
        <div>
            <div class="txd" style="font-size:10px;letter-spacing:1px;text-transform:uppercase">Observaciones</div>
            <div style="color:var(--tx)">{{ $orden->observaciones }}</div>
        </div>
        @endif
    </div>
</div>

<div class="seg-hint" style="font-size:12px;color:var(--txd);margin-bottom:12px">
    Elegí cada ítem del <strong>catálogo</strong> (el mismo que en presupuestos), con medidas y cantidad. Sin precios — eso lo pone Ventas al presupuestar.
</div>

<form id="form-trabajos" action="{{ route('trabajos.store-multiples') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="orden_trabajo_id" value="{{ $orden->id }}">

    @include('trabajos._catalogo-items')

    <div style="display:flex;gap:10px;margin-top:16px">
        <button type="submit" class="gbtn gbtn-primary">Guardar todos</button>
    </div>
</form>

@endsection

@section('scripts')
@include('trabajos._catalogo-items-js')
<script>
document.getElementById('form-trabajos').addEventListener('submit', function (e) {
    if (document.querySelectorAll('.trabajo-fila').length === 0) {
        e.preventDefault(); alert('Agregá al menos un ítem antes de guardar.');
    }
});
</script>
@endsection

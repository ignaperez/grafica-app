@extends('layouts.app')

@section('page-title', 'Nueva Orden de Trabajo')

@section('topbar-actions')
    <a href="{{ route('ordenes-trabajo.index') }}" class="gbtn gbtn-ghost gbtn-sm">← Volver</a>
@endsection

@section('content')

<form action="{{ route('ordenes-trabajo.store') }}" method="POST" id="orden-form">
@csrf

<div style="display:grid;grid-template-columns:340px 1fr;gap:20px;align-items:start">

    {{-- COLUMNA IZQUIERDA: datos de la orden --}}
    <div class="gcard">
        <div class="gcard-hd"><span class="gcard-title">Datos de la orden</span></div>
        <div class="gcard-bd">

            <div class="gfg">
                <label class="glabel">Cliente *</label>
                <select name="cliente_id" id="cliente_id" class="gselect" required style="width:100%"></select>
            </div>

            <div class="gfg">
                <label class="glabel">Fecha recibido</label>
                <input type="date" name="fecha_recibido" class="ginput" value="{{ date('Y-m-d') }}">
            </div>

            <div class="gfg">
                <label class="glabel">Observaciones</label>
                <textarea name="observaciones" class="gtextarea" rows="3" placeholder="Notas internas, referencias, etc."></textarea>
            </div>

            <button type="submit" class="gbtn gbtn-primary" style="width:100%">
                Crear orden
            </button>

        </div>
    </div>

    {{-- COLUMNA DERECHA: aviso --}}
    <div class="gcard" style="border-style:dashed;border-color:#2a2a2a">
        <div class="gcard-bd" style="text-align:center;padding:48px 24px">
            <div style="font-size:32px;margin-bottom:12px;opacity:.2">+</div>
            <div style="font-size:13px;color:#444;line-height:1.8">
                Primero creá la orden.<br>
                Después agregás los trabajos desde el detalle.
            </div>
        </div>
    </div>

</div>

</form>

@endsection

@section('scripts')
<script>
$(function(){
    $('#cliente_id').select2({
        placeholder: 'Buscar cliente...',
        minimumInputLength: 2,
        ajax: {
            url: "{{ url('clientes/search') }}",
            dataType: 'json',
            delay: 250,
            data: params => ({ q: params.term }),
            processResults: data => ({ results: data })
        }
    });
});
</script>
@endsection

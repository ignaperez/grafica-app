@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Nueva Orden de Trabajo</h2>

    <form action="{{ route('ordenes-trabajo.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label>Cliente</label>
            <select name="cliente_id" id="cliente_id" class="form-control" required></select>
        </div>

        <div class="mb-3">
            <label>Fecha recibido</label>
            <input type="date" name="fecha_recibido" class="form-control" value="{{ date('Y-m-d') }}">
        </div>

        <div class="mb-3">
            <label>Observaciones</label>
            <textarea name="observaciones" class="form-control"></textarea>
        </div>

        <button class="btn btn-primary">Crear orden</button>
    </form>
</div>
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

@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Nueva Orden de Trabajo</h1>

    <form method="POST" action="{{ route('ordenes-trabajo.store') }}">
        @csrf

        <div class="mb-3">
            <label>Cliente *</label>
            <select name="cliente_id" id="cliente_id" class="form-select select2" required>
                @if (old('cliente_id'))
                    @php
                        $cliente = \App\Models\Cliente::find(old('cliente_id'));
                    @endphp
                    @if ($cliente)
                        <option value="{{ $cliente->id }}" selected>{{ $cliente->nombre }}</option>
                    @endif
                @endif
            </select>
        </div>

        <div class="mb-3">
            <label>Fecha de recepción *</label>
            <input type="date" name="fecha_recibido" class="form-control" value="{{ date('Y-m-d') }}" required>
        </div>

        <div class="mb-3">
            <label>Observaciones</label>
            <textarea name="observaciones" class="form-control"></textarea>
        </div>

        <hr>
        <h4>Trabajos</h4>
        <div id="trabajos-container"></div>
        <button type="button" class="btn btn-sm btn-secondary my-2" onclick="agregarTrabajo()">+ Agregar trabajo</button>

        <button type="submit" class="btn btn-primary mt-3">Guardar Orden</button>
    </form>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function () {
        $('#cliente_id').select2({
            placeholder: 'Buscar cliente...',
            ajax: {
                url: '/clientes/search',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return { term: params.term };
                },
                processResults: function (data) {
                    return { results: data };
                },
                cache: true
            }
        });
    });

    let trabajoIndex = 0;

    function agregarTrabajo() {
        const container = document.getElementById('trabajos-container');

        const trabajo = document.createElement('div');
        trabajo.classList.add('border', 'rounded', 'p-3', 'mb-3');
        trabajo.innerHTML = `
            <div class="row g-2">
                <div class="col-md-4">
                    <label>Producto *</label>
                    <select name="trabajos[${trabajoIndex}][producto_id]" class="form-select producto-select" required></select>
                </div>
                <div class="col-md-2">
                    <label>Cantidad</label>
                    <input type="number" name="trabajos[${trabajoIndex}][cantidad]" class="form-control" value="1" min="1">
                </div>
                <div class="col-md-2">
                    <label>Medidas</label>
                    <input type="text" name="trabajos[${trabajoIndex}][medidas]" class="form-control">
                </div>
                <div class="col-md-3">
                    <label>Fecha de entrega</label>
                    <input type="date" name="trabajos[${trabajoIndex}][fecha_entrega]" class="form-control">
                </div>
            </div>
            <div class="mt-2">
                <label>Descripción</label>
                <input type="text" name="trabajos[${trabajoIndex}][descripcion]" class="form-control">
            </div>
        `;
        container.appendChild(trabajo);

        $(`select[name="trabajos[${trabajoIndex}][producto_id]"]`).select2({
            placeholder: 'Buscar producto...',
            ajax: {
                url: '/productos/search',
                dataType: 'json',
                delay: 250,
                processResults: function (data) {
                    return { results: data };
                },
                cache: true
            }
        });

        trabajoIndex++;
    }
</script>
@endsection

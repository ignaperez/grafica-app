@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Nueva Orden de Trabajo</h1>

        <form action="{{ route('ordenes-trabajo.store') }}" method="POST" enctype="multipart/form-data">


            @csrf

            <div class="mb-3">
                <label for="cliente_id">Cliente *</label>
                <select name="cliente_id" id="cliente_id" class="form-control" required>
                    <option value="">Seleccionar cliente</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="fecha_recibido">Fecha de recepción *</label>
                <input type="date" name="fecha_recibido" id="fecha_recibido" class="form-control"
                    value="{{ date('Y-m-d') }}" required>
            </div>

            <div class="mb-3">
                <label for="observaciones">Observaciones</label>
                <textarea name="observaciones" id="observaciones" class="form-control"></textarea>
            </div>
            <div class="mb-3">
                <label for="fotos" class="form-label">Subir fotos</label>
                <input type="file" name="fotos[]" id="fotos" class="form-control" multiple>
            </div>

            <button type="submit" class="btn btn-primary">Guardar Orden</button>
        </form>
    </div>
@endsection

@section('scripts')
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

   <script>
$(document).ready(function () {
    $('#cliente_id').select2({
        placeholder: 'Buscar cliente...',
        minimumInputLength: 2,
        ajax: {
            url: "{{ route('clientes.search') }}",
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return { q: params.term };
            },
            processResults: function (data) {
                return { results: data };
            }
        }
    });
});
</script>



@endsection


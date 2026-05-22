@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Nuevo Cliente</h1>

    <form action="{{ route('clientes.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre *</label>
            <input type="text" name="nombre" class="form-control" required value="{{ old('nombre') }}">
        </div>

        <div class="mb-3">
            <label for="telefono" class="form-label">Teléfono</label>
            <input type="text" name="telefono" class="form-control" value="{{ old('telefono') }}">
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}">
        </div>

        <div class="mb-3">
            <label for="direccion" class="form-label">Dirección</label>
            <textarea name="direccion" class="form-control">{{ old('direccion') }}</textarea>
        </div>

        <div class="mb-3">
            <label for="lista_precio_id" class="form-label">Lista de Precios</label>
            <select name="lista_precio_id" id="lista_precio_id" class="form-select" required style="width: 100%">
            @foreach($listas as $lista)
                    <option value="{{ $lista->id }}" {{ old('lista_precio_id', 1) == $lista->id ? 'selected' : '' }}>
                        {{ $lista->nombre }}
                    </option>
                @endforeach
                </select>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Guardar Cliente</button>
    </form>
</div>
<script>
$(document).ready(function() {
    $('#lista_precio_id').select2({
        placeholder: 'Seleccionar lista de precios',
        allowClear: true,
        ajax: {
            url: '/listas-precios/buscar',
            dataType: 'json',
            delay: 250,
            processResults: function (data) {
                return { results: data };
            },
            cache: true
        }
    });

    // Si querés que venga preseleccionado por defecto con "Consumidor Final" (id = 1)
    $('#lista_precio_id').append(new Option('Consumidor Final', 1, true, true)).trigger('change');
});
</script>

@endsection

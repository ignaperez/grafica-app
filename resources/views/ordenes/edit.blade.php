@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Editar Orden de Trabajo #{{ $orden_trabajo->id }}</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('ordenes-trabajo.update', $orden_trabajo) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="cliente_id">Cliente</label>
            <select name="cliente_id" id="cliente_id" class="form-control select2" required>
                <option value="{{ $orden_trabajo->cliente->id }}" selected>{{ $orden_trabajo->cliente->nombre }}</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="fecha_recibido" class="form-label">Fecha de recepción</label>
            <input type="date" name="fecha_recibido" id="fecha_recibido" class="form-control" required
                value="{{ old('fecha_recibido', $orden_trabajo->fecha_recibido) }}">
        </div>

        <div class="mb-3">
            <label for="observaciones" class="form-label">Observaciones</label>
            <textarea name="observaciones" id="observaciones"
                class="form-control">{{ old('observaciones', $orden_trabajo->observaciones) }}</textarea>
        </div>

        <hr>
        <h4>Trabajos</h4>

        <div id="trabajos-container">
            @foreach ($orden_trabajo->trabajos as $index => $trabajo)
                <div class="trabajo border p-3 mb-3">
                    <input type="hidden" name="trabajos[{{ $index }}][id]" value="{{ $trabajo->id }}">

                    <div class="mb-2">
                        <label>Producto</label>
                        <select name="trabajos[{{ $index }}][tipo]" class="form-control select2-producto" required>
                            <option value="">Seleccione producto</option>
                            @foreach ($productos as $producto)
                                <option value="{{ $producto->nombre }}" {{ $producto->nombre == $trabajo->tipo ? 'selected' : '' }}>
                                    {{ $producto->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-2">
                        <label>Descripción</label>
                        <input type="text" name="trabajos[{{ $index }}][descripcion]" class="form-control"
                            value="{{ $trabajo->descripcion }}">
                    </div>

                    <div class="row mb-2">
                        <div class="col">
                            <label>Ancho (cm)</label>
                            <input type="number" step="0.01" name="trabajos[{{ $index }}][ancho]" class="form-control"
                                value="{{ $trabajo->ancho }}">
                        </div>
                        <div class="col">
                            <label>Alto (cm)</label>
                            <input type="number" step="0.01" name="trabajos[{{ $index }}][alto]" class="form-control"
                                value="{{ $trabajo->alto }}">
                        </div>
                    </div>

                    <div class="mb-2">
                        <label>Cantidad</label>
                        <input type="number" name="trabajos[{{ $index }}][cantidad]" class="form-control"
                            value="{{ $trabajo->cantidad }}">
                    </div>

                    <div class="mb-2">
                        <label>Fecha de entrega</label>
                        <input type="date" name="trabajos[{{ $index }}][fecha_entrega]" class="form-control"
                            value="{{ $trabajo->fecha_entrega }}">
                    </div>
                </div>
            @endforeach
        </div>

        <button type="button" id="agregar-trabajo" class="btn btn-secondary mb-3">+ Agregar otro trabajo</button>

        <button type="submit" class="btn btn-primary">Actualizar Orden</button>
    </form>
</div>

<script>
    let index = {{ count($orden_trabajo->trabajos) }};

    let productosOptions = `
        <option value="">Seleccione producto</option>
        @foreach ($productos as $producto)
            <option value="{{ $producto->nombre }}">{{ $producto->nombre }}</option>
        @endforeach
    `;

    document.getElementById('agregar-trabajo').addEventListener('click', function () {
        const container = document.getElementById('trabajos-container');

        const nuevoTrabajo = document.createElement('div');
        nuevoTrabajo.classList.add('trabajo', 'border', 'p-3', 'mb-3');
        nuevoTrabajo.innerHTML = `
            <input type="hidden" name="trabajos[${index}][id]" value="">
            <div class="mb-2">
                <label>Producto</label>
                <select name="trabajos[${index}][tipo]" class="form-control select2-producto" required>
                    ${productosOptions}
                </select>
            </div>
            <div class="mb-2">
                <label>Descripción</label>
                <input type="text" name="trabajos[${index}][descripcion]" class="form-control">
            </div>
            <div class="row mb-2">
                <div class="col">
                    <label>Ancho (cm)</label>
                    <input type="number" step="0.01" name="trabajos[${index}][ancho]" class="form-control">
                </div>
                <div class="col">
                    <label>Alto (cm)</label>
                    <input type="number" step="0.01" name="trabajos[${index}][alto]" class="form-control">
                </div>
            </div>
            <div class="mb-2">
                <label>Cantidad</label>
                <input type="number" name="trabajos[${index}][cantidad]" class="form-control" value="1">
            </div>
            <div class="mb-2">
                <label>Fecha de entrega</label>
                <input type="date" name="trabajos[${index}][fecha_entrega]" class="form-control">
            </div>
        `;

        container.appendChild(nuevoTrabajo);
        index++;

        $(nuevoTrabajo).find('.select2-producto').select2();
    });
</script>
@endsection

@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Editar Orden de Trabajo #{{ $orden->id }}</h1>

        <div class="alert alert-info mt-2">
            Porcentaje de trabajos completados: {{ round($porcentaje, 2) }}%
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="orden-form" action="{{ route('ordenes-trabajo.update', $orden->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label>Cliente</label>
                <select name="cliente_id" id="cliente_id" class="form-control select2" required>
                    @foreach($clientes as $cli)
                        <option value="{{ $cli->id }}" {{ $cli->id == $orden->cliente_id ? 'selected' : '' }}>
                            {{ $cli->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="fecha_recibido">Fecha de recepción</label>
                <input type="date" name="fecha_recibido" id="fecha_recibido" class="form-control" required
                    value="{{ old('fecha_recibido', $orden->fecha_recibido) }}">
            </div>

            <div class="mb-3">
                <label for="observaciones">Observaciones</label>
                <textarea name="observaciones" id="observaciones"
                    class="form-control">{{ old('observaciones', $orden->observaciones) }}</textarea>
            </div>

            <hr>
            <h4>Trabajos</h4>

            <div id="trabajos-container">
                @foreach ($orden->trabajos as $index => $trabajo)
                    <div class="trabajo border p-3 mb-3" data-trabajo-id="{{ $trabajo->id }}">
                        <input type="hidden" name="trabajos[{{ $index }}][id]" value="{{ $trabajo->id }}">


                        <div class="mb-2">
                            <label>Producto</label>
                            <select name="trabajos[{{ $index }}][producto_id]" class="form-control select2-producto" required>
                                <option value="">Seleccione producto</option>
                                @foreach ($productos as $producto)
                                    <option value="{{ $producto->id }}" {{ $producto->id == $trabajo->producto_id ? 'selected' : '' }}>
                                        {{ $producto->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2">
                            <label>Tipo de trabajo</label>
                            <input type="text" name="trabajos[{{ $index }}][tipo]" class="form-control" required
                                value="{{ $trabajo->tipo }}">
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
                        <div class="mb-2">
                            <label>Estado:</label>
                            <span class="badge {{ $trabajo->estado == 'terminado' ? 'bg-success' : 'bg-secondary' }}">
                                {{ $trabajo->estado }}
                            </span>
                            <button type="button" class="btn btn-sm btn-success marcar-terminado ms-2">Marcar como
                                Terminado</button>
                            <button type="button" class="btn btn-danger btn-sm eliminar-trabajo ms-2">Eliminar</button>
                        </div>
                    </div>
                @endforeach
            </div>

            <button type="button" id="agregar-trabajo" class="btn btn-secondary mb-3">+ Agregar otro trabajo</button>

            <button type="submit" class="btn btn-primary">Actualizar Orden</button>
        </form>

        <hr>
        <h4>Previsualización de Trabajos</h4>
        <div id="preview-container"></div>
    </div>

    <script>
        let index = {{ count($orden->trabajos) }};
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
            <div class="mb-2">
                <label>Producto</label>
                <select name="trabajos[${index}][tipo]" class="form-control trabajo-input select2-producto" required>
                    ${productosOptions}
                </select>
            </div>
            <!-- resto de campos... -->`;
            container.appendChild(nuevoTrabajo);
            index++;

            $(nuevoTrabajo).find('.select2-producto').select2();
            actualizarPreview();
        });

        document.addEventListener('click', function (e) {
            // Marcar como terminado...
            // Eliminar trabajo...
        });

        function actualizarPreview() { /* ... */ }

        // Inicializar Select2 para los trabajos existentes
        document.addEventListener('DOMContentLoaded', function () {
            $('.select2-producto').each(function () {
                $(this).select2();
            });
        });
    </script>

@endsection
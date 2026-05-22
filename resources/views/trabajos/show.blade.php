@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Detalle del Trabajo #{{ $trabajo->id }}</h3>

    <div class="card mt-3">
        <div class="card-body">
            <p><strong>Producto:</strong> {{ $trabajo->producto->nombre ?? $trabajo->tipo }}</p>
            <p><strong>Ancho:</strong> {{ $trabajo->ancho }} m</p>
            <p><strong>Alto:</strong> {{ $trabajo->alto }} m</p>
            <p><strong>Cantidad:</strong> {{ $trabajo->cantidad }}</p>
            <p><strong>Observaciones:</strong> {{ $trabajo->descripcion ?? '-' }}</p>
            <p><strong>Fecha de Entrega:</strong> {{ $trabajo->fecha_entrega ?? '-' }}</p>
            <p><strong>Consumo:</strong>
                @php
                    $consumo = 0;
                    if ($trabajo->ancho && $trabajo->alto) {
                        $consumo = round($trabajo->ancho * $trabajo->alto * $trabajo->cantidad, 2);
                    }
                @endphp
                {{ $consumo }} m²
            </p>
        </div>
    </div>

    <a href="{{ url()->previous() }}" class="btn btn-secondary mt-3">Volver</a>
</div>
@endsection

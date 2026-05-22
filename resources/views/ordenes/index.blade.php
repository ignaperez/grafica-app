@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Órdenes de Trabajo</h1>
    <a href="{{ route('ordenes-trabajo.create') }}" class="btn btn-success mb-3">
    + Crear nueva orden
</a>


    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if($ordenes->isEmpty())
        <div class="alert alert-warning text-center">
            No hay órdenes de trabajo registradas.
        </div>
    @else
        <div class="list-group">
            @foreach($ordenes as $orden)
                @php
                    $total = $orden->trabajos->count();
                    $terminados = $orden->trabajos->where('estado', 'terminado')->count();
                    $porcentaje = ($total > 0) ? ($terminados / $total * 100) : 0;
                @endphp
                <div class="list-group-item mb-2 shadow-sm">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1">Orden #{{ $orden->id }} — {{ $orden->cliente->nombre ?? 'Sin cliente' }}</h5>
                            <p class="mb-1">
                                <strong>Fecha:</strong> {{ $orden->fecha_recibido }} <br>
                                <strong>Observaciones:</strong> {{ $orden->observaciones ?? '-' }}
                            </p>
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar bg-success" role="progressbar"
                                    style="width: {{ $porcentaje }}%;" aria-valuenow="{{ $porcentaje }}"
                                    aria-valuemin="0" aria-valuemax="100">
                                    {{ round($porcentaje, 2) }}%
                                </div>
                            </div>
                        </div>
                        <div class="text-end">
                        <a href="{{ route('ordenes-trabajo.show', $orden->id) }}" class="btn btn-info btn-sm mb-1">Ver</a>
                            <a href="{{ route('ordenes-trabajo.edit', $orden->id) }}" class="btn btn-primary btn-sm mb-1">Editar</a>
                            <form action="{{ route('ordenes-trabajo.destroy', $orden->id) }}" method="POST" style="display:inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm"
                                    onclick="return confirm('¿Estás seguro de eliminar esta orden?')">
                                    Eliminar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection

@extends('layouts.app')

@section('content')
<div class="container">
    <h1>RRHH - Presentismo</h1>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-success">
                <div class="card-body">
                    <h5 class="card-title">Fichadas de hoy</h5>
                    <p class="card-text display-6">{{ $totalHoy }}</p>
                    <small class="text-muted">{{ $hoy->format('d/m/Y') }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-primary">
                <div class="card-body">
                    <h5 class="card-title">Empleados activos</h5>
                    <p class="card-text display-6">{{ $empleados }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Ingresos de hoy (hasta 6) --}}
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>Ingresos de hoy</strong>
            <small class="text-muted">últimos {{ $ingresosHoy->count() }}</small>
        </div>
        <ul class="list-group list-group-flush">
            @forelse($ingresosHoy as $f)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span>{{ $f->empleado?->nombre_completo ?? ('#' . $f->empleado_id) }}</span>
                    <span class="badge bg-success rounded-pill" style="font-size:.95rem">
                        {{ $f->momento->format('H:i') }}
                    </span>
                </li>
            @empty
                <li class="list-group-item text-muted">Sin ingresos hoy.</li>
            @endforelse
        </ul>
    </div>

    <a href="{{ route('rrhh.fichadas.hoy') }}" class="btn btn-success">Ver fichadas de hoy</a>
    <a href="{{ route('rrhh.fichadas.index') }}" class="btn btn-outline-secondary">Buscar fichadas</a>
    <a href="{{ route('rrhh.empleados.index') }}" class="btn btn-outline-primary">Empleados</a>
</div>
@endsection

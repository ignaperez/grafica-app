@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Tipos de Trabajo</h2>
        <a href="{{ route('tipo-trabajos.create') }}" class="btn btn-primary">+ Nuevo tipo</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tipos as $tipo)
                <tr>
                    <td>{{ $tipo->nombre }}</td>
                    <td>{{ $tipo->descripcion ?? '-' }}</td>
                    <td>
                        <span class="badge {{ $tipo->activo ? 'bg-success' : 'bg-secondary' }}">
                            {{ $tipo->activo ? 'Activo' : 'Inactivo' }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('tipo-trabajos.edit', $tipo->id) }}" class="btn btn-sm btn-warning">Editar</a>
                        <form action="{{ route('tipo-trabajos.destroy', $tipo->id) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('¿Eliminar este tipo de trabajo?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted">No hay tipos de trabajo cargados.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Materiales</h2>
        <a href="{{ route('materiales.create') }}" class="btn btn-primary">+ Nuevo material</a>
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
            @forelse($materiales as $material)
                <tr>
                    <td>{{ $material->nombre }}</td>
                    <td>{{ $material->descripcion ?? '-' }}</td>
                    <td>
                        <span class="badge {{ $material->activo ? 'bg-success' : 'bg-secondary' }}">
                            {{ $material->activo ? 'Activo' : 'Inactivo' }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('materiales.edit', $material->id) }}" class="btn btn-sm btn-warning">Editar</a>
                        <form action="{{ route('materiales.destroy', $material->id) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('¿Eliminar este material?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted">No hay materiales cargados.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Máquinas</h2>
        <a href="{{ route('maquinas.create') }}" class="btn btn-primary">+ Nueva máquina</a>
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
            @forelse($maquinas as $maquina)
                <tr>
                    <td>{{ $maquina->nombre }}</td>
                    <td>{{ $maquina->descripcion ?? '-' }}</td>
                    <td>
                        <span class="badge {{ $maquina->activo ? 'bg-success' : 'bg-secondary' }}">
                            {{ $maquina->activo ? 'Activo' : 'Inactivo' }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('maquinas.edit', $maquina->id) }}" class="btn btn-sm btn-warning">Editar</a>
                        <form action="{{ route('maquinas.destroy', $maquina->id) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('¿Eliminar esta máquina?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted">No hay máquinas cargadas.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

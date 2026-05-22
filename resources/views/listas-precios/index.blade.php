@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Listas de Precios</h2>

    <a href="{{ route('listas-precios.create') }}" class="btn btn-primary mb-3">Nueva Lista</a>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($listas as $lista)
                <tr>
                    <td>{{ $lista->nombre }}</td>
                    <td>{{ $lista->descripcion ?? '-' }}</td>
                    <td>
                        <a href="{{ route('listas-precios.edit', $lista->id) }}" class="btn btn-sm btn-warning">Editar</a>

                        <form action="{{ route('listas-precios.destroy', $lista->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar esta lista?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

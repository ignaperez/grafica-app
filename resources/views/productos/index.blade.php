@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Listado de Productos</h2>

    <a href="{{ route('productos.create') }}" class="btn btn-primary mb-3">Nuevo Producto</a>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($productos as $producto)
                <tr>
                    <td>{{ $producto->nombre }}</td>
                    <td>{{ $producto->descripcion }}</td>
                    <td>
                        <a href="{{ route('productos.edit', $producto->id) }}" class="btn btn-sm btn-warning">Editar</a>
                        
                        <form action="{{ route('productos.destroy', $producto->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de eliminar este producto?');">
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

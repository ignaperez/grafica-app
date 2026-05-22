@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Editar Producto</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('productos.update', $producto->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Tipo *</label>
            <input type="text" name="tipo" class="form-control" required
                   placeholder="Ej: vinilo, lona, papel"
                   value="{{ old('tipo', $producto->tipo) }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Nombre *</label>
            <input type="text" name="nombre" class="form-control" required value="{{ old('nombre', $producto->nombre) }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Descripción</label>
            <textarea name="descripcion" class="form-control" rows="3">{{ old('descripcion', $producto->descripcion) }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Precio base *</label>
            <div class="input-group">
                <span class="input-group-text">$</span>
                <input type="number" name="precio" class="form-control" required
                       min="0" step="0.01" value="{{ old('precio', $producto->precio) }}">
            </div>
        </div>

        <a href="{{ route('productos.index') }}" class="btn btn-secondary">Cancelar</a>
        <button type="submit" class="btn btn-primary">Guardar cambios</button>
    </form>
</div>
@endsection

@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Editar Cliente</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('clientes.update', $cliente->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Nombre *</label>
            <input type="text" name="nombre" class="form-control" required value="{{ old('nombre', $cliente->nombre) }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Teléfono</label>
            <input type="text" name="telefono" class="form-control" value="{{ old('telefono', $cliente->telefono) }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email', $cliente->email) }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Dirección</label>
            <textarea name="direccion" class="form-control">{{ old('direccion', $cliente->direccion) }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Lista de Precios *</label>
            <select name="lista_precio_id" class="form-select" required>
                @foreach ($listas as $lista)
                    <option value="{{ $lista->id }}" {{ old('lista_precio_id', $cliente->lista_precio_id) == $lista->id ? 'selected' : '' }}>
                        {{ $lista->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <a href="{{ route('clientes.index') }}" class="btn btn-secondary">Cancelar</a>
        <button type="submit" class="btn btn-primary">Guardar cambios</button>
    </form>
</div>
@endsection

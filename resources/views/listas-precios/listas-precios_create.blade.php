@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Nueva Lista de Precios</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('listas-precios.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label class="form-label">Nombre *</label>
            <input type="text" name="nombre" class="form-control" required
                   placeholder="Ej: Consumidor Final, Gremio, Estado"
                   value="{{ old('nombre') }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Descripción</label>
            <textarea name="descripcion" class="form-control" rows="3">{{ old('descripcion') }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Multiplicador *</label>
            <div class="input-group">
                <input type="number" name="multiplicador" class="form-control" required
                       min="0" step="0.01" value="{{ old('multiplicador', 1.00) }}">
                <span class="input-group-text">× precio base</span>
            </div>
            <div class="form-text">1.00 = precio normal. 1.50 = 50% más caro. 0.80 = 20% de descuento.</div>
        </div>

        <a href="{{ route('listas-precios.index') }}" class="btn btn-secondary">Cancelar</a>
        <button type="submit" class="btn btn-primary">Guardar lista</button>
    </form>
</div>
@endsection

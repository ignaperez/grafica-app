@extends('layouts.app')

@section('content')
<div class="container" style="max-width:600px">
    <h2 class="mb-4">Editar Material</h2>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <form action="{{ route('materiales.update', $material->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label class="form-label">Nombre *</label>
            <input type="text" name="nombre" class="form-control" required
                   value="{{ old('nombre', $material->nombre) }}">
        </div>
        <div class="mb-3">
            <label class="form-label">Descripción</label>
            <input type="text" name="descripcion" class="form-control"
                   value="{{ old('descripcion', $material->descripcion) }}">
        </div>
        <div class="mb-3 form-check">
            <input type="hidden" name="activo" value="0">
            <input type="checkbox" name="activo" value="1" class="form-check-input" id="activo"
                   {{ old('activo', $material->activo) ? 'checked' : '' }}>
            <label class="form-check-label" for="activo">Activo</label>
        </div>
        <a href="{{ route('materiales.index') }}" class="btn btn-secondary">Cancelar</a>
        <button type="submit" class="btn btn-primary">Actualizar</button>
    </form>
</div>
@endsection

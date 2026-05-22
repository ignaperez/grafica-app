@extends('layouts.app')

@section('content')
<div class="container" style="max-width:600px">
    <h2 class="mb-4">Editar Tipo de Trabajo</h2>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <form action="{{ route('tipo-trabajos.update', $tipoTrabajo->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label class="form-label">Nombre *</label>
            <input type="text" name="nombre" class="form-control" required
                   value="{{ old('nombre', $tipoTrabajo->nombre) }}">
        </div>
        <div class="mb-3">
            <label class="form-label">Descripción</label>
            <input type="text" name="descripcion" class="form-control"
                   value="{{ old('descripcion', $tipoTrabajo->descripcion) }}">
        </div>
        <div class="mb-3 form-check">
            <input type="hidden" name="activo" value="0">
            <input type="checkbox" name="activo" value="1" class="form-check-input" id="activo"
                   {{ old('activo', $tipoTrabajo->activo) ? 'checked' : '' }}>
            <label class="form-check-label" for="activo">Activo</label>
        </div>
        <a href="{{ route('tipo-trabajos.index') }}" class="btn btn-secondary">Cancelar</a>
        <button type="submit" class="btn btn-primary">Actualizar</button>
    </form>
</div>
@endsection

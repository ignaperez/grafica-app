@extends('layouts.app')

@section('content')
<div class="container" style="max-width:600px">
    <h2 class="mb-4">Nuevo Material</h2>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <form action="{{ route('materiales.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label class="form-label">Nombre *</label>
            <input type="text" name="nombre" class="form-control" required
                   placeholder="Ej: Vinilo, Lona, Acrílico, MDF"
                   value="{{ old('nombre') }}">
        </div>
        <div class="mb-3">
            <label class="form-label">Descripción</label>
            <input type="text" name="descripcion" class="form-control"
                   value="{{ old('descripcion') }}">
        </div>
        <a href="{{ route('materiales.index') }}" class="btn btn-secondary">Cancelar</a>
        <button type="submit" class="btn btn-primary">Guardar</button>
    </form>
</div>
@endsection

@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Clientes</h1>
    <a href="{{ route('clientes.create') }}" class="btn btn-primary mb-3">+ Nuevo Cliente</a>

    <ul class="list-group">
        @foreach ($clientes as $cliente)
            <li class="list-group-item">
                {{ $cliente->nombre }} — Lista: {{ $cliente->listaPrecio->nombre ?? 'Sin asignar' }}
            </li>
        @endforeach
    </ul>
</div>
@endsection

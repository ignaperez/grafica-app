@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h2>Prueba de Órdenes de Trabajo</h2>

        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($ordenes as $orden)
                    <tr>
                        <td>{{ $orden->id }}</td>
                        <td>{{ $orden->cliente?->nombre ?? 'Sin especificar' }}</td>
                        <td>{{ \Carbon\Carbon::parse($orden->fecha_recibido)->format('d/m/Y') }}</td>
                        <td>{{ ucfirst($orden->estado) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

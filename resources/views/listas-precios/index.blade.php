@extends('layouts.app')

@section('page-title', 'Listas de Precios')

@section('topbar-actions')
    <a href="{{ route('listas-precios.create') }}" class="gbtn gbtn-primary gbtn-sm">+ Nueva lista</a>
@endsection

@section('content')
<div class="gcard">
    <div class="gcard-hd">
        <span class="gcard-title">Listas de precios</span>
    </div>
    <div class="gcard-bd" style="padding:0">
        <table class="gtable">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Multiplicador</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($listas as $lista)
                <tr>
                    <td><span class="mono">{{ $lista->nombre }}</span></td>
                    <td><span class="txm">{{ $lista->descripcion ?? '—' }}</span></td>
                    <td><span class="mono">× {{ number_format($lista->multiplicador, 2) }}</span></td>
                    <td style="text-align:right; white-space:nowrap;">
                        <a href="{{ route('listas-precios.edit', $lista->id) }}" class="gbtn gbtn-ghost gbtn-xs">Editar</a>
                        <form action="{{ route('listas-precios.destroy', $lista->id) }}" method="POST"
                              class="d-inline" onsubmit="return confirm('¿Eliminar esta lista?')">
                            @csrf @method('DELETE')
                            <button class="gbtn gbtn-danger gbtn-xs">Eliminar</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align:center;" class="txm">No hay listas de precios.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

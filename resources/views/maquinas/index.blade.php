@extends('layouts.app')

@section('page-title', 'Máquinas')

@section('topbar-actions')
    <a href="{{ route('maquinas.create') }}" class="gbtn gbtn-primary gbtn-sm">+ Nueva máquina</a>
@endsection

@section('content')
<div class="gcard">
    <div class="gcard-hd">
        <span class="gcard-title">Máquinas</span>
    </div>
    <div class="gcard-bd" style="padding:0">
        <table class="gtable">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Proceso</th>
                    <th>Costo m²</th>
                    <th>Costo ml</th>
                    <th>Costo unidad</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($maquinas as $maquina)
                <tr>
                    <td>{{ $maquina->nombre }}</td>
                    <td><span class="txm">{{ $maquina->tipoTrabajo?->nombre ?? '—' }}</span></td>
                    <td><span class="mono">{{ $maquina->costo_m2 > 0 ? '$'.number_format($maquina->costo_m2,2) : '—' }}</span></td>
                    <td><span class="mono">{{ $maquina->costo_ml > 0 ? '$'.number_format($maquina->costo_ml,2) : '—' }}</span></td>
                    <td><span class="mono">{{ $maquina->costo_unidad > 0 ? '$'.number_format($maquina->costo_unidad,2) : '—' }}</span></td>
                    <td>
                        <span class="badge-estado {{ $maquina->activo ? 'be-lista' : 'be-cancelada' }}">
                            {{ $maquina->activo ? 'Activa' : 'Inactiva' }}
                        </span>
                    </td>
                    <td style="text-align:right; white-space:nowrap;">
                        <a href="{{ route('maquinas.edit', $maquina->id) }}" class="gbtn gbtn-ghost gbtn-xs">Editar</a>
                        <form action="{{ route('maquinas.destroy', $maquina->id) }}" method="POST"
                              class="d-inline" onsubmit="return confirm('¿Eliminar esta máquina?')">
                            @csrf @method('DELETE')
                            <button class="gbtn gbtn-danger gbtn-xs">Eliminar</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align:center;" class="txm">No hay máquinas cargadas.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('page-title', 'Materiales')

@section('topbar-actions')
    <a href="{{ route('materiales.create') }}" class="gbtn gbtn-primary gbtn-sm">+ Nuevo material</a>
@endsection

@section('content')
<div class="gcard">
    <div class="gcard-hd">
        <span class="gcard-title">Materiales</span>
    </div>
    <div class="gcard-bd" style="padding:0">
        <table class="gtable">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Costo m²</th>
                    <th>Costo ml</th>
                    <th>Costo unidad</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($materiales as $material)
                <tr>
                    <td>{{ $material->nombre }}</td>
                    <td><span class="txm">{{ $material->descripcion ?? '—' }}</span></td>
                    <td><span class="mono">{{ $material->costo_m2 > 0 ? '$'.number_format($material->costo_m2,2) : '—' }}</span></td>
                    <td><span class="mono">{{ $material->costo_ml > 0 ? '$'.number_format($material->costo_ml,2) : '—' }}</span></td>
                    <td><span class="mono">{{ $material->costo_unidad > 0 ? '$'.number_format($material->costo_unidad,2) : '—' }}</span></td>
                    <td>
                        <span class="badge-estado {{ $material->activo ? 'be-lista' : 'be-cancelada' }}">
                            {{ $material->activo ? 'Activo' : 'Inactivo' }}
                        </span>
                    </td>
                    <td style="text-align:right; white-space:nowrap;">
                        <a href="{{ route('materiales.edit', $material->id) }}" class="gbtn gbtn-ghost gbtn-xs">Editar</a>
                        <form action="{{ route('materiales.destroy', $material->id) }}" method="POST"
                              class="d-inline" onsubmit="return confirm('¿Eliminar este material?')">
                            @csrf @method('DELETE')
                            <button class="gbtn gbtn-danger gbtn-xs">Eliminar</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align:center;" class="txm">No hay materiales cargados.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

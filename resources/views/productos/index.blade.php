@extends('layouts.app')

@section('page-title', 'Catálogo de servicios')

@section('topbar-actions')
    <a href="{{ route('productos.create') }}" class="gbtn gbtn-primary gbtn-sm">+ Nuevo servicio</a>
@endsection

@section('content')

<div class="gcard">
    <div class="gcard-hd">
        <span class="gcard-title">Servicios / productos</span>
        <span class="txd" style="font-size:12px">{{ $productos->count() }} registros</span>
    </div>
    <div class="gcard-bd" style="padding:0">
        <table class="gtable">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Proceso</th>
                    <th>Material</th>
                    <th>Unidad</th>
                    <th style="text-align:right">M.O.</th>
                    <th>Estado</th>
                    <th style="width:120px"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($productos as $p)
                <tr>
                    <td>
                        <span style="font-weight:600;color:var(--tx)">{{ $p->nombre }}</span>
                        @if($p->descripcion)
                            <div class="txd" style="font-size:11px;margin-top:2px">{{ Str::limit($p->descripcion, 60) }}</div>
                        @endif
                    </td>
                    <td class="txd">{{ $p->tipoTrabajo?->nombre ?? '—' }}</td>
                    <td class="txd">{{ $p->material?->nombre ?? '—' }}</td>
                    <td>
                        <span class="mono" style="font-size:12px;color:var(--ac)">{{ $p->unidad }}</span>
                    </td>
                    <td style="text-align:right" class="mono txd">
                        {{ $p->costo_mano_obra > 0 ? '$' . number_format($p->costo_mano_obra, 2) : '—' }}
                    </td>
                    <td>
                        @if($p->activo)
                            <span style="color:#4caf50;font-size:12px">● activo</span>
                        @else
                            <span style="color:var(--txd);font-size:12px">● inactivo</span>
                        @endif
                    </td>
                    <td style="text-align:right">
                        <a href="{{ route('productos.edit', $p->id) }}" class="gbtn gbtn-ghost gbtn-xs">Editar</a>
                        <form action="{{ route('productos.destroy', $p->id) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('¿Eliminar « {{ $p->nombre }} »?')">
                            @csrf @method('DELETE')
                            <button class="gbtn gbtn-danger gbtn-xs">Quitar</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align:center;color:var(--txd);padding:32px">
                        No hay servicios cargados todavía.
                        <a href="{{ route('productos.create') }}" style="color:var(--ac)">Crear el primero</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

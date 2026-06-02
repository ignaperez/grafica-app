@extends('layouts.app')

@section('page-title', 'Remitos')

@section('topbar-actions')
    <a href="{{ route('remitos.create') }}" class="gbtn gbtn-primary gbtn-sm">+ Nuevo remito</a>
@endsection

@section('content')

<div class="gcard">
    <div class="gcard-hd">
        <span class="gcard-title">Remitos emitidos</span>
        <span class="txd" style="font-size:12px">{{ $remitos->count() }} registros</span>
    </div>
    <div class="gcard-bd" style="padding:0">
        <table class="gtable">
            <thead>
                <tr>
                    <th>N° Remito</th>
                    <th>Tipo</th>
                    <th>Cliente</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                    <th>Creado por</th>
                    <th style="width:130px"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($remitos as $r)
                <tr>
                    <td>
                        <span class="mono" style="color:var(--ac);font-weight:600">{{ $r->numeroFormateado() }}</span>
                    </td>
                    <td style="font-size:11px">
                        @if($r->tipo === 'oficial')
                            <span style="color:var(--green,#4caf50)">● Oficial</span>
                        @else
                            <span class="txd">● Interno</span>
                        @endif
                    </td>
                    <td style="color:var(--tx)">{{ $r->cliente?->nombre ?? '—' }}</td>
                    <td class="txd">{{ $r->fecha->format('d/m/Y') }}</td>
                    <td>
                        <span style="color:{{ $r->estadoColor() }};font-size:12px">
                            ● {{ $r->estadoLabel() }}
                        </span>
                    </td>
                    <td class="txd" style="font-size:12px">{{ $r->createdBy?->name ?? '—' }}</td>
                    <td style="text-align:right">
                        <a href="{{ route('remitos.show', $r->id) }}" class="gbtn gbtn-ghost gbtn-xs">Ver</a>
                        <a href="{{ route('remitos.print', $r->id) }}" class="gbtn gbtn-ghost gbtn-xs" target="_blank">🖨</a>
                        <form action="{{ route('remitos.destroy', $r->id) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('¿Eliminar {{ $r->numeroFormateado() }}?')">
                            @csrf @method('DELETE')
                            <button class="gbtn gbtn-danger gbtn-xs">×</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align:center;color:var(--txd);padding:32px">
                        No hay remitos.
                        <a href="{{ route('remitos.create') }}" style="color:var(--ac)">Crear el primero</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

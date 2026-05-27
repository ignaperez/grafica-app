@extends('layouts.app')

@section('page-title', 'Presupuestos')

@section('topbar-actions')
    <a href="{{ route('presupuestos.create') }}" class="gbtn gbtn-primary gbtn-sm">+ Nuevo presupuesto</a>
@endsection

@section('content')

<div class="gcard">
    <div class="gcard-hd">
        <span class="gcard-title">Presupuestos</span>
        <span class="txd" style="font-size:12px">{{ $presupuestos->count() }} registros</span>
    </div>
    <div class="gcard-bd" style="padding:0">
        <table class="gtable">
            <thead>
                <tr>
                    <th>N°</th>
                    <th>Cliente</th>
                    <th>Fecha</th>
                    <th>Vence</th>
                    <th style="text-align:right">Total</th>
                    <th>Estado</th>
                    <th>Creado por</th>
                    <th style="width:150px"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($presupuestos as $p)
                <tr>
                    <td class="mono" style="color:var(--ac);font-weight:600">{{ $p->numeroFormateado() }}</td>
                    <td style="color:var(--tx)">{{ $p->cliente->nombre }}</td>
                    <td class="txd">{{ $p->fecha->format('d/m/Y') }}</td>
                    <td class="txd">
                        @if($p->fecha_vencimiento)
                            @if($p->fecha_vencimiento->isPast() && $p->estado === 'enviado')
                                <span style="color:#e53935">{{ $p->fecha_vencimiento->format('d/m/Y') }}</span>
                            @else
                                {{ $p->fecha_vencimiento->format('d/m/Y') }}
                            @endif
                        @else
                            —
                        @endif
                    </td>
                    <td style="text-align:right" class="mono">
                        <strong>${{ number_format($p->total, 2) }}</strong>
                    </td>
                    <td>
                        <span style="color:{{ $p->estadoColor() }};font-size:12px">
                            ● {{ $p->estadoLabel() }}
                        </span>
                    </td>
                    <td>
                        @if($p->createdBy)
                            <span class="txd" style="font-size:12px" title="Modificado por {{ $p->updatedBy?->name ?? '—' }}">
                                {{ $p->createdBy->name }}
                                @if($p->updatedBy && $p->updatedBy->id !== $p->createdBy->id)
                                    <span style="opacity:.5"> / {{ $p->updatedBy->name }}</span>
                                @endif
                            </span>
                        @else
                            <span class="txd">—</span>
                        @endif
                    </td>
                    <td style="text-align:right">
                        <a href="{{ route('presupuestos.show', $p->id) }}" class="gbtn gbtn-ghost gbtn-xs">Ver</a>
                        <a href="{{ route('presupuestos.edit', $p->id) }}" class="gbtn gbtn-ghost gbtn-xs">Editar</a>
                        <a href="{{ route('presupuestos.print', $p->id) }}" class="gbtn gbtn-ghost gbtn-xs" target="_blank">🖨</a>
                        <form action="{{ route('presupuestos.destroy', $p->id) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('¿Eliminar {{ $p->numeroFormateado() }}?')">
                            @csrf @method('DELETE')
                            <button class="gbtn gbtn-danger gbtn-xs">×</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align:center;color:var(--txd);padding:32px">
                        No hay presupuestos.
                        <a href="{{ route('presupuestos.create') }}" style="color:var(--ac)">Crear el primero</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

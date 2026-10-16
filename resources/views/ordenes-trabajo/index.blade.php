@extends('layouts.app')

@section('page-title', 'Órdenes de trabajo')

@section('topbar-actions')
    <a href="{{ route('ordenes-trabajo.create') }}" class="gbtn gbtn-primary gbtn-sm">+ Nueva orden</a>
@endsection

@section('content')

@php
    // Los estados que maneja la OT (ver CLAUDE.md). Las clases be-* ya existen
    // en el CSS del layout, las mismas que usa el show.
    $estados = ['borrador', 'en_produccion', 'lista', 'entregada', 'cancelada'];
    $etiqueta = fn ($e) => ucfirst(str_replace('_', ' ', $e));
@endphp

{{-- Filtros: el controller ya los soportaba (estado / desde / hasta), pero la
     pantalla vieja no los exponía, así que eran código muerto desde la UI. --}}
<form method="GET" style="display:flex;gap:10px;align-items:flex-end;margin-bottom:14px;flex-wrap:wrap">
    <div class="gfg" style="margin:0">
        <label class="glabel">Estado</label>
        <select name="estado" class="gselect" style="width:auto;min-width:150px">
            <option value="">Todos</option>
            @foreach($estados as $e)
                <option value="{{ $e }}" {{ request('estado') === $e ? 'selected' : '' }}>{{ $etiqueta($e) }}</option>
            @endforeach
        </select>
    </div>
    <div class="gfg" style="margin:0">
        <label class="glabel">Recibida desde</label>
        <input type="date" name="desde" value="{{ request('desde') }}" class="ginput" style="width:auto">
    </div>
    <div class="gfg" style="margin:0">
        <label class="glabel">Hasta</label>
        <input type="date" name="hasta" value="{{ request('hasta') }}" class="ginput" style="width:auto">
    </div>
    <button class="gbtn gbtn-primary gbtn-sm">Filtrar</button>
    @if(request()->hasAny(['estado', 'desde', 'hasta']))
        <a href="{{ route('ordenes-trabajo.index') }}" class="gbtn gbtn-ghost gbtn-sm">Limpiar</a>
    @endif
</form>

<div class="gcard">
    <div class="gcard-hd">
        <span class="gcard-title">Órdenes</span>
        <span class="txd" style="font-size:12px">
            @if($ordenes->total() > $ordenes->count())
                {{ $ordenes->firstItem() }}–{{ $ordenes->lastItem() }} de {{ $ordenes->total() }}
            @else
                {{ $ordenes->total() }} {{ $ordenes->total() === 1 ? 'orden' : 'órdenes' }}
            @endif
        </span>
    </div>
    <div class="gcard-bd" style="padding:0">
        <table class="gtable">
            <thead>
                <tr>
                    <th style="width:70px">N°</th>
                    <th>Cliente</th>
                    <th style="width:100px">Recibida</th>
                    <th style="width:190px">Trabajos</th>
                    <th style="width:120px">Estado</th>
                    <th>Observaciones</th>
                    <th style="width:150px"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($ordenes as $orden)
                    @php
                        $total      = $orden->trabajos->count();
                        $terminados = $orden->trabajos->where('estado', 'terminado')->count();
                        $pct        = $total > 0 ? round($terminados / $total * 100) : 0;
                    @endphp
                    <tr>
                        <td class="mono" style="color:var(--ac);font-weight:600">#{{ $orden->id }}</td>
                        <td style="color:var(--tx)">{{ $orden->cliente->nombre ?? 'Sin cliente' }}</td>
                        <td class="txd" style="font-size:12px">
                            @if($orden->fecha_recibido)
                                {{ \Carbon\Carbon::parse($orden->fecha_recibido)->format('d/m/Y') }}
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            @if($total === 0)
                                <span class="txd" style="font-size:12px">sin trabajos</span>
                            @else
                                <div class="gprog" style="margin-bottom:3px">
                                    <div class="gprog-fill" style="width:{{ $pct }}%"></div>
                                </div>
                                <span class="txd" style="font-size:11px">
                                    {{ $terminados }} de {{ $total }} terminados · {{ $pct }}%
                                </span>
                            @endif
                        </td>
                        <td>
                            <span class="badge-estado be-{{ $orden->estado }}">{{ $etiqueta($orden->estado) }}</span>
                        </td>
                        <td class="txd" style="font-size:12px">
                            {{ $orden->observaciones ? \Illuminate\Support\Str::limit($orden->observaciones, 70) : '—' }}
                        </td>
                        <td style="text-align:right;white-space:nowrap">
                            <a href="{{ route('ordenes-trabajo.show', $orden->id) }}" class="gbtn gbtn-ghost gbtn-xs">Ver</a>
                            <a href="{{ route('ordenes-trabajo.print', $orden->id) }}" class="gbtn gbtn-ghost gbtn-xs"
                               target="_blank" title="Imprimir">🖨</a>
                            <form action="{{ route('ordenes-trabajo.destroy', $orden->id) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('¿Eliminar la orden #{{ $orden->id }}?')">
                                @csrf @method('DELETE')
                                <button class="gbtn gbtn-danger gbtn-xs">×</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center;color:var(--txd);padding:32px">
                            @if(request()->hasAny(['estado', 'desde', 'hasta']))
                                Ninguna orden coincide con el filtro.
                                <a href="{{ route('ordenes-trabajo.index') }}" style="color:var(--ac)">Ver todas</a>
                            @else
                                No hay órdenes de trabajo todavía.
                                <a href="{{ route('ordenes-trabajo.create') }}" style="color:var(--ac)">Crear la primera</a>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- La pantalla vieja NO renderizaba esto, con paginate(10): a partir de la
         orden 11 no había forma de llegar al resto. --}}
    @if($ordenes->hasPages())
        <div class="gcard-bd">{{ $ordenes->links() }}</div>
    @endif
</div>

@endsection

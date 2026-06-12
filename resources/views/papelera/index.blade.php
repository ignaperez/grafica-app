@extends('layouts.app')

@section('page-title', 'Papelera')

@section('content')

<p class="txd" style="margin-bottom:20px;font-size:13px">
    Registros eliminados. No se borran de la base — quedan acá para auditoría y los podés
    <strong style="color:var(--tx)">restaurar</strong> cuando quieras.
</p>

@php
    $fecha = fn ($d) => $d ? \Carbon\Carbon::parse($d)->format('d/m/Y H:i') : '—';
@endphp

{{-- ══ CLIENTES ══ --}}
<div class="gcard" style="margin-bottom:20px">
    <div class="gcard-hd">
        <span class="gcard-title">Clientes eliminados <span class="txd">({{ $clientes->count() }})</span></span>
    </div>
    <div class="gcard-bd" style="padding:0">
        @if($clientes->isEmpty())
            <div class="txd" style="padding:18px">No hay clientes eliminados.</div>
        @else
        <table class="gtable">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th style="width:160px">CUIT</th>
                    <th style="width:160px">Eliminado el</th>
                    <th style="width:120px"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($clientes as $c)
                <tr>
                    <td>{{ $c->nombre }}</td>
                    <td class="mono">{{ $c->cuit ?: '—' }}</td>
                    <td class="txd">{{ $fecha($c->deleted_at) }}</td>
                    <td>
                        <form method="POST" action="{{ route('papelera.restore', ['tipo' => 'cliente', 'id' => $c->id]) }}">
                            @csrf
                            <button class="gbtn gbtn-ghost gbtn-sm" type="submit">↺ Restaurar</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>

{{-- ══ PRESUPUESTOS ══ --}}
<div class="gcard" style="margin-bottom:20px">
    <div class="gcard-hd">
        <span class="gcard-title">Presupuestos eliminados <span class="txd">({{ $presupuestos->count() }})</span></span>
    </div>
    <div class="gcard-bd" style="padding:0">
        @if($presupuestos->isEmpty())
            <div class="txd" style="padding:18px">No hay presupuestos eliminados.</div>
        @else
        <table class="gtable">
            <thead>
                <tr>
                    <th style="width:110px">N°</th>
                    <th>Cliente</th>
                    <th style="width:140px;text-align:right">Total</th>
                    <th style="width:160px">Eliminado el</th>
                    <th style="width:120px"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($presupuestos as $p)
                <tr>
                    <td class="mono">{{ $p->numeroFormateado() }}</td>
                    <td>{{ $p->cliente?->nombre ?? '—' }}</td>
                    <td class="mono" style="text-align:right">${{ number_format($p->total, 2, ',', '.') }}</td>
                    <td class="txd">{{ $fecha($p->deleted_at) }}</td>
                    <td>
                        <form method="POST" action="{{ route('papelera.restore', ['tipo' => 'presupuesto', 'id' => $p->id]) }}">
                            @csrf
                            <button class="gbtn gbtn-ghost gbtn-sm" type="submit">↺ Restaurar</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>

{{-- ══ REMITOS ══ --}}
<div class="gcard" style="margin-bottom:20px">
    <div class="gcard-hd">
        <span class="gcard-title">Remitos eliminados <span class="txd">({{ $remitos->count() }})</span></span>
    </div>
    <div class="gcard-bd" style="padding:0">
        @if($remitos->isEmpty())
            <div class="txd" style="padding:18px">No hay remitos eliminados.</div>
        @else
        <table class="gtable">
            <thead>
                <tr>
                    <th style="width:110px">N°</th>
                    <th>Cliente</th>
                    <th style="width:140px">Estado</th>
                    <th style="width:160px">Eliminado el</th>
                    <th style="width:120px"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($remitos as $r)
                <tr>
                    <td class="mono">{{ $r->numeroFormateado() }}</td>
                    <td>{{ $r->cliente?->nombre ?? '—' }}</td>
                    <td>{{ $r->estadoLabel() }}</td>
                    <td class="txd">{{ $fecha($r->deleted_at) }}</td>
                    <td>
                        <form method="POST" action="{{ route('papelera.restore', ['tipo' => 'remito', 'id' => $r->id]) }}">
                            @csrf
                            <button class="gbtn gbtn-ghost gbtn-sm" type="submit">↺ Restaurar</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>

@endsection

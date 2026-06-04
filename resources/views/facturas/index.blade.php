@extends('layouts.app')

@section('page-title', 'Facturas')

@section('topbar-actions')
    <a href="{{ route('facturas.create') }}" class="gbtn gbtn-primary gbtn-sm">+ Nueva factura</a>
@endsection

@section('content')

<div class="gcard">
    <div class="gcard-hd">
        <span class="gcard-title">Comprobantes emitidos</span>
        <span class="txd" style="font-size:12px">{{ $facturas->count() }} registros</span>
    </div>
    <div class="gcard-bd" style="padding:0">
        <table class="gtable">
            <thead>
                <tr>
                    <th>N° Comprobante</th>
                    <th>Tipo</th>
                    <th>Cliente</th>
                    <th>Fecha</th>
                    <th style="text-align:right">Total</th>
                    <th>CAE</th>
                    <th>Estado</th>
                    <th style="width:120px"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($facturas as $f)
                <tr>
                    <td>
                        <span class="mono" style="color:var(--ac);font-weight:600">{{ $f->numeroFormateado() }}</span>
                    </td>
                    <td style="font-size:12px">{{ $f->tipoLabel() }}</td>
                    <td style="color:var(--tx)">{{ $f->cliente->nombre }}</td>
                    <td class="txd">{{ $f->fecha->format('d/m/Y') }}</td>
                    <td style="text-align:right" class="mono">
                        <strong>${{ number_format($f->imp_total, 2, ',', '.') }}</strong>
                    </td>
                    <td>
                        @if($f->tieneCAE())
                            <span class="mono" style="font-size:11px;color:var(--green)">{{ $f->cae }}</span>
                        @else
                            <span class="txd">—</span>
                        @endif
                    </td>
                    <td>
                        <span style="color:{{ $f->estadoColor() }};font-size:12px">
                            ● {{ $f->estadoLabel() }}
                        </span>
                    </td>
                    <td style="text-align:right">
                        <a href="{{ route('facturas.show', $f->id) }}" class="gbtn gbtn-ghost gbtn-xs">Ver</a>
                        <a href="{{ route('facturas.print', $f->id) }}" class="gbtn gbtn-ghost gbtn-xs" target="_blank">🖨</a>
                        <a href="{{ route('facturas.print', $f->id) }}?auto=1" class="gbtn gbtn-ghost gbtn-xs" target="_blank" title="Descargar PDF">⬇</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align:center;color:var(--txd);padding:32px">
                        No hay facturas emitidas.
                        <a href="{{ route('facturas.create') }}" style="color:var(--ac)">Emitir la primera</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

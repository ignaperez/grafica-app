@extends('layouts.app')

@section('page-title', 'Facturas')

@section('topbar-actions')
    <a href="{{ route('facturas.create') }}" class="gbtn gbtn-primary gbtn-sm">+ Nueva factura</a>
@endsection

@section('content')

@if(isset($borradores) && $borradores->count())
<div class="gcard" style="margin-bottom:16px;border-color:#3a2a14">
    <div class="gcard-hd" style="background:#1a1206">
        <span class="gcard-title" style="color:#e0a23a">💾 Borradores pendientes</span>
        <span class="txd" style="font-size:12px">{{ $borradores->count() }} sin emitir</span>
    </div>
    <div class="gcard-bd" style="padding:0">
        <table class="gtable">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th style="text-align:right">Total estimado</th>
                    <th>Motivo</th>
                    <th>Guardado</th>
                    <th style="width:170px"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($borradores as $b)
                <tr>
                    <td style="color:var(--tx)">{{ $b->cliente->nombre ?? 'Sin cliente' }}</td>
                    <td style="text-align:right" class="mono">${{ number_format($b->total, 2, ',', '.') }}</td>
                    <td class="txd" style="font-size:12px;max-width:320px">{{ \Illuminate\Support\Str::limit($b->error, 80) ?: '—' }}</td>
                    <td class="txd" style="font-size:12px">{{ $b->updated_at->format('d/m/Y H:i') }}</td>
                    <td style="text-align:right">
                        <a href="{{ route('facturas.create', ['borrador_id' => $b->id]) }}" class="gbtn gbtn-primary gbtn-xs">Retomar</a>
                        <form method="POST" action="{{ route('facturas.borradores.destroy', $b->id) }}" style="display:inline"
                              onsubmit="return confirm('¿Eliminar este borrador?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="gbtn gbtn-danger gbtn-xs">Eliminar</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

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
                        @if(in_array($f->tipo, [1, 6, 11]))
                        <a href="{{ route('remitos.create', ['factura_id' => $f->id]) }}" class="gbtn gbtn-ghost gbtn-xs" title="Crear remito">📦 Remito</a>
                        @endif
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

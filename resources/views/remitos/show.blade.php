@extends('layouts.app')

@section('page-title', 'Remito ' . $remito->numeroFormateado())

@section('topbar-actions')
    <a href="{{ route('remitos.index') }}" class="gbtn gbtn-ghost gbtn-sm">← Volver</a>
    <a href="{{ route('remitos.print', $remito->id) }}" class="gbtn gbtn-ghost gbtn-sm" target="_blank">🖨 Imprimir</a>
@endsection

@section('content')

<div style="display:grid;grid-template-columns:1fr 260px;gap:16px;align-items:start">

{{-- ── Columna principal ──────────────────────────────────────────────── --}}
<div>

    {{-- Encabezado --}}
    <div class="gcard" style="margin-bottom:16px">
        <div class="gcard-hd">
            <span class="gcard-title">{{ $remito->numeroFormateado() }}</span>
            <span style="color:{{ $remito->estadoColor() }};font-size:13px;font-weight:600">
                ● {{ $remito->estadoLabel() }}
            </span>
        </div>
        <div class="gcard-bd">
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px">

                <div>
                    <div class="txd" style="font-size:11px;margin-bottom:2px">CLIENTE</div>
                    <div style="font-weight:600">{{ $remito->cliente?->nombre ?? '—' }}</div>
                    @if($remito->cliente?->email)
                        <div class="txd" style="font-size:12px">{{ $remito->cliente->email }}</div>
                    @endif
                    @if($remito->cliente?->telefono)
                        <div class="txd" style="font-size:12px">{{ $remito->cliente->telefono }}</div>
                    @endif
                </div>

                <div>
                    <div class="txd" style="font-size:11px;margin-bottom:2px">FECHA</div>
                    <div>{{ $remito->fecha->format('d/m/Y') }}</div>
                </div>

                <div>
                    <div class="txd" style="font-size:11px;margin-bottom:2px">EMITIDO POR</div>
                    <div style="font-size:13px">{{ $remito->createdBy?->name ?? '—' }}</div>
                    <div class="txd" style="font-size:11px">{{ $remito->created_at->format('d/m/Y H:i') }}</div>
                </div>

                {{-- Vínculos --}}
                @if($remito->presupuesto || $remito->factura)
                <div style="grid-column:1/-1;border-top:1px solid var(--b);padding-top:12px;margin-top:4px;display:flex;gap:32px">
                    @if($remito->presupuesto)
                    <div>
                        <div class="txd" style="font-size:11px;margin-bottom:2px">PRESUPUESTO</div>
                        <a href="{{ route('presupuestos.show', $remito->presupuesto_id) }}"
                           style="color:var(--ac);font-family:var(--mono);font-size:13px;text-decoration:none">
                            {{ $remito->presupuesto->numeroFormateado() }}
                        </a>
                    </div>
                    @endif
                    @if($remito->factura)
                    <div>
                        <div class="txd" style="font-size:11px;margin-bottom:2px">FACTURA</div>
                        <a href="{{ route('facturas.show', $remito->factura_id) }}"
                           style="color:var(--ac);font-family:var(--mono);font-size:13px;text-decoration:none">
                            {{ $remito->factura->numeroFormateado() }}
                        </a>
                    </div>
                    @endif
                </div>
                @endif

                @if($remito->observaciones)
                <div style="grid-column:1/-1;border-top:1px solid var(--b);padding-top:12px;margin-top:4px">
                    <div class="txd" style="font-size:11px;margin-bottom:4px">OBSERVACIONES</div>
                    <div style="font-size:13px;white-space:pre-line">{{ $remito->observaciones }}</div>
                </div>
                @endif

            </div>
        </div>
    </div>

    {{-- Ítems --}}
    <div class="gcard">
        <div class="gcard-hd"><span class="gcard-title">Detalle de ítems</span></div>
        <div class="gcard-bd" style="padding:0">
            <table class="gtable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Descripción</th>
                        <th style="text-align:center">Cantidad</th>
                        <th style="text-align:center">Unidad</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($remito->items as $item)
                    <tr>
                        <td class="txd" style="font-size:12px;width:36px">{{ $loop->iteration }}</td>
                        <td>{{ $item->descripcion }}</td>
                        <td class="mono" style="text-align:center">
                            {{ rtrim(rtrim(number_format($item->cantidad, 3, ',', '.'), '0'), ',') }}
                        </td>
                        <td style="text-align:center" class="txd">{{ $item->unidad }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- ── Columna lateral — Acciones ─────────────────────────────────────── --}}
<div style="display:flex;flex-direction:column;gap:16px">

    {{-- CAI fiscal --}}
    @if($remito->tieneCai() && $remito->remitoCai)
    <div class="gcard">
        <div class="gcard-hd">
            <span class="gcard-title">CAI fiscal</span>
            <span style="font-size:11px;color:var(--green, #4caf50)">● Vigente</span>
        </div>
        <div class="gcard-bd" style="font-size:12px;line-height:1.8">
            <div class="txd" style="font-size:10px;letter-spacing:.08em;text-transform:uppercase">N° comprobante</div>
            <div class="mono" style="font-size:15px;font-weight:600;color:var(--tx)">{{ $remito->numeroFormateado() }}</div>
            <div style="margin-top:8px">
                <div class="txd" style="font-size:10px;letter-spacing:.08em;text-transform:uppercase">Código CAI</div>
                <div class="mono" style="font-size:12px">{{ $remito->remitoCai->codigo }}</div>
            </div>
            <div style="margin-top:8px">
                <div class="txd" style="font-size:10px;letter-spacing:.08em;text-transform:uppercase">Vencimiento</div>
                <div>{{ $remito->remitoCai->vencimiento->format('d/m/Y') }}</div>
            </div>
        </div>
    </div>
    @elseif(!$remito->tieneCai())
    <div class="gcard" style="border-color:rgba(230,80,42,.3)">
        <div class="gcard-bd" style="font-size:12px;color:var(--txd)">
            Sin CAI fiscal — documento interno.
            @if(auth()->user()->rol === 'admin')
                <a href="{{ route('remito-cais.index') }}" style="color:var(--ac)">Gestionar CAI →</a>
            @endif
        </div>
    </div>
    @endif

    <div class="gcard">
        <div class="gcard-hd"><span class="gcard-title">Estado</span></div>
        <div class="gcard-bd">
            <form method="POST" action="{{ route('remitos.estado', $remito->id) }}">
                @csrf @method('PATCH')
                <div class="gfg" style="margin-bottom:12px">
                    <label class="glabel">Cambiar estado</label>
                    <select name="estado" class="gselect">
                        <option value="pendiente"  {{ $remito->estado === 'pendiente'  ? 'selected' : '' }}>Pendiente</option>
                        <option value="entregado"  {{ $remito->estado === 'entregado'  ? 'selected' : '' }}>Entregado</option>
                        <option value="cancelado"  {{ $remito->estado === 'cancelado'  ? 'selected' : '' }}>Cancelado</option>
                    </select>
                </div>
                <button type="submit" class="gbtn gbtn-ghost gbtn-sm" style="width:100%;justify-content:center">
                    Actualizar
                </button>
            </form>
        </div>
    </div>

    <form action="{{ route('remitos.destroy', $remito->id) }}" method="POST"
          onsubmit="return confirm('¿Eliminar el remito {{ $remito->numeroFormateado() }}?')">
        @csrf @method('DELETE')
        <button class="gbtn gbtn-danger" style="width:100%;justify-content:center">
            Eliminar remito
        </button>
    </form>

</div>
</div>{{-- /grid --}}

@endsection

@extends('layouts.app')

@section('page-title', 'Lista de precios' . ($cliente ? ' — ' . $cliente->nombre : ''))

@section('topbar-actions')
    <a href="{{ route('catalogo.index') }}" class="gbtn gbtn-ghost gbtn-sm">← Catálogo</a>
    <button onclick="window.print()" class="gbtn gbtn-primary gbtn-sm">🖨 Imprimir</button>
@endsection

@section('content')
<style>
    @media print {
        .sidebar, .topbar, .no-print { display: none !important; }
        .print-header { display: block !important; }
        body, .main-content { background: white !important; color: black !important; }
        .gtable th, .gtable td { border-color: #ccc !important; color: black !important; }
        .price-val { color: black !important; font-weight: 700 !important; }
    }
    .print-header { display: none; }
</style>

{{-- Selector de cliente --}}
<div class="gcard mb-4 no-print">
    <div class="gcard-bd" style="padding:14px 20px">
        <form method="GET" action="{{ route('catalogo.print') }}"
              style="display:flex;align-items:flex-end;gap:16px">
            <div class="gfg mb-0" style="flex:1;max-width:400px">
                <label class="glabel">Aplicar lista de precios de un cliente</label>
                <select name="cliente_id" class="gselect">
                    <option value="">— Sin cliente (mostrar costo base) —</option>
                    @foreach($clientes as $c)
                        <option value="{{ $c->id }}" {{ $cliente?->id == $c->id ? 'selected' : '' }}>
                            {{ $c->nombre }}
                            @if($c->listaPrecio)
                                (×{{ $c->listaPrecio->multiplicador }})
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="gbtn gbtn-ghost gbtn-sm" style="margin-bottom:1px">Aplicar</button>
        </form>

        @if($cliente && $multiplicador != 1)
        <div style="margin-top:8px;font-size:12px;color:var(--txd)">
            Multiplicador: <span class="mono" style="color:var(--ac)">×{{ $multiplicador }}</span>
            &nbsp;·&nbsp; {{ $cliente->listaPrecio?->nombre }}
            @if($cliente->listaPrecio?->mo_m2 !== null)
                &nbsp;·&nbsp; MO personalizada: <span class="mono" style="color:var(--ac)">${{ number_format($mo['m2'],2) }}/m²</span>
            @endif
        </div>
        @endif
    </div>
</div>

{{-- Encabezado para impresión --}}
<div class="print-header" style="margin-bottom:24px;padding-bottom:12px;border-bottom:2px solid #000">
    <div style="font-size:22px;font-weight:700">Lista de precios</div>
    @if($cliente)
        <div style="font-size:14px;margin-top:4px">Cliente: <strong>{{ $cliente->nombre }}</strong></div>
    @endif
    <div style="font-size:11px;color:#888;margin-top:4px">{{ \Carbon\Carbon::now()->isoFormat('D [de] MMMM [de] YYYY') }}</div>
</div>

@if($grupos->isEmpty())
<div class="gcard">
    <div class="gcard-bd" style="padding:40px;text-align:center;color:var(--txd)">
        No hay servicios configurados.
        <a href="{{ route('materiales.create') }}" style="color:var(--ac)">Agregar materiales</a>
    </div>
</div>
@else

@foreach($grupos as $tipo => $items)
<div class="gcard mb-4">
    <div class="gcard-hd">
        <span class="gcard-title">{{ $tipo }}</span>
    </div>
    <div class="gcard-bd" style="padding:0">
        <table class="gtable">
            <thead>
                <tr>
                    <th>Servicio</th>
                    <th>Material</th>
                    <th style="text-align:right">
                        {{ $multiplicador != 1 ? 'Precio' : 'Costo' }}
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $c)
                @php
                    $precio = round($c['costo'] * $multiplicador, 2);
                    $label  = match($c['unidad']) { 'ml' => 'ml', 'unidad' => 'unidad', default => 'm²' };
                @endphp
                <tr>
                    <td style="font-weight:600;color:var(--tx)">{{ $c['maquina']->nombre }}</td>
                    <td class="txd">{{ $c['material']->nombre }}</td>
                    <td style="text-align:right" class="mono price-val">
                        <strong style="color:var(--ac);font-size:14px">${{ number_format($precio, 2) }}</strong>
                        <span class="txd" style="font-size:11px"> / {{ $label }}</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endforeach

@endif

@endsection

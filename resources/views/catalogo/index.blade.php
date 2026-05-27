@extends('layouts.app')

@section('page-title', 'Catálogo de servicios')

@section('topbar-actions')
    <a href="{{ route('catalogo.print') }}" class="gbtn gbtn-ghost gbtn-sm" target="_blank">🖨 Imprimir lista</a>
    <a href="{{ route('materiales.index') }}" class="gbtn gbtn-ghost gbtn-sm">Materiales</a>
    <a href="{{ route('maquinas.index') }}" class="gbtn gbtn-ghost gbtn-sm">Máquinas</a>
@endsection

@section('content')

@if($grupos->isEmpty())
<div class="gcard">
    <div class="gcard-bd" style="padding:40px;text-align:center;color:var(--txd)">
        <div style="font-size:32px;margin-bottom:12px">⚙️</div>
        <div style="font-weight:600;color:var(--tx);margin-bottom:8px">No hay servicios configurados</div>
        <div style="font-size:13px;margin-bottom:20px">
            Cargá materiales y asignales las máquinas en que se pueden imprimir.
        </div>
        <a href="{{ route('materiales.create') }}" class="gbtn gbtn-primary gbtn-sm">Ir a Materiales</a>
    </div>
</div>
@else

{{-- MO activa --}}
@if($mo['m2'] > 0 || $mo['ml'] > 0 || $mo['unidad'] > 0)
<div style="margin-bottom:16px;font-size:12px;color:var(--txd)">
    MO colocación global:
    @if($mo['m2'] > 0) <span class="mono" style="color:var(--ac)">${{ number_format($mo['m2'],2) }}/m²</span> @endif
    @if($mo['ml'] > 0) <span class="mono" style="color:var(--ac)">${{ number_format($mo['ml'],2) }}/ml</span> @endif
    @if($mo['unidad'] > 0) <span class="mono" style="color:var(--ac)">${{ number_format($mo['unidad'],2) }}/u</span> @endif
    — <a href="{{ route('configuracion.edit') }}" style="color:var(--txd)">cambiar</a>
</div>
@endif

@foreach($grupos as $tipo => $items)
<div class="gcard mb-4">
    <div class="gcard-hd">
        <span class="gcard-title">{{ $tipo }}</span>
        <span class="txd" style="font-size:12px">{{ $items->count() }} servicio(s)</span>
    </div>
    <div class="gcard-bd" style="padding:0">
        <table class="gtable">
            <thead>
                <tr>
                    <th>Servicio</th>
                    <th>Material</th>
                    <th style="text-align:right">Costo base</th>
                    <th style="text-align:right">+ MO</th>
                    <th style="text-align:right">Total costo</th>
                    <th style="width:60px"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $c)
                @php
                    $label = match($c['unidad']) { 'ml' => 'ml', 'unidad' => 'u', default => 'm²' };
                @endphp
                <tr>
                    <td style="font-weight:600;color:var(--tx)">{{ $c['maquina']->nombre }}</td>
                    <td class="txd">{{ $c['material']->nombre }}</td>
                    <td style="text-align:right" class="mono txd">
                        ${{ number_format($c['maq'] + $c['mat'], 2) }}
                    </td>
                    <td style="text-align:right" class="mono txd">
                        {{ $c['mo'] > 0 ? '+$' . number_format($c['mo'], 2) : '—' }}
                    </td>
                    <td style="text-align:right" class="mono">
                        <strong style="color:var(--ac)">${{ number_format($c['costo'], 2) }}</strong>
                        <span class="txd" style="font-size:11px"> / {{ $label }}</span>
                    </td>
                    <td style="text-align:right">
                        <a href="{{ route('materiales.edit', $c['material']->id) }}"
                           style="font-size:11px;color:var(--txd)">editar</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endforeach

<div class="gcard" style="border-color:var(--bm)">
    <div class="gcard-bd" style="padding:12px 20px">
        <div class="txd" style="font-size:12px;line-height:1.7">
            <strong style="color:var(--tx)">Costo total</strong> =
            <span class="mono">costo_máquina + costo_material + MO_colocación</span>
            &nbsp;·&nbsp;
            <strong style="color:var(--tx)">Precio de venta</strong> =
            <span class="mono">costo_total × multiplicador_lista_cliente</span>
            &nbsp;·&nbsp;
            Usá <a href="{{ route('catalogo.print') }}" style="color:var(--ac)">Imprimir lista</a>
            para ver precios por cliente.
        </div>
    </div>
</div>

@endif

@endsection

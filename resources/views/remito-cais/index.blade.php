@extends('layouts.app')

@section('page-title', 'CAI — Códigos de Autorización de Impresión')

@section('topbar-actions')
    <a href="{{ route('remito-cais.create') }}" class="gbtn gbtn-primary gbtn-sm">+ Cargar nuevo CAI</a>
@endsection

@section('content')

{{-- Alerta si no hay CAI vigente --}}
@php $vigente = \App\Models\RemitoCai::vigente(); @endphp
@if(!$vigente)
<div style="background:rgba(230,80,42,.12);border:1px solid var(--ac);border-radius:6px;padding:12px 16px;margin-bottom:16px;color:var(--ac);font-size:13px">
    ⚠️ <strong>No hay CAI vigente.</strong>
    Los remitos que emitas no tendrán número fiscal oficial.
    <a href="{{ route('remito-cais.create') }}" style="color:var(--ac);text-decoration:underline">Cargar un CAI →</a>
</div>
@elseif($vigente->casiAgotado())
<div style="background:rgba(245,158,11,.12);border:1px solid #f59e0b;border-radius:6px;padding:12px 16px;margin-bottom:16px;color:#f59e0b;font-size:13px">
    ⚠️ <strong>CAI casi agotado.</strong>
    Quedan <strong>{{ $vigente->restantes() }}</strong> números disponibles.
    Tramitar el próximo CAI en ARCA antes de que se agote.
</div>
@endif

<div class="gcard">
    <div class="gcard-hd">
        <span class="gcard-title">CAI registrados</span>
        <span class="txd" style="font-size:12px">{{ $cais->count() }} registros</span>
    </div>
    <div class="gcard-bd" style="padding:0">
        <table class="gtable">
            <thead>
                <tr>
                    <th>Código CAI</th>
                    <th>PV</th>
                    <th>Rango</th>
                    <th>Usados</th>
                    <th>Restantes</th>
                    <th>Vencimiento</th>
                    <th>Estado</th>
                    <th style="width:80px"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($cais as $cai)
                <tr style="{{ $cai->trashed() ? 'opacity:.45' : '' }}">
                    <td class="mono" style="font-size:12px;letter-spacing:.04em">{{ $cai->codigo }}</td>
                    <td class="mono">{{ str_pad($cai->punto_venta, 4, '0', STR_PAD_LEFT) }}</td>
                    <td style="font-size:12px">
                        <span class="mono">{{ number_format($cai->numero_desde) }}</span>
                        <span class="txd">→</span>
                        <span class="mono">{{ number_format($cai->numero_hasta) }}</span>
                    </td>
                    <td style="font-size:12px">
                        {{ number_format($cai->usados()) }}
                        <span class="txd">({{ $cai->porcentajeUso() }}%)</span>
                    </td>
                    <td style="font-size:12px">
                        <strong>{{ number_format($cai->restantes()) }}</strong>
                    </td>
                    <td style="font-size:12px">
                        {{ $cai->vencimiento->format('d/m/Y') }}
                        @if($cai->vencido() && !$cai->trashed())
                            <span style="color:var(--ac);font-size:11px"> (vencido)</span>
                        @endif
                    </td>
                    <td>
                        @if($cai->trashed())
                            <span style="color:var(--txd);font-size:12px">● Archivado</span>
                        @else
                            <span style="color:{{ $cai->estadoColor() }};font-size:12px">● {{ $cai->estadoLabel() }}</span>
                        @endif
                    </td>
                    <td style="text-align:right">
                        @if(!$cai->trashed())
                        <form action="{{ route('remito-cais.destroy', $cai->id) }}" method="POST"
                              onsubmit="return confirm('¿Archivar este CAI?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="gbtn gbtn-ghost gbtn-xs" style="color:var(--txd)">Archivar</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align:center;color:var(--txd);padding:32px">
                        No hay CAI registrados.
                        <a href="{{ route('remito-cais.create') }}" style="color:var(--ac)">Cargar el primero →</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Explicación qué es el CAI --}}
<div class="gcard" style="margin-top:16px">
    <div class="gcard-hd"><span class="gcard-title" style="font-size:13px">¿Qué es el CAI?</span></div>
    <div class="gcard-bd" style="font-size:13px;color:var(--txd);line-height:1.7">
        El <strong style="color:var(--tx)">Código de Autorización de Impresión (CAI)</strong> es otorgado por ARCA para
        documentos fiscales pre-impresos (Remitos R, Facturas M, etc.).<br>
        Se tramita en <a href="https://auth.afip.gob.ar" target="_blank" style="color:var(--ac)">ARCA → Mis aplicaciones web → Autorización de Impresión de Comprobantes</a>.<br>
        ARCA asigna un código de 14 dígitos, un rango de números autorizados y una fecha de vencimiento.
        Esta app asigna automáticamente el próximo número disponible a cada remito que emitas.
    </div>
</div>

@endsection

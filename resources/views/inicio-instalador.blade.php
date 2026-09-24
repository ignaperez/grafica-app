@extends('layouts.app')

@section('page-title', 'Mis vehículos')

@section('content')
<style>
    .kpis { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 18px; }
    @media (max-width: 560px) { .kpis { gap: 8px; } }

    .kpi {
        background: var(--bg-s); border: 1px solid var(--b); border-radius: 12px;
        padding: 16px 14px; text-align: center;
    }
    .kpi-num   { font-family: var(--mono); font-size: 34px; font-weight: 700; line-height: 1; }
    .kpi-label { font-size: 10px; letter-spacing: 1.5px; text-transform: uppercase;
                 color: var(--txd); margin-top: 7px; }
    @media (max-width: 560px) { .kpi-num { font-size: 26px; } .kpi-label { font-size: 9px; letter-spacing: 1px; } }

    .veh-fila {
        display: flex; align-items: center; justify-content: space-between; gap: 10px;
        padding: 11px 13px; border: 1px solid var(--b); border-radius: 10px;
        background: var(--bg-s); margin-bottom: 7px; text-decoration: none;
    }
    .veh-fila:hover { background: var(--bg-h); border-color: var(--bm); }
    .veh-pat { font-family: var(--mono); font-size: 15px; font-weight: 700;
               letter-spacing: 2px; color: var(--tx); }
    .veh-sub { font-size: 11.5px; color: var(--txd); margin-top: 2px; }
    .veh-cta { font-size: 11px; color: var(--ac); white-space: nowrap; }
    .bloque-tit { font-size: 11px; letter-spacing: 2px; text-transform: uppercase;
                  color: #444; margin: 22px 0 10px; }
</style>

<div class="kpis">
    <div class="kpi">
        <div class="kpi-num" style="color:var(--tx)">{{ $asignados }}</div>
        <div class="kpi-label">Asignados</div>
    </div>
    <div class="kpi" style="{{ $pendientes > 0 ? 'border-color:#4a2f14' : '' }}">
        <div class="kpi-num" style="color:{{ $pendientes > 0 ? '#f59e0b' : 'var(--txm)' }}">{{ $pendientes }}</div>
        <div class="kpi-label">Pendientes</div>
    </div>
    <div class="kpi">
        <div class="kpi-num" style="color:{{ $terminados > 0 ? '#22c55e' : 'var(--txm)' }}">{{ $terminados }}</div>
        <div class="kpi-label">Terminados</div>
    </div>
</div>

{{-- ── Lo que falta hacer ─────────────────────────────────────────── --}}
<div class="bloque-tit">Pendientes {{ $pendientes > $listaPendientes->count() ? '(' . $listaPendientes->count() . ' de ' . $pendientes . ')' : '' }}</div>

@forelse($listaPendientes as $v)
    <a href="{{ route('vehiculos-ploteo.edit', $v->id) }}" class="veh-fila">
        <div style="min-width:0">
            <div class="veh-pat">{{ $v->patente }}</div>
            <div class="veh-sub">
                {{ $v->marca }} {{ $v->modelo }}
                @if($v->cliente) · {{ $v->cliente->nombre }} @endif
                @if($v->fecha_ploteo) · {{ $v->fecha_ploteo->format('d/m/Y') }} @endif
            </div>
        </div>
        <span class="veh-cta">Cargar fotos →</span>
    </a>
@empty
    <div class="gcard">
        <div class="gcard-bd" style="text-align:center;color:var(--txd);padding:26px">
            @if($asignados === 0)
                Todavía no tenés vehículos asignados.
            @else
                ✓ No te queda ninguno pendiente.
            @endif
        </div>
    </div>
@endforelse

{{-- ── Últimos terminados ─────────────────────────────────────────── --}}
@if($ultimosTerminados->isNotEmpty())
    <div class="bloque-tit">Últimos terminados</div>

    @foreach($ultimosTerminados as $v)
        <a href="{{ route('vehiculos-ploteo.show', $v->id) }}" class="veh-fila">
            <div style="min-width:0">
                <div class="veh-pat" style="color:var(--txd)">{{ $v->patente }}</div>
                <div class="veh-sub">
                    {{ $v->marca }} {{ $v->modelo }}
                    @if($v->cliente) · {{ $v->cliente->nombre }} @endif
                </div>
            </div>
            <span class="veh-cta" style="color:#22c55e">✓ Ver</span>
        </a>
    @endforeach
@endif

<div style="margin-top:22px;text-align:center">
    <a href="{{ route('vehiculos-ploteo.index') }}" class="gbtn gbtn-ghost gbtn-sm">Ver todos mis vehículos</a>
</div>
@endsection

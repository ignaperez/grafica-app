@extends('layouts.app')

@section('page-title', 'Reporte de producción')

@section('topbar-actions')
<form method="GET" style="display:flex;align-items:center;gap:8px">
    <input type="date" name="desde" class="ginput" style="width:140px;padding:5px 10px;font-size:12px"
           value="{{ $desde->format('Y-m-d') }}">
    <span style="color:var(--txd);font-size:12px">→</span>
    <input type="date" name="hasta" class="ginput" style="width:140px;padding:5px 10px;font-size:12px"
           value="{{ $hasta->format('Y-m-d') }}">
    <button type="submit" class="gbtn gbtn-ghost gbtn-sm">Filtrar</button>
    <a href="{{ route('reportes.produccion') }}" class="gbtn gbtn-ghost gbtn-sm">Este mes</a>
    <button onclick="window.print()" class="gbtn gbtn-ghost gbtn-sm no-print" style="margin-left:4px">🖨 Imprimir</button>
</form>
@endsection

@section('content')
<style>
/* KPIs */
.rep-kpis {
    display: grid;
    grid-template-columns: repeat(4,1fr);
    gap: 12px;
    margin-bottom: 20px;
}
@media(max-width:900px){ .rep-kpis { grid-template-columns: repeat(2,1fr); } }
@media(max-width:480px){ .rep-kpis { grid-template-columns: 1fr 1fr; } }

.rkpi {
    background: var(--bg-s);
    border: 1px solid var(--b);
    border-radius: 10px;
    padding: 18px 20px 16px;
    position: relative;
    overflow: hidden;
}
.rkpi::after {
    content:'';
    position:absolute;
    width:80px;height:80px;border-radius:50%;
    background:var(--kc,#fff);
    opacity:.04;
    top:-20px;right:-20px;
    pointer-events:none;
}
.rkpi-val {
    font-family: var(--mono);
    font-size: 38px;
    font-weight: 700;
    line-height: .95;
    color: var(--kc, var(--tx));
    letter-spacing: -2px;
    margin-bottom: 8px;
}
.rkpi-val sup {
    font-size: 14px;
    letter-spacing: 0;
    font-weight: 500;
    opacity: .7;
    margin-left: 2px;
}
.rkpi-label {
    font-size: 9.5px;
    font-weight: 700;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: var(--txd);
}
.rkpi-bar {
    position: absolute;
    bottom:0;left:0;right:0;
    height: 2px;
    background: var(--kc, transparent);
    opacity: .4;
}

/* Layout */
.rep-body {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 16px;
    align-items: start;
}
@media(max-width:900px){ .rep-body { grid-template-columns:1fr; } }

/* Panel */
.rpnl {
    background: var(--bg-s);
    border: 1px solid var(--b);
    border-radius: 10px;
    overflow: hidden;
}
.rpnl-hd {
    padding: 13px 18px;
    border-bottom: 1px solid var(--b);
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.rpnl-title {
    font-size: 10.5px;
    font-weight: 700;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: var(--txd);
}
.rpnl-sub {
    font-size: 11px;
    color: var(--txm);
    font-family: var(--mono);
}

/* Tabla materiales */
.mat-table { width:100%; border-collapse:collapse; }
.mat-table thead th {
    font-size: 9px;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: var(--txm);
    font-weight: 700;
    padding: 8px 18px;
    border-bottom: 1px solid var(--b);
    text-align: left;
}
.mat-table thead th:last-child { text-align:right; }
.mat-table tbody tr {
    border-bottom: 1px solid #141414;
    transition: background .1s;
}
.mat-table tbody tr:last-child { border-bottom:none; }
.mat-table tbody tr:hover { background: var(--bg-h); }
.mat-table td { padding: 13px 18px; vertical-align: middle; }
.mat-table td:last-child { text-align:right; }

/* Barra de porcentaje */
.mbar-wrap {
    display: flex;
    align-items: center;
    gap: 10px;
}
.mbar-track {
    flex: 1;
    height: 4px;
    background: #1c1c1c;
    border-radius: 2px;
    overflow: hidden;
    min-width: 60px;
}
.mbar-fill {
    height: 100%;
    border-radius: 2px;
    background: var(--ac);
    transition: width .6s ease;
}
.mbar-pct {
    font-family: var(--mono);
    font-size: 10px;
    color: var(--txd);
    width: 34px;
    text-align: right;
    flex-shrink: 0;
}

/* Ranking clientes */
.rank-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 12px 18px;
    border-bottom: 1px solid #141414;
    transition: background .1s;
}
.rank-item:last-child { border-bottom: none; }
.rank-item:hover { background: var(--bg-h); }
.rank-pos {
    font-family: var(--mono);
    font-size: 18px;
    font-weight: 700;
    color: var(--b);
    width: 24px;
    flex-shrink: 0;
    text-align: center;
}
.rank-pos.top1 { color: #c8a040; }
.rank-pos.top2 { color: #888; }
.rank-pos.top3 { color: #9a6040; }
.rank-info { flex: 1; min-width: 0; }
.rank-name {
    font-size: 13px;
    font-weight: 600;
    color: var(--tx);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.rank-meta {
    font-size: 11px;
    color: var(--txd);
    margin-top: 1px;
}
.rank-m2 {
    font-family: var(--mono);
    font-size: 14px;
    font-weight: 700;
    color: var(--tx);
    text-align: right;
    flex-shrink: 0;
}
.rank-m2 small {
    display: block;
    font-size: 9px;
    color: var(--txd);
    font-weight: 400;
    letter-spacing: .5px;
    text-transform: uppercase;
}

/* Estado vacío */
.empty-state {
    padding: 48px 20px;
    text-align: center;
    color: var(--txm);
    font-size: 13px;
}
.empty-state .es-icon {
    font-size: 32px;
    margin-bottom: 12px;
    opacity: .3;
}
.empty-state .es-msg { color: var(--txd); margin-bottom: 6px; }
.empty-state .es-sub { font-size: 11px; color: var(--txm); }

/* Período badge */
.period-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 12px;
    background: var(--bg-h);
    border: 1px solid var(--b);
    border-radius: 20px;
    font-size: 11px;
    color: var(--txd);
    font-family: var(--mono);
    margin-bottom: 18px;
}

/* ── Print ────────────────────────────────── */
.print-header { display: none; }

@media print {
    /* Ocultar chrome de la app */
    #sidebar,
    .topbar,
    .no-print { display: none !important; }

    /* El main no necesita margen izquierdo */
    #main {
        margin-left: 0 !important;
        padding: 0 !important;
    }

    body {
        background: #fff !important;
        color: #111 !important;
        font-size: 11pt;
    }

    /* Encabezado de impresión */
    .print-header {
        display: block !important;
        border-bottom: 2px solid #111;
        padding-bottom: 10px;
        margin-bottom: 18px;
    }
    .print-header h1 {
        font-size: 18pt;
        font-weight: 700;
        margin: 0 0 2px;
        color: #111;
    }
    .print-header p {
        font-size: 9.5pt;
        color: #444;
        margin: 0;
    }

    /* Periodo badge: solo texto */
    .period-badge {
        background: none !important;
        border: none !important;
        padding: 0 !important;
        font-size: 10pt;
        color: #444 !important;
        margin-bottom: 14px;
    }

    /* KPIs */
    .rep-kpis {
        grid-template-columns: repeat(4, 1fr) !important;
        gap: 8px !important;
        margin-bottom: 16px !important;
    }
    .rkpi {
        background: #f5f5f5 !important;
        border: 1px solid #ccc !important;
        border-radius: 6px !important;
        padding: 12px 14px !important;
        break-inside: avoid;
    }
    .rkpi::after { display: none !important; }
    .rkpi-val {
        font-size: 26pt !important;
        color: #111 !important;
        letter-spacing: -1px !important;
    }
    .rkpi-val sup { color: #444 !important; }
    .rkpi-label { color: #555 !important; }
    .rkpi-bar { background: #bbb !important; opacity: 1 !important; }

    /* Layout body */
    .rep-body {
        grid-template-columns: 1fr 280px !important;
        gap: 12px !important;
    }

    /* Paneles */
    .rpnl {
        background: #fff !important;
        border: 1px solid #ccc !important;
        border-radius: 6px !important;
        break-inside: avoid;
    }
    .rpnl-hd {
        background: #f5f5f5 !important;
        border-bottom: 1px solid #ccc !important;
    }
    .rpnl-title { color: #333 !important; }
    .rpnl-sub   { color: #555 !important; }

    /* Tabla materiales */
    .mat-table thead th {
        color: #555 !important;
        border-bottom: 1px solid #ccc !important;
    }
    .mat-table tbody tr {
        border-bottom: 1px solid #eee !important;
    }
    .mat-table tbody tr:hover { background: none !important; }
    .mat-table td { color: #111 !important; }

    /* Barra proporción */
    .mbar-track { background: #ddd !important; }
    .mbar-fill  { background: #e6502a !important; }
    .mbar-pct   { color: #444 !important; }

    /* Ranking clientes */
    .rank-item {
        border-bottom: 1px solid #eee !important;
        break-inside: avoid;
    }
    .rank-item:hover { background: none !important; }
    .rank-pos  { color: #bbb !important; }
    .rank-pos.top1 { color: #c8a040 !important; }
    .rank-pos.top2 { color: #888 !important; }
    .rank-pos.top3 { color: #9a6040 !important; }
    .rank-name { color: #111 !important; }
    .rank-meta { color: #555 !important; }
    .rank-m2   { color: #111 !important; }
    .rank-m2 small { color: #555 !important; }
}
</style>

{{-- Encabezado solo visible al imprimir --}}
<div class="print-header">
    <h1>Reporte de producción</h1>
    <p>
        {{ $desde->isoFormat('D MMM YYYY') }} → {{ $hasta->isoFormat('D MMM YYYY') }}
        &nbsp;·&nbsp; Generado el {{ \Carbon\Carbon::now()->isoFormat('D MMM YYYY, HH:mm') }}
    </p>
</div>

{{-- Período --}}
<div class="period-badge">
    📅 {{ $desde->isoFormat('D MMM YYYY') }} → {{ $hasta->isoFormat('D MMM YYYY') }}
    @if($desde->isSameMonth($hasta) && $desde->isSameYear($hasta))
        <span style="color:var(--ac)">· {{ $desde->isoFormat('MMMM YYYY') }}</span>
    @endif
    <span style="color:var(--green);margin-left:4px">· solo terminados</span>
</div>

{{-- KPIs --}}
<div class="rep-kpis">
    <div class="rkpi" style="--kc:var(--ac)">
        <div class="rkpi-val">
            {{ number_format($totalM2, 1) }}<sup>m²</sup>
        </div>
        <div class="rkpi-label">Total impreso</div>
        <div class="rkpi-bar"></div>
    </div>
    <div class="rkpi" style="--kc:#3d8fd4">
        <div class="rkpi-val">{{ $totalTrabajos }}</div>
        <div class="rkpi-label">Trabajos</div>
        <div class="rkpi-bar"></div>
    </div>
    <div class="rkpi" style="--kc:#3fb96a">
        <div class="rkpi-val">{{ $totalClientes }}</div>
        <div class="rkpi-label">Clientes</div>
        <div class="rkpi-bar"></div>
    </div>
    <div class="rkpi" style="--kc:#e0960a">
        <div class="rkpi-val">{{ $totalOrdenes }}</div>
        <div class="rkpi-label">Órdenes</div>
        <div class="rkpi-bar"></div>
    </div>
</div>

{{-- Cuerpo: materiales + ranking --}}
<div class="rep-body">

    {{-- Tabla por material --}}
    <div class="rpnl">
        <div class="rpnl-hd">
            <span class="rpnl-title">m² por material</span>
            <span class="rpnl-sub">{{ number_format($totalM2, 2) }} m² total</span>
        </div>

        @if($porMaterial->isEmpty())
            <div class="empty-state">
                <div class="es-icon">📊</div>
                <div class="es-msg">Sin datos de producción en este período</div>
                <div class="es-sub">Cargá trabajos con ancho y alto para ver métricas</div>
            </div>
        @else
            <table class="mat-table">
                <thead>
                    <tr>
                        <th>Material</th>
                        <th>Trabajos</th>
                        <th>Proporción</th>
                        <th>m²</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($porMaterial as $row)
                        @php
                            $pct = $totalM2 > 0 ? round($row->m2_total / $totalM2 * 100, 1) : 0;
                            $nombre = $row->material->nombre ?? 'Sin material';
                        @endphp
                        <tr>
                            <td>
                                <span style="font-weight:600;color:var(--tx);font-size:13px">
                                    {{ $nombre }}
                                </span>
                            </td>
                            <td>
                                <span style="font-family:var(--mono);font-size:12px;color:var(--txd)">
                                    {{ $row->cant_trabajos }}
                                </span>
                            </td>
                            <td style="min-width:140px">
                                <div class="mbar-wrap">
                                    <div class="mbar-track">
                                        <div class="mbar-fill" style="width:{{ $pct }}%"></div>
                                    </div>
                                    <span class="mbar-pct">{{ $pct }}%</span>
                                </div>
                            </td>
                            <td>
                                <span style="font-family:var(--mono);font-size:15px;font-weight:700;color:var(--ac)">
                                    {{ number_format($row->m2_total, 2) }}
                                </span>
                                <span style="font-size:10px;color:var(--txd)"> m²</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- Top 5 clientes --}}
    <div class="rpnl">
        <div class="rpnl-hd">
            <span class="rpnl-title">Top clientes</span>
            <span style="font-size:10px;color:var(--txm)">por m² · top 5</span>
        </div>

        @if($topClientes->isEmpty())
            <div class="empty-state">
                <div class="es-icon">🏆</div>
                <div class="es-msg">Sin datos</div>
                <div class="es-sub">Cargá trabajos con cliente y medidas</div>
            </div>
        @else
            @foreach($topClientes as $i => $row)
                @php
                    $posClass = match($i) { 0=>'top1', 1=>'top2', 2=>'top3', default=>'' };
                    $pct = $totalM2 > 0 ? round($row->m2_total / $totalM2 * 100) : 0;
                @endphp
                <div class="rank-item">
                    <div class="rank-pos {{ $posClass }}">{{ $i + 1 }}</div>
                    <div class="rank-info">
                        <div class="rank-name">
                            {{ $row->cliente->nombre ?? 'Sin cliente' }}
                        </div>
                        <div class="rank-meta">
                            {{ $row->cant_trabajos }} {{ $row->cant_trabajos == 1 ? 'trabajo' : 'trabajos' }}
                            · {{ $pct }}% del total
                        </div>
                    </div>
                    <div class="rank-m2">
                        {{ number_format($row->m2_total, 1) }}
                        <small>m²</small>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

</div>
@endsection

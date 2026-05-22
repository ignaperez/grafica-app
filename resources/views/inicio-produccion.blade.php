@extends('layouts.app')

@section('page-title', 'Inicio')

@section('content')
<style>
:root {
  --c0:#0c0c0c; --c1:#111111; --c2:#161616; --c3:#1e1e1e; --c4:#2a2a2a;
  --tx:#e8e4dc; --txd:#888; --ac:#e6502a;
  --green:#3fb96a; --amber:#e0960a; --blue:#3d8fd4; --red:#e05050; --purple:#8b6de8;
  --mono:'DM Mono',monospace;
}
.kpi-row { display:grid; grid-template-columns:repeat(5,1fr); gap:12px; margin-bottom:20px; }
@media(max-width:1100px){ .kpi-row{ grid-template-columns:repeat(3,1fr); } }
@media(max-width:640px) { .kpi-row{ grid-template-columns:repeat(2,1fr); } }
.kpi { position:relative; border-radius:12px; padding:20px 20px 18px; background:var(--c1); border:1px solid var(--c3); overflow:hidden; display:block; text-decoration:none; transition:border-color .2s,transform .2s; }
.kpi:hover { border-color:var(--c4); transform:translateY(-2px); }
.kpi::after { content:''; position:absolute; width:120px; height:120px; border-radius:50%; background:var(--kpi-c,#fff); opacity:.04; top:-30px; right:-30px; pointer-events:none; }
.kpi::before { content:''; position:absolute; bottom:0; left:0; right:0; height:2px; background:var(--kpi-c,transparent); opacity:.5; }
.kpi-icon { font-size:18px; margin-bottom:14px; line-height:1; }
.kpi-num  { font-family:var(--mono); font-size:52px; font-weight:700; line-height:.9; color:var(--kpi-c,var(--tx)); letter-spacing:-3px; margin-bottom:10px; }
.kpi-label{ font-size:10px; font-weight:700; letter-spacing:1.5px; text-transform:uppercase; color:var(--txd); }

.pnl { background:var(--c1); border:1px solid var(--c3); border-radius:12px; overflow:hidden; margin-bottom:14px; }
.pnl-hd { padding:14px 18px 12px; display:flex; align-items:center; justify-content:space-between; border-bottom:1px solid var(--c2); }
.pnl-title { font-size:11px; font-weight:700; letter-spacing:1.5px; text-transform:uppercase; color:var(--txd); }
.pnl-action { font-size:11px; color:var(--c4); text-decoration:none; transition:color .15s; }
.pnl-action:hover { color:var(--txd); }

.otable { width:100%; border-collapse:collapse; }
.otable thead th { padding:8px 16px; font-size:9px; letter-spacing:1.5px; text-transform:uppercase; color:var(--c4); font-weight:700; text-align:left; border-bottom:1px solid var(--c2); }
.otable tbody tr { border-bottom:1px solid var(--c2); transition:background .1s; }
.otable tbody tr:last-child { border-bottom:none; }
.otable tbody tr:hover { background:var(--c2); }
.otable td { padding:12px 16px; vertical-align:middle; }
.otable td.ind { padding-left:0; width:4px; padding-right:12px; }
.ind-bar { width:3px; height:36px; border-radius:2px; background:var(--row-c,var(--c3)); margin:auto; }
.tprog { height:3px; background:var(--c3); border-radius:2px; overflow:hidden; margin-top:4px; }
.tprog-fill { height:100%; border-radius:2px; background:var(--ac); transition:width .5s; }
.tprog-fill.done { background:var(--green); }
.spill { display:inline-flex; align-items:center; gap:5px; font-size:10px; font-weight:700; padding:3px 9px; border-radius:20px; letter-spacing:.3px; white-space:nowrap; }
.spill i { width:5px; height:5px; border-radius:50%; background:currentColor; flex-shrink:0; }
.sp-borrador      { color:#444; background:rgba(255,255,255,.03); border:1px solid #222; }
.sp-en_produccion { color:var(--amber); background:rgba(224,150,10,.08); border:1px solid rgba(224,150,10,.15); }
.sp-lista         { color:var(--green); background:rgba(63,185,106,.08); border:1px solid rgba(63,185,106,.15); }
</style>

{{-- Saludo --}}
<div style="margin-bottom:20px">
    <div style="font-size:18px;font-weight:600;color:var(--tx)">
        Buen día, {{ explode(' ', auth()->user()->name)[0] }} 👋
    </div>
    <div style="font-size:12px;color:var(--txd);margin-top:3px">
        {{ \Carbon\Carbon::now()->isoFormat('dddd D [de] MMMM') }}
    </div>
</div>

{{-- KPIs producción --}}
<div class="kpi-row">
    <a href="{{ route('trabajos-libres.index') }}" class="kpi" style="--kpi-c:var(--red)">
        <div class="kpi-icon">🔴</div>
        <div class="kpi-num">{{ $trabajosPendientes }}</div>
        <div class="kpi-label">Trabajos pendientes</div>
    </a>
    <div class="kpi" style="--kpi-c:var(--amber)">
        <div class="kpi-icon">⚙️</div>
        <div class="kpi-num">{{ $trabajosEnProd }}</div>
        <div class="kpi-label">En producción</div>
    </div>
    <div class="kpi" style="--kpi-c:var(--green)">
        <div class="kpi-icon">✅</div>
        <div class="kpi-num">{{ $trabajosTermHoy }}</div>
        <div class="kpi-label">Terminados hoy</div>
    </div>
    <div class="kpi" style="--kpi-c:var(--amber)">
        <div class="kpi-icon">📋</div>
        <div class="kpi-num">{{ $contadores['en_produccion'] }}</div>
        <div class="kpi-label">Órdenes en producción</div>
    </div>
    <div class="kpi" style="--kpi-c:var(--purple)">
        <div class="kpi-icon">📐</div>
        <div class="kpi-num" style="font-size:{{ strlen((string)intval($m2Mes)) > 4 ? '32px' : '42px' }};letter-spacing:-2px">
            {{ number_format($m2Mes, 1, ',', '.') }}
        </div>
        <div class="kpi-label">m² — {{ \Carbon\Carbon::now()->isoFormat('MMMM') }}</div>
    </div>
</div>

{{-- Órdenes activas --}}
<div class="pnl">
    <div class="pnl-hd">
        <span class="pnl-title">Órdenes activas</span>
        <a href="{{ route('ordenes-trabajo.index') }}" class="pnl-action">Ver todas →</a>
    </div>
    @if($ordenes->isEmpty())
        <div style="padding:40px 20px;text-align:center;color:var(--c4);font-size:13px">No hay órdenes activas.</div>
    @else
    <table class="otable">
        <thead>
            <tr>
                <th style="width:4px;padding:0"></th>
                <th>#</th><th>Cliente</th><th>Estado</th><th>Progreso</th><th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($ordenes as $orden)
            @php
                $total = $orden->trabajos->count();
                $term  = $orden->trabajos->where('estado','terminado')->count();
                $pct   = $total > 0 ? round($term/$total*100) : 0;
                $rc    = match($orden->estado) {
                    'en_produccion' => 'var(--amber)',
                    'lista'         => 'var(--green)',
                    default         => 'var(--c4)',
                };
            @endphp
            <tr>
                <td class="ind" style="padding:0 10px 0 0"><div class="ind-bar" style="--row-c:{{ $rc }}"></div></td>
                <td><span style="font-family:var(--mono);font-size:11px;color:var(--c4)">#{{ str_pad($orden->id,4,'0',STR_PAD_LEFT) }}</span></td>
                <td>
                    <div style="font-weight:600;color:var(--tx);font-size:13.5px">{{ $orden->cliente->nombre ?? 'Sin cliente' }}</div>
                    @if($orden->observaciones)
                        <div style="font-size:11px;color:var(--c4);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:200px">{{ $orden->observaciones }}</div>
                    @endif
                </td>
                <td>
                    <span class="spill sp-{{ $orden->estado }}"><i></i>
                        {{ ['borrador'=>'Borrador','en_produccion'=>'Producción','lista'=>'Lista'][$orden->estado] ?? $orden->estado }}
                    </span>
                </td>
                <td style="min-width:130px">
                    @if($total > 0)
                        <div style="display:flex;justify-content:space-between;font-size:10px;color:var(--c4);font-family:var(--mono);margin-bottom:3px">
                            <span>{{ $term }}/{{ $total }}</span>
                            <span style="color:{{ $pct==100 ? 'var(--green)' : 'var(--ac)' }}">{{ $pct }}%</span>
                        </div>
                        <div class="tprog"><div class="tprog-fill {{ $pct==100?'done':'' }}" style="width:{{ $pct }}%"></div></div>
                    @else
                        <span style="font-size:11px;color:var(--c3)">sin trabajos</span>
                    @endif
                </td>
                <td style="text-align:right">
                    <a href="{{ route('ordenes-trabajo.show',$orden->id) }}"
                       style="font-size:11px;color:var(--c4);text-decoration:none;padding:5px 12px;border:1px solid var(--c3);border-radius:6px;transition:all .15s;display:inline-block"
                       onmouseover="this.style.borderColor='var(--c4)';this.style.color='var(--tx)'"
                       onmouseout="this.style.borderColor='var(--c3)';this.style.color='var(--c4)'">Ver →</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>
@endsection

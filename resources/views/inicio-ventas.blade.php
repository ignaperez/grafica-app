@extends('layouts.app')

@section('page-title', 'Inicio')

@section('topbar-actions')
    <a href="{{ route('ordenes-trabajo.create') }}" class="gbtn gbtn-primary gbtn-sm">+ Nueva orden</a>
@endsection

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
.kpi { position:relative; border-radius:12px; padding:20px 20px 18px; background:var(--c1); border:1px solid var(--c3); overflow:hidden; text-decoration:none; display:block; transition:border-color .2s,transform .2s; }
.kpi:hover { border-color:var(--c4); transform:translateY(-2px); }
.kpi::after { content:''; position:absolute; width:120px; height:120px; border-radius:50%; background:var(--kpi-c,#fff); opacity:.04; top:-30px; right:-30px; pointer-events:none; }
.kpi::before { content:''; position:absolute; bottom:0; left:0; right:0; height:2px; background:var(--kpi-c,transparent); opacity:.5; }
.kpi-icon { font-size:18px; margin-bottom:14px; line-height:1; }
.kpi-num  { font-family:var(--mono); font-size:52px; font-weight:700; line-height:.9; color:var(--kpi-c,var(--tx)); letter-spacing:-3px; margin-bottom:10px; }
.kpi-label{ font-size:10px; font-weight:700; letter-spacing:1.5px; text-transform:uppercase; color:var(--txd); }

.dash-grid { display:grid; grid-template-columns:1fr 280px; gap:14px; margin-bottom:14px; align-items:start; }
@media(max-width:900px){ .dash-grid{ grid-template-columns:1fr; } }
.dash-grid.full { grid-template-columns:1fr; }

.pnl { background:var(--c1); border:1px solid var(--c3); border-radius:12px; overflow:hidden; }
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
.tprog-fill { height:100%; border-radius:2px; background:var(--ac); transition:width .5s ease; }
.tprog-fill.done { background:var(--green); }
.spill { display:inline-flex; align-items:center; gap:5px; font-size:10px; font-weight:700; padding:3px 9px; border-radius:20px; letter-spacing:.3px; white-space:nowrap; }
.spill i { width:5px; height:5px; border-radius:50%; background:currentColor; flex-shrink:0; }
.sp-borrador      { color:#444; background:rgba(255,255,255,.03); border:1px solid #222; }
.sp-en_produccion { color:var(--amber); background:rgba(224,150,10,.08); border:1px solid rgba(224,150,10,.15); }
.sp-lista         { color:var(--green); background:rgba(63,185,106,.08); border:1px solid rgba(63,185,106,.15); }
.sp-entregada     { color:var(--blue);  background:rgba(61,143,212,.08); border:1px solid rgba(61,143,212,.15); }

.dlv-item { display:flex; align-items:center; gap:14px; padding:12px 16px; border-bottom:1px solid var(--c2); transition:background .1s; }
.dlv-item:last-child { border-bottom:none; }
.dlv-item:hover { background:var(--c2); }
.dlv-cal { flex-shrink:0; width:44px; text-align:center; border-radius:8px; padding:6px 4px; background:var(--c2); border:1px solid var(--c3); }
.dlv-cal .dc-day { font-family:var(--mono); font-size:22px; font-weight:700; line-height:1; color:var(--dlv-c,var(--tx)); }
.dlv-cal .dc-mon { font-size:9px; letter-spacing:1px; text-transform:uppercase; color:var(--txd); margin-top:1px; }
.dlv-info { min-width:0; flex:1; }
.dlv-name { font-size:12.5px; font-weight:600; color:var(--tx); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.dlv-sub  { font-size:11px; color:var(--txd); margin-top:2px; }
.dlv-badge{ flex-shrink:0; font-family:var(--mono); font-size:10px; font-weight:700; padding:3px 7px; border-radius:20px; background:var(--c2); color:var(--dlv-c,var(--txd)); border:1px solid var(--c3); }
</style>

{{-- KPIs --}}
<div class="kpi-row">
    <div class="kpi" style="--kpi-c:#555">
        <div class="kpi-icon">📋</div>
        <div class="kpi-num">{{ $contadores['borrador'] }}</div>
        <div class="kpi-label">Borrador</div>
    </div>
    <div class="kpi" style="--kpi-c:var(--amber)">
        <div class="kpi-icon">⚙️</div>
        <div class="kpi-num">{{ $contadores['en_produccion'] }}</div>
        <div class="kpi-label">En producción</div>
    </div>
    <div class="kpi" style="--kpi-c:var(--green)">
        <div class="kpi-icon">✅</div>
        <div class="kpi-num">{{ $contadores['lista'] }}</div>
        <div class="kpi-label">Lista p/ entregar</div>
    </div>
    <div class="kpi" style="--kpi-c:var(--blue)">
        <div class="kpi-icon">📦</div>
        <div class="kpi-num">{{ $contadores['entregada_mes'] }}</div>
        <div class="kpi-label">Entregadas este mes</div>
    </div>
    <a href="{{ route('trabajos-libres.index') }}" class="kpi" style="--kpi-c:var(--ac)">
        <div class="kpi-icon">⚠️</div>
        <div class="kpi-num">{{ $trabajosLibresPend }}</div>
        <div class="kpi-label">Sin asignar</div>
    </a>
</div>

{{-- Órdenes + entregas --}}
<div class="{{ $proximasEntregas->isNotEmpty() ? 'dash-grid' : 'dash-grid full' }}">
    <div class="pnl">
        <div class="pnl-hd">
            <span class="pnl-title">Órdenes activas</span>
            <a href="{{ route('ordenes-trabajo.index') }}" class="pnl-action">Ver todas →</a>
        </div>
        @if($ordenes->isEmpty())
            <div style="padding:52px 20px;text-align:center;color:var(--c4);font-size:13px">
                No hay órdenes activas. <a href="{{ route('ordenes-trabajo.create') }}" style="color:var(--ac)">Crear primera</a>
            </div>
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

    @if($proximasEntregas->isNotEmpty())
    <div class="pnl">
        <div class="pnl-hd">
            <span class="pnl-title">Próximas entregas</span>
            <span style="font-size:9px;letter-spacing:1px;color:var(--c4)">7 DÍAS</span>
        </div>
        @foreach($proximasEntregas as $t)
            @php
                $d  = \Carbon\Carbon::today()->diffInDays($t->fecha_entrega, false);
                $dc = $d<=1 ? 'var(--red)' : ($d<=3 ? 'var(--amber)' : 'var(--green)');
                $dl = $d==0 ? 'hoy' : ($d==1 ? 'mañana' : $d.'d');
            @endphp
            <div class="dlv-item" style="--dlv-c:{{ $dc }}">
                <div class="dlv-cal">
                    <div class="dc-day">{{ $t->fecha_entrega->format('d') }}</div>
                    <div class="dc-mon">{{ $t->fecha_entrega->isoFormat('MMM') }}</div>
                </div>
                <div class="dlv-info">
                    <div class="dlv-name">{{ $t->descripcion ?? ($t->tipoTrabajo->nombre ?? 'Trabajo #'.$t->id) }}</div>
                    <div class="dlv-sub">{{ $t->cliente->nombre ?? $t->orden?->cliente?->nombre ?? '—' }}</div>
                </div>
                <div class="dlv-badge">{{ $dl }}</div>
            </div>
        @endforeach
    </div>
    @endif
</div>
@endsection

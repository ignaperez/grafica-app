@extends('layouts.app')

@section('page-title', 'Vehículos')

@section('topbar-actions')
    <a href="{{ route('vehiculos-ploteo.create') }}" class="gbtn gbtn-primary gbtn-sm">+ Nuevo vehículo</a>
@endsection

@section('content')

<form method="GET" style="display:flex;gap:8px;margin-bottom:14px;max-width:460px">
    <input type="text" name="q" value="{{ $q ?? '' }}" class="ginput"
           placeholder="Buscar por patente, marca o modelo…"
           style="text-transform:uppercase;letter-spacing:1px" autofocus>
    <button class="gbtn gbtn-primary gbtn-sm">Buscar</button>
    @if(!empty($q))
        <a href="{{ route('vehiculos-ploteo.index') }}" class="gbtn gbtn-ghost gbtn-sm">Limpiar</a>
    @endif
</form>

<div class="gcard">
    <div class="gcard-hd">
        <span class="gcard-title">Vehículos ploteados</span>
        <span class="txd" style="font-size:11px">{{ $vehiculos->total() }} {{ !empty($q) ? 'resultado(s)' : 'registros' }}</span>
    </div>

    @if($vehiculos->isEmpty())
        <div style="padding:52px 20px;text-align:center;color:#333;font-size:13px">
            @if(!empty($q))
                No se encontraron vehículos para <strong style="color:var(--tx)">“{{ $q }}”</strong>. &nbsp;
                <a href="{{ route('vehiculos-ploteo.index') }}" style="color:var(--ac)">Ver todos</a>
            @else
                Todavía no hay vehículos registrados. &nbsp;
                <a href="{{ route('vehiculos-ploteo.create') }}" style="color:var(--ac)">Agregar primero</a>
            @endif
        </div>
    @else
    <table class="gtable">
        <thead>
            <tr>
                <th>#</th>
                <th>Patente</th>
                <th>Vehículo</th>
                <th>Cliente</th>
                <th>Fecha ploteo</th>
                <th>Orden</th>
                <th>Fotos</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($vehiculos as $v)
            <tr>
                <td class="mono txd" style="font-size:11px">{{ str_pad($v->id,4,'0',STR_PAD_LEFT) }}</td>
                <td>
                    <span style="font-family:var(--mono);font-size:13px;font-weight:600;
                                 color:var(--tx);letter-spacing:1px;text-transform:uppercase">
                        {{ $v->patente }}
                    </span>
                </td>
                <td>
                    <div style="font-weight:500;color:var(--tx)">{{ $v->marca }} {{ $v->modelo }}</div>
                </td>
                <td style="color:var(--tx)">{{ $v->cliente->nombre ?? '—' }}</td>
                <td class="txd">
                    {{ $v->fecha_ploteo ? $v->fecha_ploteo->format('d/m/Y') : '—' }}
                </td>
                <td>
                    @if($v->orden)
                        <a href="{{ route('ordenes-trabajo.show', $v->orden_trabajo_id) }}"
                           style="font-size:12px;color:var(--ac);text-decoration:none">
                            #{{ str_pad($v->orden_trabajo_id,4,'0',STR_PAD_LEFT) }}
                            {{ $v->orden->cliente->nombre ?? '' }}
                        </a>
                    @else
                        <span class="txd">—</span>
                    @endif
                </td>
                <td>
                    @php
                        $antes   = collect(['foto_antes_frente','foto_antes_atras','foto_antes_izq','foto_antes_der'])->filter(fn($f) => $v->$f)->count();
                        $despues = collect(['foto_despues_frente','foto_despues_atras','foto_despues_izq','foto_despues_der'])->filter(fn($f) => $v->$f)->count();
                    @endphp
                    <span style="font-size:11px;color:var(--txd)">
                        {{ $antes }}/4 antes &nbsp;·&nbsp; {{ $despues }}/4 después
                    </span>
                </td>
                <td style="text-align:right">
                    <a href="{{ route('vehiculos-ploteo.show', $v->id) }}" class="gbtn gbtn-ghost gbtn-xs">Ver →</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div style="padding:16px 18px">
        {{ $vehiculos->links() }}
    </div>
    @endif
</div>
@endsection

@extends('layouts.app')

@php $puedePresu = auth()->user()->puedeModulo('presupuestos'); @endphp

@section('page-title', 'Vehículos')

@section('topbar-actions')
    <a href="{{ route('vehiculos-ploteo.export', ['q' => request('q')]) }}" class="gbtn gbtn-ghost gbtn-sm">⬇ Excel</a>
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
                @if($puedePresu)<th style="width:30px;text-align:center"><input type="checkbox" id="veh-all" title="Seleccionar todos" style="cursor:pointer;accent-color:var(--ac)"></th>@endif
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
                @if($puedePresu)
                <td style="text-align:center">
                    @unless($v->presupuestado())
                        <input type="checkbox" class="veh-check" value="{{ $v->id }}"
                               data-cliente-id="{{ $v->cliente_id }}" data-cliente-nombre="{{ $v->cliente->nombre ?? 'sin cliente' }}"
                               style="cursor:pointer;accent-color:var(--ac)">
                    @endunless
                </td>
                @endif
                <td class="mono txd" style="font-size:11px">{{ str_pad($v->id,4,'0',STR_PAD_LEFT) }}</td>
                <td>
                    <span style="font-family:var(--mono);font-size:13px;font-weight:600;
                                 color:var(--tx);letter-spacing:1px;text-transform:uppercase">
                        {{ $v->patente }}
                    </span>
                    @if($v->presupuestado())
                        <span title="Presupuestado{{ $v->presupuesto ? ' · '.$v->presupuesto->numeroFormateado() : ' (manual)' }}"
                              style="margin-left:6px;display:inline-flex;align-items:center;gap:3px;
                                     background:#1c3a29;color:#3fb96a;font-size:9.5px;font-weight:700;
                                     padding:2px 6px;border-radius:10px;vertical-align:middle;letter-spacing:.3px">✓ Presup.</span>
                    @endif
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
                <td style="text-align:right;white-space:nowrap">
                    @if($puedePresu)
                        @if($v->presupuestado())
                            <form method="POST" action="{{ route('vehiculos-ploteo.desmarcar-presupuestado', $v->id) }}" style="display:inline"
                                  onsubmit="return confirm('¿Quitar la marca de presupuestado de este vehículo?')">
                                @csrf @method('DELETE')
                                <button class="gbtn gbtn-ghost gbtn-xs" title="Quitar presupuestado">✕ Presup.</button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('vehiculos-ploteo.marcar-presupuestado', $v->id) }}" style="display:inline">
                                @csrf
                                <button class="gbtn gbtn-ghost gbtn-xs" title="Marcar como presupuestado (manual)">✓ Marcar</button>
                            </form>
                        @endif
                    @endif
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

@if($puedePresu)
{{-- Barra flotante: presupuestar selección --}}
<form method="POST" action="{{ route('presupuestos.desde-vehiculos') }}" id="veh-presu-form"
      style="display:none;position:fixed;left:50%;bottom:22px;transform:translateX(-50%);z-index:1040;
             background:var(--bg-s);border:1px solid var(--bm);border-radius:999px;
             box-shadow:0 10px 30px rgba(0,0,0,.5);padding:9px 10px 9px 18px;align-items:center;gap:14px">
    @csrf
    <div id="veh-inputs"></div>
    <span style="font-size:13px;color:var(--tx)"><strong id="veh-count">0</strong> seleccionados</span>
    <span id="veh-cliente" style="font-size:12px;color:var(--ac);font-family:var(--mono)"></span>
    <button type="submit" id="veh-presu-btn" class="gbtn gbtn-primary gbtn-sm">⚡ Presupuestar</button>
    <button type="button" class="gbtn gbtn-ghost gbtn-sm" onclick="vehLimpiar()">Limpiar</button>
</form>
@endif
@endsection

@if($puedePresu ?? false)
@section('scripts')
<script>
(function () {
    const $bar = $('#veh-presu-form');
    const KEY  = 'veh_sel';   // selección persistida entre páginas (sessionStorage)

    const load = () => { try { return JSON.parse(sessionStorage.getItem(KEY) || '{}'); } catch (e) { return {}; } };
    const save = (m) => sessionStorage.setItem(KEY, JSON.stringify(m));
    const datos = (cb) => ({ cid: cb.dataset.clienteId || '', cname: cb.dataset.clienteNombre || '' });

    // Refresca la barra a partir de TODA la selección (todas las páginas)
    function refrescar() {
        const m   = load();
        const ids = Object.keys(m);
        $('#veh-count').text(ids.length);
        $bar.css('display', ids.length ? 'flex' : 'none');

        const $inp = $('#veh-inputs').empty();
        ids.forEach(id => $inp.append('<input type="hidden" name="vehiculo_ids[]" value="' + id + '">'));

        if (!ids.length) return;
        const cids = [...new Set(ids.map(id => m[id].cid || ''))];
        const $cli = $('#veh-cliente'), $btn = $('#veh-presu-btn');
        if (cids.length > 1) {
            $cli.text('⚠ distintos clientes').css('color', '#e05555'); $btn.prop('disabled', true).css('opacity', .5);
        } else if (cids[0] === '') {
            $cli.text('⚠ sin cliente asignado').css('color', '#e05555'); $btn.prop('disabled', true).css('opacity', .5);
        } else {
            $cli.text(m[ids[0]].cname || '').css('color', 'var(--ac)'); $btn.prop('disabled', false).css('opacity', 1);
        }
    }

    // Restaura los checkboxes de ESTA página desde la selección guardada
    function restaurar() {
        const m = load();
        $('.veh-check').each(function () { this.checked = !!m[this.value]; });
        const $ch = $('.veh-check');
        $('#veh-all').prop('checked', $ch.length > 0 && $ch.filter(':checked').length === $ch.length);
        refrescar();
    }

    $(document).on('change', '.veh-check', function () {
        const m = load();
        if (this.checked) m[this.value] = datos(this); else delete m[this.value];
        save(m); refrescar();
    });

    $('#veh-all').on('change', function () {
        const m = load(), on = this.checked;
        $('.veh-check').each(function () {
            this.checked = on;
            if (on) m[this.value] = datos(this); else delete m[this.value];
        });
        save(m); refrescar();
    });

    window.vehLimpiar = function () {
        sessionStorage.removeItem(KEY);
        $('.veh-check, #veh-all').prop('checked', false);
        refrescar();
    };

    // Al presupuestar, limpiar la selección guardada
    $bar.on('submit', function () { sessionStorage.removeItem(KEY); });

    restaurar();
})();
</script>
@endsection
@endif

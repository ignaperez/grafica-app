@extends('layouts.app')

@section('page-title', 'Seguimiento')

@section('topbar-actions')
    <form method="GET" style="display:inline-flex;align-items:center;gap:6px;margin-right:8px">
        <span class="txd" style="font-size:12px">Año</span>
        <select name="anio" class="gselect gselect-sm" style="width:auto" onchange="this.form.submit()">
            @forelse($anios as $y)
                <option value="{{ $y }}" {{ $anio == $y ? 'selected' : '' }}>{{ $y }}</option>
            @empty
                <option value="{{ $anio }}">{{ $anio }}</option>
            @endforelse
        </select>
    </form>
    <a href="{{ route('seguimientos.print', ['anio' => $anio]) }}" target="_blank" class="gbtn gbtn-ghost gbtn-sm">🖨 Imprimir</a>
@endsection

@section('content')

<style>
    /* ── Totales ── */
    .seg-tot { display:grid; grid-template-columns:repeat(4,1fr); gap:10px; margin-bottom:14px; }
    @media (max-width:800px){ .seg-tot{ grid-template-columns:repeat(2,1fr); } }
    .seg-tc { background:var(--bg-s); border:1px solid var(--b); border-radius:10px; padding:12px 14px; }
    .seg-tc .l { font-size:9.5px; letter-spacing:.08em; text-transform:uppercase; color:var(--txd); font-weight:700; }
    .seg-tc .v { font-family:var(--mono); font-size:19px; font-weight:700; margin-top:5px; letter-spacing:-.5px; }

    /* ── Tabla ── */
    .seg-wrap { overflow-x:auto; border:1px solid var(--b); border-radius:10px; background:var(--bg-s); }
    table.seg { border-collapse:collapse; width:100%; font-size:10.5px; white-space:nowrap; }
    table.seg th {
        background:#141414; color:var(--txd);
        font-size:8.5px; letter-spacing:.04em; text-transform:uppercase; font-weight:700;
        padding:7px 6px; text-align:left; border-bottom:1px solid var(--bm); position:sticky; top:0; z-index:2;
    }
    [data-theme="light"] table.seg th { background:#efece6; }
    table.seg td { padding:3px 5px; border-bottom:1px solid var(--b); vertical-align:middle; }
    table.seg td.auto { color:var(--tx); }
    table.seg td.calc { text-align:right; font-family:var(--mono); color:var(--txd); font-size:9.5px; }

    .seg-input, .seg-select {
        background:transparent; border:1px solid transparent; border-radius:5px;
        color:var(--tx); font-size:10.5px; padding:4px 5px; width:100%; font-family:inherit;
        transition:border-color .12s, background .12s;
    }
    .seg-input:hover, .seg-select:hover { border-color:var(--bm); }
    .seg-input:focus, .seg-select:focus { outline:none; border-color:var(--ac); background:var(--bg); }
    .seg-input.num { text-align:right; font-family:var(--mono); }
    .seg-input.wide { min-width:170px; }
    .seg-input.mid  { min-width:110px; }
    .seg-input.oc   { width:52px; text-align:center; font-family:var(--mono); }

    /* select de estado: chip compacto coloreado */
    .seg-estado {
        border:none; border-radius:14px; padding:3px 8px; font-size:9px; font-weight:700;
        letter-spacing:.02em; cursor:pointer; -webkit-appearance:none; appearance:none; text-align:center; min-width:118px;
    }

    /* Fila cobrada → verde pastel legible */
    .seg-row.cobrado td { background:#cdeedd; }
    [data-theme="light"] .seg-row.cobrado td { background:#dcf3e6; }
    .seg-row.cobrado td.auto, .seg-row.cobrado td.calc, .seg-row.cobrado .seg-input { color:#123a26; }

    .seg-row.saved td { animation:segflash 1s ease; }
    @keyframes segflash { 0%{ background:rgba(63,185,106,.18); } 100%{ } }

    .seg-hint { font-size:11.5px; color:var(--txd); margin-bottom:12px; }
</style>

{{-- ── Totales del año ── --}}
<div class="seg-tot">
    <div class="seg-tc">
        <div class="l">Presupuestado</div>
        <div class="v" style="color:var(--tx)">${{ number_format($totales['presupuestado'], 2, ',', '.') }}</div>
    </div>
    <div class="seg-tc">
        <div class="l">Facturado</div>
        <div class="v" style="color:#3d8fd4">${{ number_format($totales['facturado'], 2, ',', '.') }}</div>
    </div>
    <div class="seg-tc">
        <div class="l">Cobrado</div>
        <div class="v" style="color:#3fb96a">${{ number_format($totales['cobrado'], 2, ',', '.') }}</div>
    </div>
    <div class="seg-tc">
        <div class="l">Pendiente de cobro</div>
        <div class="v" style="color:{{ $totales['pendiente'] > 0 ? '#e0960a' : 'var(--txd)' }}">${{ number_format($totales['pendiente'], 2, ',', '.') }}</div>
    </div>
</div>

<div class="seg-hint">
    Fila por presupuesto (se crean solas). Datos de presupuesto y factura automáticos; el resto se edita acá y se guarda solo.
    Los cálculos 21% / 5% / TRANSF. aparecen al cargar la fecha de pago.
</div>

<div class="seg-wrap">
<table class="seg">
    <thead>
        <tr>
            <th>Fecha</th>
            <th>Presup.</th>
            <th style="text-align:right">Monto</th>
            <th>Área</th>
            <th>Detalle</th>
            <th>OC</th>
            <th style="text-align:right">M. O.P.</th>
            <th>F. Fact.</th>
            <th>Factura</th>
            <th>Estado</th>
            <th>Obs.</th>
            <th>Pasó a</th>
            <th style="text-align:right;font-size:8px">21%</th>
            <th style="text-align:right;font-size:8px">5%</th>
            <th>F. transf.</th>
            <th style="text-align:right">TRANSF.</th>
        </tr>
    </thead>
    <tbody>
        @forelse($seguimientos as $s)
        <tr class="seg-row {{ $s->estado === 'cobrado' ? 'cobrado' : '' }}" data-id="{{ $s->id }}" data-url="{{ route('seguimientos.update', $s->id) }}">
            <td class="auto">{{ $s->presupuesto->fecha?->format('d/m/y') ?? '—' }}</td>
            <td class="auto mono" style="color:var(--ac)">{{ $s->presupuesto->numeroFormateado() }}</td>
            <td class="auto mono" style="text-align:right">${{ number_format($s->montoBase(), 2, ',', '.') }}</td>

            <td><input type="text" class="seg-input mid" data-f="area_oficina" value="{{ $s->area_oficina }}"></td>
            <td><input type="text" class="seg-input wide" data-f="detalle" value="{{ $s->detalle }}"></td>
            <td><input type="text" inputmode="numeric" maxlength="4" class="seg-input oc" data-f="orden_compra" value="{{ $s->orden_compra }}"></td>
            <td><input type="number" step="0.01" class="seg-input num" data-f="monto_op" value="{{ $s->monto_op !== null ? rtrim(rtrim(number_format($s->monto_op,2,'.',''),'0'),'.') : '' }}" style="min-width:100px"></td>

            <td class="auto">{{ $s->factura?->fecha?->format('d/m/y') ?? '—' }}</td>
            <td class="auto mono">{{ $s->factura?->numeroFormateado() ?? '—' }}</td>

            <td>
                <select class="seg-select seg-estado" data-f="estado" style="background:{{ $s->estadoBg() }};color:{{ $s->estadoText() }}">
                    @foreach(\App\Models\Seguimiento::ESTADOS as $val => $info)
                        <option value="{{ $val }}" {{ $s->estado === $val ? 'selected' : '' }}>{{ $info[0] }}</option>
                    @endforeach
                </select>
            </td>

            <td><input type="text" class="seg-input mid" data-f="observaciones" value="{{ $s->observaciones }}"></td>
            <td><input type="text" class="seg-input" data-f="pasado_a" value="{{ $s->pasado_a }}" style="min-width:90px"></td>

            <td class="calc cell-21">{{ $s->mostrarCalculos() ? '$'.number_format($s->iva21(), 2, ',', '.') : '—' }}</td>
            <td class="calc cell-5">{{ $s->mostrarCalculos() ? '$'.number_format($s->cinco(), 2, ',', '.') : '—' }}</td>

            <td><input type="date" class="seg-input" data-f="fecha_pago" value="{{ $s->fecha_pago?->format('Y-m-d') }}" style="min-width:125px"></td>

            <td class="calc cell-total" style="color:var(--tx);font-weight:600">
                {{ $s->mostrarCalculos() ? '$'.number_format($s->totalHernan(), 2, ',', '.') : '—' }}
            </td>
        </tr>
        @empty
        <tr><td colspan="16" style="text-align:center;color:var(--txd);padding:32px">
            No hay presupuestos en {{ $anio }}.
        </td></tr>
        @endforelse
    </tbody>
</table>
</div>

{{-- Paginación --}}
@if($seguimientos->hasPages())
<div style="display:flex;justify-content:space-between;align-items:center;margin-top:12px">
    <span class="txd" style="font-size:12px">Página {{ $seguimientos->currentPage() }} de {{ $seguimientos->lastPage() }} · {{ $seguimientos->total() }} filas</span>
    <div style="display:flex;gap:8px">
        @if($seguimientos->onFirstPage())
            <span class="gbtn gbtn-ghost gbtn-sm" style="opacity:.4">← Anterior</span>
        @else
            <a href="{{ $seguimientos->previousPageUrl() }}" class="gbtn gbtn-ghost gbtn-sm">← Anterior</a>
        @endif
        @if($seguimientos->hasMorePages())
            <a href="{{ $seguimientos->nextPageUrl() }}" class="gbtn gbtn-ghost gbtn-sm">Siguiente →</a>
        @else
            <span class="gbtn gbtn-ghost gbtn-sm" style="opacity:.4">Siguiente →</span>
        @endif
    </div>
</div>
@endif

@endsection

@section('scripts')
<script>
(function () {
    const CSRF = '{{ csrf_token() }}';

    function guardarFila($row) {
        const url  = $row.data('url');
        const data = {};
        $row.find('.seg-input, .seg-select').each(function () {
            data[$(this).data('f')] = $(this).val();
        });

        fetch(url, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify(data),
        })
        .then(r => r.ok ? r.json() : Promise.reject(r))
        .then(res => {
            const $sel = $row.find('.seg-estado');
            $sel.css({ background: res.estadoBg, color: res.estadoText }).data('prev', res.estado);
            $row.toggleClass('cobrado', res.estado === 'cobrado');
            $row.find('.cell-21').text(res.mostrarCalc ? '$' + res.iva21 : '—');
            $row.find('.cell-5').text(res.mostrarCalc ? '$' + res.cinco : '—');
            $row.find('.cell-total').text(res.mostrarCalc ? '$' + res.totalHernan : '—');
            $row.addClass('saved');
            setTimeout(() => $row.removeClass('saved'), 1000);
        })
        .catch(() => alert('No se pudo guardar el cambio. Revisá los datos e intentá de nuevo.'));
    }

    // Recordar valor previo del estado (para poder revertir si cancelan)
    $(document).on('focus', '.seg-estado', function () {
        if ($(this).data('prev') === undefined) $(this).data('prev', $(this).val());
    });

    $(document).on('change', '.seg-input, .seg-select', function () {
        const $el  = $(this);
        const $row = $el.closest('.seg-row');

        // Confirmación SOLO al pasar a Cobrado
        if ($el.hasClass('seg-estado') && $el.val() === 'cobrado') {
            if (!confirm('¿Marcar esta factura como COBRADA?')) {
                $el.val($el.data('prev'));   // revertir
                return;
            }
        }
        guardarFila($row);
    });

    // OC: solo dígitos, máx 4
    $(document).on('input', '.seg-input.oc', function () {
        this.value = this.value.replace(/\D/g, '').slice(0, 4);
    });
})();
</script>
@endsection

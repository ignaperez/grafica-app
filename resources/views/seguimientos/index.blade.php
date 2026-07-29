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
    .seg-tot { display:grid; grid-template-columns:repeat(4,1fr); gap:10px; margin-bottom:14px; }
    @media (max-width:800px){ .seg-tot{ grid-template-columns:repeat(2,1fr); } }
    .seg-tc { background:var(--bg-s); border:1px solid var(--b); border-radius:10px; padding:12px 14px; }
    .seg-tc .l { font-size:9.5px; letter-spacing:.08em; text-transform:uppercase; color:var(--txd); font-weight:700; }
    .seg-tc .v { font-family:var(--mono); font-size:19px; font-weight:700; margin-top:5px; letter-spacing:-.5px; }

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

    .seg-estado {
        border:none; border-radius:14px; padding:3px 8px; font-size:9px; font-weight:700;
        letter-spacing:.02em; cursor:pointer; -webkit-appearance:none; appearance:none; text-align:center; min-width:118px;
    }
    .seg-row.manual td:first-child { border-left:3px solid var(--ac); }
    .seg-row.cobrado td { background:#cdeedd; }
    [data-theme="light"] .seg-row.cobrado td { background:#dcf3e6; }
    .seg-row.cobrado td.auto, .seg-row.cobrado td.calc, .seg-row.cobrado .seg-input { color:#123a26; }
    .seg-row.saved td { animation:segflash 1s ease; }
    @keyframes segflash { 0%{ background:rgba(63,185,106,.18); } 100%{ } }

    .seg-hint { font-size:11.5px; color:var(--txd); margin-bottom:12px; }
</style>

{{-- ── Totales del año ── --}}
<div class="seg-tot">
    <div class="seg-tc"><div class="l">Presupuestado</div><div class="v" style="color:var(--tx)">${{ number_format($totales['presupuestado'], 2, ',', '.') }}</div></div>
    <div class="seg-tc"><div class="l">Facturado</div><div class="v" style="color:#3d8fd4">${{ number_format($totales['facturado'], 2, ',', '.') }}</div></div>
    <div class="seg-tc"><div class="l">Cobrado</div><div class="v" style="color:#3fb96a">${{ number_format($totales['cobrado'], 2, ',', '.') }}</div></div>
    <div class="seg-tc"><div class="l">Pendiente de cobro</div><div class="v" style="color:{{ $totales['pendiente'] > 0 ? '#e0960a' : 'var(--txd)' }}">${{ number_format($totales['pendiente'], 2, ',', '.') }}</div></div>
</div>

{{-- ── Cargar proceso a mano (presupuesto del sistema anterior) ── --}}
<details class="gcard" style="margin-bottom:14px">
    <summary style="cursor:pointer;padding:12px 16px;font-weight:600;font-size:13px;color:var(--ac)">
        + Cargar proceso a mano <span class="txd" style="font-weight:400">(presupuesto del sistema anterior)</span>
    </summary>
    <form method="POST" action="{{ route('seguimientos.store') }}" class="gcard-bd"
          style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:10px;align-items:end;border-top:1px solid var(--b)">
        @csrf
        <div class="gfg" style="margin:0"><label class="glabel">Fecha *</label><input type="date" name="fecha_manual" class="ginput" value="{{ now()->format('Y-m-d') }}" required></div>
        <div class="gfg" style="margin:0"><label class="glabel">N° presup. (viejo)</label><input type="text" name="numero_manual" class="ginput" placeholder="ej: 1234"></div>
        <div class="gfg" style="margin:0"><label class="glabel">Monto *</label><input type="number" step="0.01" min="0" name="monto_manual" class="ginput" required></div>
        <div class="gfg" style="margin:0">
            <label class="glabel">Factura (opcional)</label>
            <select name="factura_id" class="gselect">
                <option value="">— sin factura —</option>
                @foreach($facturas as $f)
                    <option value="{{ $f->id }}">{{ $f->numeroFormateado() }} · {{ \Illuminate\Support\Str::limit($f->cliente->nombre ?? '', 20) }}</option>
                @endforeach
            </select>
        </div>
        <div class="gfg" style="margin:0"><label class="glabel">Área / oficina</label><input type="text" name="area_oficina" class="ginput"></div>
        <div class="gfg" style="margin:0"><label class="glabel">Detalle</label><input type="text" name="detalle" class="ginput"></div>
        <div class="gfg" style="margin:0">
            <label class="glabel">Estado</label>
            <select name="estado" class="gselect">
                @foreach(\App\Models\Seguimiento::ESTADOS as $val => $info)
                    <option value="{{ $val }}">{{ $info[0] }}</option>
                @endforeach
            </select>
        </div>
        <div><button class="gbtn gbtn-primary" style="width:100%">Cargar</button></div>
    </form>
</details>

<div class="seg-hint">
    Fila por presupuesto (se crean solas). Las de <strong style="color:var(--ac)">borde naranja</strong> son
    cargadas a mano: se editan fecha, N°, monto y factura. El resto se edita en línea y se guarda solo.
</div>

<div class="seg-wrap">
<table class="seg">
    <thead>
        <tr>
            <th style="width:26px;text-align:center"><input type="checkbox" id="seg-all" title="Seleccionar todos" style="cursor:pointer;accent-color:var(--ac)"></th>
            <th>Fecha</th><th>Presup.</th><th style="text-align:right">Monto</th>
            <th>Área</th><th>Detalle</th><th>OC</th><th style="text-align:right">M. O.P.</th>
            <th>F. Fact.</th><th>Factura</th><th>Estado</th><th>Obs.</th><th>Pasó a</th>
            <th style="text-align:right;font-size:8px">21%</th><th style="text-align:right;font-size:8px">5%</th>
            <th>F. transf.</th><th style="text-align:right">TRANSF.</th><th></th>
        </tr>
    </thead>
    <tbody>
        @forelse($seguimientos as $s)
        <tr class="seg-row {{ $s->esManual() ? 'manual' : '' }} {{ $s->estado === 'cobrado' ? 'cobrado' : '' }}"
            data-id="{{ $s->id }}" data-url="{{ route('seguimientos.update', $s->id) }}"
            data-label="{{ $s->numeroRef() }}"
            data-ffact="{{ $s->factura?->fecha?->format('d/m/y') ?? '—' }}"
            data-facturado="{{ $s->montoBase() }}"
            data-neto="{{ round($s->montoBase() / 1.21, 2) }}"
            data-iva="{{ $s->iva21() }}"
            data-cinco="{{ $s->cinco() }}"
            data-total="{{ $s->totalHernan() }}">

            {{-- Checkbox de selección --}}
            <td style="text-align:center"><input type="checkbox" class="seg-check" style="cursor:pointer;accent-color:var(--ac)"></td>

            {{-- Fecha --}}
            @if($s->esManual())
                <td><input type="date" class="seg-input" data-f="fecha_manual" value="{{ $s->fecha_manual?->format('Y-m-d') }}" style="min-width:120px"></td>
            @else
                <td class="auto">{{ $s->fechaRef()?->format('d/m/y') ?? '—' }}</td>
            @endif

            {{-- N° presupuesto --}}
            @if($s->esManual())
                <td><input type="text" class="seg-input" data-f="numero_manual" value="{{ $s->numero_manual }}" placeholder="N° viejo" style="min-width:80px;color:var(--ac)"></td>
            @else
                <td class="auto mono" style="color:var(--ac)">{{ $s->numeroRef() }}</td>
            @endif

            {{-- Monto --}}
            @if($s->esManual())
                <td><input type="text" inputmode="decimal" class="seg-input num seg-money" data-f="monto_manual" value="{{ $s->monto_manual !== null ? number_format($s->monto_manual, 2, ',', '.') : '' }}" style="min-width:110px"></td>
            @else
                <td class="auto mono" style="text-align:right">${{ number_format($s->montoBase(), 2, ',', '.') }}</td>
            @endif

            <td><input type="text" class="seg-input mid" data-f="area_oficina" value="{{ $s->area_oficina }}"></td>
            <td><input type="text" class="seg-input wide" data-f="detalle" value="{{ $s->detalle }}"></td>
            <td><input type="text" inputmode="numeric" maxlength="4" class="seg-input oc" data-f="orden_compra" value="{{ $s->orden_compra }}"></td>
            <td><input type="text" inputmode="decimal" class="seg-input num seg-money" data-f="monto_op" value="{{ $s->monto_op !== null ? number_format($s->monto_op, 2, ',', '.') : '' }}" style="min-width:110px"></td>

            {{-- Fecha factura --}}
            <td class="auto cell-ffact">{{ $s->factura?->fecha?->format('d/m/y') ?? '—' }}</td>

            {{-- Factura --}}
            @if($s->esManual())
                <td>
                    <select class="seg-select" data-f="factura_id" style="min-width:160px">
                        <option value="">— sin factura —</option>
                        @if($s->factura)
                            <option value="{{ $s->factura->id }}" selected>{{ $s->factura->numeroFormateado() }} · {{ \Illuminate\Support\Str::limit($s->factura->cliente->nombre ?? '', 18) }}</option>
                        @endif
                        @foreach($facturas as $f)
                            <option value="{{ $f->id }}">{{ $f->numeroFormateado() }} · {{ \Illuminate\Support\Str::limit($f->cliente->nombre ?? '', 18) }}</option>
                        @endforeach
                    </select>
                </td>
            @else
                <td class="auto mono">{{ $s->factura?->numeroFormateado() ?? '—' }}</td>
            @endif

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

            {{-- Acciones --}}
            <td style="text-align:center">
                @if($s->esManual())
                <form method="POST" action="{{ route('seguimientos.destroy', $s->id) }}" style="display:inline"
                      onsubmit="return confirm('¿Eliminar este proceso cargado a mano?')">
                    @csrf @method('DELETE')
                    <button class="gbtn gbtn-danger gbtn-xs" title="Eliminar">✕</button>
                </form>
                @endif
            </td>
        </tr>
        @empty
        <tr><td colspan="18" style="text-align:center;color:var(--txd);padding:32px">No hay procesos en {{ $anio }}.</td></tr>
        @endforelse
    </tbody>
</table>
</div>

@if($seguimientos->hasPages())
<div style="display:flex;justify-content:space-between;align-items:center;margin-top:12px">
    <span class="txd" style="font-size:12px">Página {{ $seguimientos->currentPage() }} de {{ $seguimientos->lastPage() }} · {{ $seguimientos->total() }} filas</span>
    <div style="display:flex;gap:8px">
        @if($seguimientos->onFirstPage())<span class="gbtn gbtn-ghost gbtn-sm" style="opacity:.4">← Anterior</span>
        @else<a href="{{ $seguimientos->previousPageUrl() }}" class="gbtn gbtn-ghost gbtn-sm">← Anterior</a>@endif
        @if($seguimientos->hasMorePages())<a href="{{ $seguimientos->nextPageUrl() }}" class="gbtn gbtn-ghost gbtn-sm">Siguiente →</a>
        @else<span class="gbtn gbtn-ghost gbtn-sm" style="opacity:.4">Siguiente →</span>@endif
    </div>
</div>
@endif

{{-- ── Barra flotante de selección ── --}}
<div id="calc-bar" style="display:none;position:fixed;left:50%;bottom:22px;transform:translateX(-50%);
     z-index:1040;background:var(--bg-s);border:1px solid var(--bm);border-radius:999px;
     box-shadow:0 10px 30px rgba(0,0,0,.5);padding:9px 10px 9px 18px;display:none;align-items:center;gap:14px">
    <span style="font-size:13px;color:var(--tx)"><strong id="calc-count">0</strong> seleccionadas</span>
    <button type="button" class="gbtn gbtn-primary gbtn-sm" onclick="abrirCalc()">🧮 Calcular</button>
    <button type="button" class="gbtn gbtn-ghost gbtn-sm" onclick="limpiarSel()">Limpiar</button>
</div>

{{-- ── Modal de cálculo temporal ── --}}
<div id="calc-overlay" style="display:none;position:fixed;inset:0;z-index:1050;background:rgba(0,0,0,.6);
     align-items:flex-start;justify-content:center;padding:40px 16px;overflow:auto">
    <div class="gcard" style="width:100%;max-width:760px;margin:0">
        <div class="gcard-hd">
            <span class="gcard-title">🧮 Cálculo de selección <span class="txd" style="font-weight:400">(temporal — no se guarda)</span></span>
            <button type="button" class="gbtn gbtn-ghost gbtn-xs" onclick="cerrarCalc()">✕</button>
        </div>
        <div class="gcard-bd" style="padding:0">
            <div style="overflow-x:auto">
                <table class="seg" style="font-size:11px">
                    <thead>
                        <tr>
                            <th>F. Factura</th>
                            <th style="text-align:right">Facturado</th>
                            <th style="text-align:right">Sin IVA</th>
                            <th style="text-align:right">IVA 21%</th>
                            <th style="text-align:right">5%</th>
                            <th style="text-align:right">Total (IVA+5%)</th>
                        </tr>
                    </thead>
                    <tbody id="calc-body"></tbody>
                    <tfoot>
                        <tr style="border-top:2px solid var(--bm)">
                            <td style="padding:9px 6px;font-weight:700;color:var(--tx)">TOTAL (<span id="calc-n">0</span>)</td>
                            <td class="calc" id="tot-facturado" style="font-size:11px;color:var(--tx);font-weight:700"></td>
                            <td class="calc" id="tot-neto"      style="font-size:11px;color:var(--tx);font-weight:700"></td>
                            <td class="calc" id="tot-iva"       style="font-size:11px;color:var(--tx);font-weight:700"></td>
                            <td class="calc" id="tot-cinco"     style="font-size:11px;color:var(--tx);font-weight:700"></td>
                            <td class="calc" id="tot-total"     style="font-size:12px;color:var(--ac);font-weight:700"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div style="padding:12px 16px;border-top:1px solid var(--b);display:flex;justify-content:flex-end;gap:8px">
                <button type="button" class="gbtn gbtn-primary gbtn-sm" id="btn-copiar" onclick="copiarCalc()">📋 Copiar</button>
                <button type="button" class="gbtn gbtn-ghost gbtn-sm" onclick="cerrarCalc()">Cerrar</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
(function () {
    const CSRF = '{{ csrf_token() }}';

    // es-AR: puntos = miles, coma = decimal
    function parseMoney(v) {
        v = String(v ?? '').trim();
        if (v === '') return '';
        v = v.replace(/\./g, '').replace(',', '.').replace(/[^0-9.\-]/g, '');
        return v;
    }
    function fmtMoney(v) {
        const n = parseFloat(v);
        if (isNaN(n)) return '';
        return n.toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function guardarFila($row) {
        const url  = $row.data('url');
        const data = {};
        $row.find('.seg-input, .seg-select').each(function () {
            let v = $(this).val();
            if ($(this).hasClass('seg-money')) v = parseMoney(v);
            data[$(this).data('f')] = v;
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
            if (res.facturaFecha) $row.find('.cell-ffact').text(res.facturaFecha);
            $row.find('.cell-21').text(res.mostrarCalc ? '$' + res.iva21 : '—');
            $row.find('.cell-5').text(res.mostrarCalc ? '$' + res.cinco : '—');
            $row.find('.cell-total').text(res.mostrarCalc ? '$' + res.totalHernan : '—');
            $row.addClass('saved');
            setTimeout(() => $row.removeClass('saved'), 1000);
        })
        .catch(() => alert('No se pudo guardar el cambio. Revisá los datos e intentá de nuevo.'));
    }

    $(document).on('focus', '.seg-estado', function () {
        if ($(this).data('prev') === undefined) $(this).data('prev', $(this).val());
    });

    $(document).on('change', '.seg-input, .seg-select', function () {
        const $el  = $(this);
        const $row = $el.closest('.seg-row');
        if ($el.hasClass('seg-estado') && $el.val() === 'cobrado') {
            if (!confirm('¿Marcar esta factura como COBRADA?')) { $el.val($el.data('prev')); return; }
        }
        guardarFila($row);
    });

    $(document).on('input', '.seg-input.oc', function () {
        this.value = this.value.replace(/\D/g, '').slice(0, 4);
    });

    // Formatear montos al salir del campo
    $(document).on('blur', '.seg-money', function () {
        const p = parseMoney(this.value);
        this.value = (p === '') ? '' : fmtMoney(p);
    });
})();
</script>

{{-- ── Calculadora temporal de selección ── --}}
<script>
(function () {
    const $bar = $('#calc-bar');
    let calcTexto = '';   // contenido para el botón Copiar

    function money(n) {
        return '$' + (Number(n) || 0).toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    function actualizarBarra() {
        const n = $('.seg-check:checked').length;
        $('#calc-count').text(n);
        $bar.css('display', n > 0 ? 'flex' : 'none');
    }

    // Checkbox individual + "seleccionar todos"
    $(document).on('change', '.seg-check', actualizarBarra);
    $(document).on('change', '#seg-all', function () {
        $('.seg-check').prop('checked', this.checked);
        actualizarBarra();
    });

    window.limpiarSel = function () {
        $('.seg-check, #seg-all').prop('checked', false);
        actualizarBarra();
    };

    window.abrirCalc = function () {
        const $rows = $('.seg-check:checked').closest('.seg-row');
        if ($rows.length === 0) return;

        let tot = { facturado: 0, neto: 0, iva: 0, cinco: 0, total: 0 };
        const lineas = ['F. Factura\tFacturado\tSin IVA\tIVA 21%\t5%\tTotal (IVA+5%)'];
        const filas = $rows.map(function () {
            const d = $(this).data();
            tot.facturado += Number(d.facturado) || 0;
            tot.neto      += Number(d.neto)      || 0;
            tot.iva       += Number(d.iva)       || 0;
            tot.cinco     += Number(d.cinco)     || 0;
            tot.total     += Number(d.total)     || 0;
            lineas.push([d.ffact || '—', money(d.facturado), money(d.neto), money(d.iva), money(d.cinco), money(d.total)].join('\t'));
            return `<tr>
                <td class="auto">${d.ffact || '—'}</td>
                <td class="calc" style="font-size:11px">${money(d.facturado)}</td>
                <td class="calc" style="font-size:11px">${money(d.neto)}</td>
                <td class="calc" style="font-size:11px">${money(d.iva)}</td>
                <td class="calc" style="font-size:11px">${money(d.cinco)}</td>
                <td class="calc" style="font-size:11px;color:var(--tx);font-weight:600">${money(d.total)}</td>
            </tr>`;
        }).get().join('');

        $('#calc-body').html(filas);
        $('#calc-n').text($rows.length);
        $('#tot-facturado').text(money(tot.facturado));
        $('#tot-neto').text(money(tot.neto));
        $('#tot-iva').text(money(tot.iva));
        $('#tot-cinco').text(money(tot.cinco));
        $('#tot-total').text(money(tot.total));

        lineas.push(['TOTAL (' + $rows.length + ')', money(tot.facturado), money(tot.neto), money(tot.iva), money(tot.cinco), money(tot.total)].join('\t'));
        calcTexto = lineas.join('\n');

        $('#btn-copiar').text('📋 Copiar');
        $('#calc-overlay').css('display', 'flex');
    };

    window.copiarCalc = function () {
        const done = () => { const $b = $('#btn-copiar'); $b.text('✓ Copiado'); setTimeout(() => $b.text('📋 Copiar'), 1500); };
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(calcTexto).then(done).catch(fallback);
        } else { fallback(); }
        function fallback() {
            const ta = document.createElement('textarea');
            ta.value = calcTexto; ta.style.position = 'fixed'; ta.style.opacity = '0';
            document.body.appendChild(ta); ta.select();
            try { document.execCommand('copy'); done(); } catch (e) { alert('No se pudo copiar.'); }
            document.body.removeChild(ta);
        }
    };

    window.cerrarCalc = function () { $('#calc-overlay').css('display', 'none'); };

    // Cerrar al clickear el fondo o con Escape
    $('#calc-overlay').on('click', function (e) { if (e.target === this) cerrarCalc(); });
    $(document).on('keydown', function (e) { if (e.key === 'Escape') cerrarCalc(); });
})();
</script>
@endsection

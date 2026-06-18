@extends('layouts.app')

@section('page-title', 'Facturas')

@section('topbar-actions')
    <a href="{{ route('facturas.create') }}" class="gbtn gbtn-primary gbtn-sm">+ Nueva factura</a>
@endsection

@section('content')

@if(isset($borradores) && $borradores->count())
<div class="gcard" style="margin-bottom:16px;border-color:#3a2a14">
    <div class="gcard-hd" style="background:#1a1206">
        <span class="gcard-title" style="color:#e0a23a">💾 Borradores pendientes</span>
        <span class="txd" style="font-size:12px">{{ $borradores->count() }} sin emitir</span>
    </div>
    <div class="gcard-bd" style="padding:0">
        <table class="gtable">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th style="text-align:right">Total estimado</th>
                    <th>Motivo</th>
                    <th>Guardado</th>
                    <th style="width:170px"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($borradores as $b)
                <tr>
                    <td style="color:var(--tx)">{{ $b->cliente->nombre ?? 'Sin cliente' }}</td>
                    <td style="text-align:right" class="mono">${{ number_format($b->total, 2, ',', '.') }}</td>
                    <td class="txd" style="font-size:12px;max-width:320px">{{ \Illuminate\Support\Str::limit($b->error, 80) ?: '—' }}</td>
                    <td class="txd" style="font-size:12px">{{ $b->updated_at->format('d/m/Y H:i') }}</td>
                    <td style="text-align:right">
                        <a href="{{ route('facturas.create', ['borrador_id' => $b->id]) }}" class="gbtn gbtn-primary gbtn-xs">Retomar</a>
                        <form method="POST" action="{{ route('facturas.borradores.destroy', $b->id) }}" style="display:inline"
                              onsubmit="return confirm('¿Eliminar este borrador?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="gbtn gbtn-danger gbtn-xs">Eliminar</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<div class="gcard">
    <div class="gcard-hd">
        <span class="gcard-title">Comprobantes emitidos</span>
        <span class="txd" style="font-size:12px">{{ $facturas->count() }} registros</span>
    </div>
    <div class="gcard-bd" style="padding:0">
        <table class="gtable">
            <thead>
                <tr>
                    <th>N° Comprobante</th>
                    <th>Tipo</th>
                    <th>Cliente</th>
                    <th>Fecha</th>
                    <th style="text-align:right">Total</th>
                    <th>CAE</th>
                    <th>Estado</th>
                    <th>Cobro</th>
                    <th style="width:120px"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($facturas as $f)
                <tr>
                    <td>
                        <span class="mono" style="color:var(--ac);font-weight:600">{{ $f->numeroFormateado() }}</span>
                    </td>
                    <td style="font-size:12px">{{ $f->tipoLabel() }}</td>
                    <td style="color:var(--tx)">{{ $f->cliente->nombre }}</td>
                    <td class="txd">{{ $f->fecha->format('d/m/Y') }}</td>
                    <td style="text-align:right" class="mono">
                        <strong>${{ number_format($f->imp_total, 2, ',', '.') }}</strong>
                    </td>
                    <td>
                        @if($f->tieneCAE())
                            <span class="mono" style="font-size:11px;color:var(--green)">{{ $f->cae }}</span>
                        @else
                            <span class="txd">—</span>
                        @endif
                    </td>
                    <td>
                        <span style="color:{{ $f->estadoColor() }};font-size:12px">
                            ● {{ $f->estadoLabel() }}
                        </span>
                    </td>
                    <td>
                        @if($f->esFactura())
                            @php $ec = $f->estadoCobro(); @endphp
                            <span style="color:{{ $f->estadoCobroColor() }};font-size:12px;white-space:nowrap">
                                ● {{ $f->estadoCobroLabel() }}
                            </span>
                            @if($ec === 'parcial')
                                <div class="txd mono" style="font-size:10px">resta ${{ number_format($f->saldoPendiente(), 2, ',', '.') }}</div>
                            @endif
                        @else
                            <span class="txd" style="font-size:12px">—</span>
                        @endif
                    </td>
                    <td style="text-align:right">
                        @if($f->esFactura() && $f->estadoCobro() !== 'cobrada')
                        <button type="button" class="gbtn gbtn-primary gbtn-xs btn-cobrar"
                                data-id="{{ $f->id }}"
                                data-num="{{ $f->numeroFormateado() }}"
                                data-saldo="{{ number_format($f->saldoPendiente(), 2, '.', '') }}"
                                data-forma="{{ $f->forma_pago }}">$ Cobrar</button>
                        @endif
                        <a href="{{ route('facturas.show', $f->id) }}" class="gbtn gbtn-ghost gbtn-xs">Ver</a>
                        <a href="{{ route('facturas.pdf', $f->id) }}" class="gbtn gbtn-ghost gbtn-xs" target="_blank" title="Ver / Imprimir PDF">🖨</a>
                        <a href="{{ route('facturas.pdf', ['factura' => $f->id, 'download' => 1]) }}" class="gbtn gbtn-ghost gbtn-xs" title="Descargar PDF">⬇</a>
                        @if(in_array($f->tipo, [1, 6, 11]))
                        <a href="{{ route('remitos.create', ['factura_id' => $f->id]) }}" class="gbtn gbtn-ghost gbtn-xs" title="Crear remito">📦 Remito</a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align:center;color:var(--txd);padding:32px">
                        No hay facturas emitidas.
                        <a href="{{ route('facturas.create') }}" style="color:var(--ac)">Emitir la primera</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ── Modal: registrar cobro ─────────────────────────────────────────── --}}
<div id="modal-cobrar" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.75);z-index:9999;align-items:center;justify-content:center">
    <div style="background:#141414;border:1px solid #2a2a2a;border-radius:12px;padding:28px;max-width:420px;width:92%">
        <div style="font-size:1.05rem;font-weight:600;color:#e8e4dc;margin-bottom:4px">Registrar cobro</div>
        <div class="txd" style="font-size:12px;margin-bottom:18px">Factura <span id="ck-num" class="mono" style="color:var(--ac)"></span></div>

        <form method="POST" id="form-cobrar" action="">
            @csrf
            <div class="gfg">
                <label class="glabel">Monto a cobrar *</label>
                <input type="number" name="monto" id="ck-monto" class="ginput" step="0.01" min="0.01" required>
                <div class="txd" style="font-size:11px;margin-top:3px">Saldo pendiente: <span id="ck-saldo" class="mono"></span>. Podés cobrar menos (queda Parcial).</div>
            </div>
            <div class="gfg">
                <label class="glabel">Forma de pago *</label>
                <select name="forma_pago" id="ck-forma" class="gselect" required>
                    @foreach(\App\Models\Cobro::FORMAS as $val => $label)
                        <option value="{{ $val }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="gfg">
                <label class="glabel">Fecha de cobro *</label>
                <input type="date" name="fecha" id="ck-fecha" class="ginput" value="{{ now()->format('Y-m-d') }}" max="{{ now()->format('Y-m-d') }}" required>
            </div>
            <div class="gfg" style="margin-bottom:18px">
                <label class="glabel">Observaciones</label>
                <input type="text" name="observaciones" class="ginput" placeholder="Opcional (N° de cheque, etc.)">
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end">
                <button type="button" class="gbtn gbtn-ghost gbtn-sm" onclick="cerrarCobro()">Cancelar</button>
                <button type="submit" class="gbtn gbtn-primary gbtn-sm">Registrar cobro</button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
(function () {
    const base   = '{{ url('facturas') }}';
    const $modal = $('#modal-cobrar');

    $(document).on('click', '.btn-cobrar', function () {
        const id    = $(this).data('id');
        const saldo = parseFloat($(this).data('saldo')) || 0;
        const forma = ($(this).data('forma') || '').toString();

        $('#form-cobrar').attr('action', base + '/' + id + '/cobros');
        $('#ck-num').text($(this).data('num'));
        $('#ck-monto').val(saldo.toFixed(2)).attr('max', saldo.toFixed(2));
        $('#ck-saldo').text('$' + saldo.toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        if (forma) $('#ck-forma').val(forma);
        $modal.css('display', 'flex');
    });

    window.cerrarCobro = function () { $modal.hide(); };
    $modal.on('click', function (e) { if (e.target === this) window.cerrarCobro(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') window.cerrarCobro(); });
})();
</script>
@endsection

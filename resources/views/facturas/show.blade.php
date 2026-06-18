@extends('layouts.app')

@section('page-title', $factura->tipoLabel() . ' ' . $factura->numeroFormateado())

@section('topbar-actions')
    <a href="{{ route('facturas.index') }}" class="gbtn gbtn-ghost gbtn-sm">← Volver</a>
    <a href="{{ route('facturas.pdf', $factura->id) }}" class="gbtn gbtn-ghost gbtn-sm" target="_blank">🖨 Ver / Imprimir</a>
    <a href="{{ route('facturas.pdf', ['factura' => $factura->id, 'download' => 1]) }}" class="gbtn gbtn-primary gbtn-sm">⬇ Descargar PDF</a>
@endsection

@section('content')

<div style="display:grid;grid-template-columns:1fr 300px;gap:16px;align-items:start">

{{-- ── Columna principal ──────────────────────────────────────────────── --}}
<div>

    {{-- Encabezado --}}
    <div class="gcard" style="margin-bottom:16px">
        <div class="gcard-hd">
            <span class="gcard-title">{{ $factura->tipoLabel() }} — {{ $factura->numeroFormateado() }}</span>
            <div style="display:flex;gap:16px;align-items:center">
                @if($factura->esFactura())
                <span style="color:{{ $factura->estadoCobroColor() }};font-size:13px;font-weight:600" title="Estado de cobro (interno)">
                    💲 {{ $factura->estadoCobroLabel() }}
                </span>
                @endif
                <span style="color:{{ $factura->estadoColor() }};font-size:13px;font-weight:600">
                    ● {{ $factura->estadoLabel() }}
                </span>
            </div>
        </div>
        <div class="gcard-bd">
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px">

                <div>
                    <div class="txd" style="font-size:11px;margin-bottom:2px">CLIENTE</div>
                    <div style="font-weight:600">{{ $factura->cliente->nombre }}</div>
                    @if($factura->cliente->email)
                        <div class="txd" style="font-size:12px">{{ $factura->cliente->email }}</div>
                    @endif
                </div>

                <div>
                    <div class="txd" style="font-size:11px;margin-bottom:2px">RECEPTOR</div>
                    <div style="font-size:13px">{{ $factura->docTipoLabel() }}</div>
                    @if($factura->doc_nro)
                        <div class="mono" style="font-size:12px;color:var(--txd)">{{ $factura->doc_nro }}</div>
                    @endif
                </div>

                <div>
                    <div class="txd" style="font-size:11px;margin-bottom:2px">FECHA</div>
                    <div>{{ $factura->fecha->format('d/m/Y') }}</div>
                    <div class="txd" style="font-size:12px">{{ $factura->tipoLabel() }}</div>
                </div>

                @if($factura->observaciones)
                <div style="grid-column:1/-1;border-top:1px solid var(--b);padding-top:12px;margin-top:4px">
                    <div class="txd" style="font-size:11px;margin-bottom:4px">OBSERVACIONES</div>
                    <div style="font-size:13px;white-space:pre-line">{{ $factura->observaciones }}</div>
                </div>
                @endif

                <div style="grid-column:1/-1;border-top:1px solid var(--b);padding-top:12px;margin-top:4px;display:flex;gap:32px">
                    <div>
                        <div class="txd" style="font-size:11px;margin-bottom:2px">EMITIDO POR</div>
                        <div style="font-size:12px">{{ $factura->createdBy?->name ?? '—' }}</div>
                        <div class="txd" style="font-size:11px">{{ $factura->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                    @if($factura->presupuesto)
                    <div>
                        <div class="txd" style="font-size:11px;margin-bottom:2px">PRESUPUESTO</div>
                        <a href="{{ route('presupuestos.show', $factura->presupuesto_id) }}"
                           style="font-size:12px;color:var(--ac);text-decoration:none">
                            {{ $factura->presupuesto->numeroFormateado() }}
                        </a>
                    </div>
                    @endif
                </div>

            </div>
        </div>
    </div>

    {{-- Ítems --}}
    <div class="gcard">
        <div class="gcard-hd"><span class="gcard-title">Detalle</span></div>
        <div class="gcard-bd" style="padding:0">
            <table class="gtable">
                <thead>
                    <tr>
                        <th>Descripción</th>
                        <th style="text-align:center">Cant.</th>
                        <th style="text-align:right">Precio unit.</th>
                        <th style="text-align:right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($factura->items as $item)
                    <tr>
                        <td>{{ $item->descripcion }}</td>
                        <td style="text-align:center" class="mono">{{ rtrim(rtrim(number_format($item->cantidad, 3, ',', '.'), '0'), ',') }}</td>
                        <td style="text-align:right" class="mono">${{ number_format($item->precio_unitario, 2, ',', '.') }}</td>
                        <td style="text-align:right" class="mono">${{ number_format($item->subtotal, 2, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{-- Totales --}}
        <div style="padding:14px 18px;border-top:1px solid var(--b);display:flex;flex-direction:column;align-items:flex-end;gap:6px">
            @if($factura->imp_iva > 0)
            <div style="display:flex;gap:32px;color:var(--txd);font-size:13px">
                <span>Neto gravado</span>
                <span class="mono" style="min-width:120px;text-align:right">${{ number_format($factura->imp_neto, 2, ',', '.') }}</span>
            </div>
            <div style="display:flex;gap:32px;color:var(--txd);font-size:13px">
                <span>IVA 21%</span>
                <span class="mono" style="min-width:120px;text-align:right">${{ number_format($factura->imp_iva, 2, ',', '.') }}</span>
            </div>
            @endif
            <div style="display:flex;gap:32px;align-items:center;margin-top:4px">
                <span style="font-size:12px;color:var(--txd);letter-spacing:1px;text-transform:uppercase">Total</span>
                <span class="mono" style="font-size:20px;font-weight:600;color:var(--tx)">${{ number_format($factura->imp_total, 2, ',', '.') }}</span>
            </div>
        </div>
    </div>

    {{-- Cobranza (control interno, no fiscal) — solo facturas --}}
    @if($factura->esFactura())
    <div class="gcard" style="margin-top:16px">
        <div class="gcard-hd">
            <span class="gcard-title">Cobranza</span>
            <span style="color:{{ $factura->estadoCobroColor() }};font-size:13px;font-weight:600">
                💲 {{ $factura->estadoCobroLabel() }}
            </span>
        </div>
        <div class="gcard-bd">

            {{-- Resumen --}}
            <div style="display:flex;gap:32px;margin-bottom:14px;flex-wrap:wrap">
                <div>
                    <div class="txd" style="font-size:11px">TOTAL</div>
                    <div class="mono">${{ number_format($factura->imp_total, 2, ',', '.') }}</div>
                </div>
                <div>
                    <div class="txd" style="font-size:11px">COBRADO</div>
                    <div class="mono" style="color:var(--green)">${{ number_format($factura->totalCobrado(), 2, ',', '.') }}</div>
                </div>
                <div>
                    <div class="txd" style="font-size:11px">SALDO</div>
                    <div class="mono" style="color:{{ $factura->saldoPendiente() > 0.009 ? '#f59e0b' : 'var(--txd)' }}">
                        ${{ number_format($factura->saldoPendiente(), 2, ',', '.') }}
                    </div>
                </div>
                @if($factura->forma_pago)
                <div>
                    <div class="txd" style="font-size:11px">FORMA ACORDADA</div>
                    <div>{{ $factura->formaPagoLabel() }}</div>
                </div>
                @endif
            </div>

            {{-- Lista de cobros --}}
            @if($factura->cobros->count())
            <table class="gtable" style="margin-bottom:14px">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Forma</th>
                        <th style="text-align:right">Monto</th>
                        <th>Registró</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($factura->cobros as $c)
                    <tr>
                        <td>{{ $c->fecha->format('d/m/Y') }}</td>
                        <td>{{ $c->formaLabel() }}@if($c->observaciones)<div class="txd" style="font-size:11px">{{ $c->observaciones }}</div>@endif</td>
                        <td style="text-align:right" class="mono">${{ number_format($c->monto, 2, ',', '.') }}</td>
                        <td class="txd" style="font-size:12px">{{ $c->createdBy?->name ?? '—' }}</td>
                        <td style="text-align:right">
                            <form method="POST" action="{{ route('cobros.destroy', $c->id) }}"
                                  onsubmit="return confirm('¿Eliminar este cobro?')" style="display:inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="gbtn gbtn-danger gbtn-xs">×</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif

            {{-- Registrar cobro --}}
            @if($factura->saldoPendiente() > 0.009)
            <form method="POST" action="{{ route('facturas.cobros.store', $factura->id) }}"
                  style="display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:10px;align-items:end;border-top:1px solid var(--b);padding-top:14px">
                @csrf
                <div class="gfg" style="margin-bottom:0">
                    <label class="glabel">Monto *</label>
                    <input type="number" name="monto" class="ginput" step="0.01" min="0.01"
                           max="{{ number_format($factura->saldoPendiente(), 2, '.', '') }}"
                           value="{{ number_format($factura->saldoPendiente(), 2, '.', '') }}" required>
                </div>
                <div class="gfg" style="margin-bottom:0">
                    <label class="glabel">Forma *</label>
                    <select name="forma_pago" class="gselect" required>
                        @foreach(\App\Models\Cobro::FORMAS as $val => $label)
                            <option value="{{ $val }}" {{ $factura->forma_pago == $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="gfg" style="margin-bottom:0">
                    <label class="glabel">Fecha *</label>
                    <input type="date" name="fecha" class="ginput" value="{{ now()->format('Y-m-d') }}" max="{{ now()->format('Y-m-d') }}" required>
                </div>
                <button type="submit" class="gbtn gbtn-primary">Registrar</button>
            </form>
            @else
            <div style="border-top:1px solid var(--b);padding-top:12px;color:var(--green);font-size:13px">
                ✓ Factura cobrada en su totalidad.
            </div>
            @endif

        </div>
    </div>
    @endif

    {{-- Remitos relacionados --}}
    @if($factura->remitos->count())
    <div class="gcard" style="margin-top:16px">
        <div class="gcard-hd"><span class="gcard-title">Remitos relacionados</span></div>
        <div class="gcard-bd" style="padding:0">
            <table class="gtable">
                <tbody>
                    @foreach($factura->remitos as $r)
                    <tr>
                        <td><a href="{{ route('remitos.show', $r->id) }}" style="color:var(--ac);text-decoration:none" class="mono">{{ $r->numeroFormateado() }}</a></td>
                        <td class="txd">{{ $r->fecha->format('d/m/Y') }}</td>
                        <td><span style="color:{{ $r->estadoColor() }};font-size:12px">● {{ $r->estadoLabel() }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>

{{-- ── Columna lateral — CAE ──────────────────────────────────────────── --}}
<div style="display:flex;flex-direction:column;gap:16px">

    <div class="gcard">
        <div class="gcard-hd"><span class="gcard-title">Datos fiscales</span></div>
        <div class="gcard-bd" style="display:flex;flex-direction:column;gap:14px">

            <div>
                <div class="txd" style="font-size:10px;letter-spacing:1px;text-transform:uppercase;margin-bottom:4px">CAE</div>
                <div class="mono" style="font-size:13px;color:var(--green);word-break:break-all">
                    {{ $factura->cae ?? '—' }}
                </div>
            </div>

            @if($factura->cae_vencimiento)
            <div>
                <div class="txd" style="font-size:10px;letter-spacing:1px;text-transform:uppercase;margin-bottom:4px">Vencimiento CAE</div>
                <div style="font-size:13px">{{ $factura->cae_vencimiento->format('d/m/Y') }}</div>
                @if($factura->cae_vencimiento->isPast())
                    <div style="font-size:11px;color:var(--amber);margin-top:2px">⚠ CAE vencido</div>
                @endif
            </div>
            @endif

            <div>
                <div class="txd" style="font-size:10px;letter-spacing:1px;text-transform:uppercase;margin-bottom:4px">Punto de venta</div>
                <div class="mono" style="font-size:13px">{{ str_pad($factura->punto_venta, 4, '0', STR_PAD_LEFT) }}</div>
            </div>

            <div style="border-top:1px solid var(--b);padding-top:14px">
                <div class="txd" style="font-size:10px;letter-spacing:1px;text-transform:uppercase;margin-bottom:8px">QR ARCA</div>
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&data={{ urlencode($factura->qrUrl()) }}"
                     alt="QR ARCA" style="width:160px;height:160px;display:block;border-radius:6px;background:#fff;padding:4px">
            </div>

        </div>
    </div>

</div>
</div>{{-- /grid --}}

@endsection

@extends('layouts.app')

@section('page-title', $factura->tipoLabel() . ' ' . $factura->numeroFormateado())

@section('topbar-actions')
    <a href="{{ route('facturas.index') }}" class="gbtn gbtn-ghost gbtn-sm">← Volver</a>
    <a href="{{ route('facturas.print', $factura->id) }}" class="gbtn gbtn-ghost gbtn-sm" target="_blank">🖨 Imprimir</a>
    <a href="{{ route('facturas.print', $factura->id) }}?auto=1" class="gbtn gbtn-primary gbtn-sm" target="_blank">⬇ Descargar PDF</a>
@endsection

@section('content')

<div style="display:grid;grid-template-columns:1fr 300px;gap:16px;align-items:start">

{{-- ── Columna principal ──────────────────────────────────────────────── --}}
<div>

    {{-- Encabezado --}}
    <div class="gcard" style="margin-bottom:16px">
        <div class="gcard-hd">
            <span class="gcard-title">{{ $factura->tipoLabel() }} — {{ $factura->numeroFormateado() }}</span>
            <span style="color:{{ $factura->estadoColor() }};font-size:13px;font-weight:600">
                ● {{ $factura->estadoLabel() }}
            </span>
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

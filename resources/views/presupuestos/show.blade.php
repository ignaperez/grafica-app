@extends('layouts.app')

@section('page-title', $presupuesto->numeroFormateado())

@section('topbar-actions')
    <a href="{{ route('presupuestos.index') }}" class="gbtn gbtn-ghost gbtn-sm">← Volver</a>
    <a href="{{ route('presupuestos.edit', $presupuesto->id) }}" class="gbtn gbtn-ghost gbtn-sm">Editar</a>
    <a href="{{ route('presupuestos.print', $presupuesto->id) }}" class="gbtn gbtn-ghost gbtn-sm" target="_blank">🖨 Imprimir</a>
@endsection

@section('content')

<div style="display:grid;grid-template-columns:1fr 320px;gap:16px;align-items:start">

{{-- ── Columna principal ──────────────────────────────────────────────── --}}
<div>

    {{-- Cabecera datos --}}
    <div class="gcard" style="margin-bottom:16px">
        <div class="gcard-hd">
            <span class="gcard-title">{{ $presupuesto->numeroFormateado() }}</span>
            <span style="color:{{ $presupuesto->estadoColor() }};font-size:13px;font-weight:600">
                ● {{ $presupuesto->estadoLabel() }}
            </span>
        </div>
        <div class="gcard-bd">
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px">

                <div>
                    <div class="txd" style="font-size:11px;margin-bottom:2px">CLIENTE</div>
                    <div style="font-weight:600">{{ $presupuesto->cliente->nombre }}</div>
                    @if($presupuesto->cliente->email)
                        <div class="txd" style="font-size:12px">{{ $presupuesto->cliente->email }}</div>
                    @endif
                    @if($presupuesto->cliente->telefono)
                        <div class="txd" style="font-size:12px">{{ $presupuesto->cliente->telefono }}</div>
                    @endif
                </div>

                <div>
                    <div class="txd" style="font-size:11px;margin-bottom:2px">FECHA</div>
                    <div>{{ $presupuesto->fecha->format('d/m/Y') }}</div>
                    @if($presupuesto->fecha_vencimiento)
                        <div class="txd" style="font-size:12px">
                            Vence:
                            @if($presupuesto->fecha_vencimiento->isPast() && $presupuesto->estado === 'enviado')
                                <span style="color:#e53935">{{ $presupuesto->fecha_vencimiento->format('d/m/Y') }}</span>
                            @else
                                {{ $presupuesto->fecha_vencimiento->format('d/m/Y') }}
                            @endif
                        </div>
                    @endif
                </div>

                <div>
                    <div class="txd" style="font-size:11px;margin-bottom:2px">LISTA DE PRECIOS</div>
                    @if($presupuesto->listaPrecio)
                        <div>{{ $presupuesto->listaPrecio->nombre }}</div>
                        <div class="txd" style="font-size:12px">× {{ number_format($presupuesto->multiplicador, 2) }}</div>
                    @else
                        <div class="txd">—</div>
                    @endif
                </div>

                @if($presupuesto->observaciones)
                <div style="grid-column:1/-1;border-top:1px solid var(--b);padding-top:12px;margin-top:4px">
                    <div class="txd" style="font-size:11px;margin-bottom:4px">OBSERVACIONES</div>
                    <div style="font-size:13px;white-space:pre-line">{{ $presupuesto->observaciones }}</div>
                </div>
                @endif

                {{-- Auditoría --}}
                <div style="grid-column:1/-1;border-top:1px solid var(--b);padding-top:12px;margin-top:4px;display:flex;gap:32px">
                    <div>
                        <div class="txd" style="font-size:11px;margin-bottom:2px">CREADO POR</div>
                        <div style="font-size:12px">{{ $presupuesto->createdBy?->name ?? '—' }}</div>
                        <div class="txd" style="font-size:11px">{{ $presupuesto->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                    @if($presupuesto->updatedBy && $presupuesto->updated_at != $presupuesto->created_at)
                    <div>
                        <div class="txd" style="font-size:11px;margin-bottom:2px">ÚLTIMA MODIFICACIÓN</div>
                        <div style="font-size:12px">{{ $presupuesto->updatedBy->name }}</div>
                        <div class="txd" style="font-size:11px">{{ $presupuesto->updated_at->format('d/m/Y H:i') }}</div>
                    </div>
                    @endif
                </div>

            </div>
        </div>
    </div>

    {{-- Tabla de ítems --}}
    <div class="gcard" style="margin-bottom:16px">
        <div class="gcard-hd">
            <span class="gcard-title">Ítems</span>
            <span class="txd" style="font-size:12px">{{ $presupuesto->items->count() }} líneas</span>
        </div>
        <div class="gcard-bd" style="padding:0">
            <table class="gtable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Descripción</th>
                        <th style="text-align:center">Unidad</th>
                        <th style="text-align:right">Medida</th>
                        <th style="text-align:right">P. Unit.</th>
                        <th style="text-align:right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($presupuesto->items as $i => $item)
                <tr>
                    <td class="txd mono" style="font-size:11px">{{ $i + 1 }}</td>
                    <td>
                        <div style="font-weight:500">{{ $item->descripcion }}</div>
                        @if($item->maquina || $item->material)
                            <div class="txd" style="font-size:11px">
                                @if($item->maquina) {{ $item->maquina->nombre }} @endif
                                @if($item->maquina && $item->material) — @endif
                                @if($item->material) {{ $item->material->nombre }} @endif
                            </div>
                        @endif
                        @if($item->unidad === 'm2')
                            <div class="txd" style="font-size:11px">
                                {{ number_format($item->ancho, 2) }} × {{ number_format($item->alto, 2) }} m — {{ $item->cantidad }} u
                            </div>
                        @elseif($item->unidad === 'ml')
                            <div class="txd" style="font-size:11px">
                                {{ number_format($item->largo, 2) }} ml — {{ $item->cantidad }} u
                            </div>
                        @else
                            <div class="txd" style="font-size:11px">{{ $item->cantidad }} unidad(es)</div>
                        @endif
                    </td>
                    <td style="text-align:center" class="txd">{{ $item->unidadLabel() }}</td>
                    <td style="text-align:right" class="mono">
                        {{ number_format($item->medidaTotal(), 3) }}
                    </td>
                    <td style="text-align:right" class="mono">
                        ${{ number_format($item->precio_unitario, 2) }}
                    </td>
                    <td style="text-align:right" class="mono">
                        <strong>${{ number_format($item->subtotal, 2) }}</strong>
                    </td>
                </tr>
                @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" style="text-align:right;font-weight:600;padding:12px 16px;border-top:1px solid var(--bm)">
                            TOTAL
                        </td>
                        <td style="text-align:right;padding:12px 16px;border-top:1px solid var(--bm)">
                            <strong class="mono" style="font-size:1.1rem">
                                ${{ number_format($presupuesto->total, 2) }}
                            </strong>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- OT asociada --}}
    @if($presupuesto->ordenTrabajo)
    <div class="gcard" style="margin-bottom:16px;border-left:3px solid #4caf50">
        <div class="gcard-bd" style="display:flex;align-items:center;gap:16px">
            <div style="flex:1">
                <div class="txd" style="font-size:11px">ORDEN DE TRABAJO ASOCIADA</div>
                <div style="font-weight:600">OT #{{ $presupuesto->ordenTrabajo->id }}</div>
            </div>
            <a href="{{ route('ordenes-trabajo.show', $presupuesto->ordenTrabajo->id) }}"
               class="gbtn gbtn-ghost gbtn-sm">Ver OT →</a>
        </div>
    </div>
    @endif

</div>

{{-- ── Columna lateral: acciones ─────────────────────────────────────────── --}}
<div>

    {{-- Estado --}}
    <div class="gcard" style="margin-bottom:16px">
        <div class="gcard-hd"><span class="gcard-title">Estado</span></div>
        <div class="gcard-bd" style="display:flex;flex-direction:column;gap:8px">

            @php
                $estados = [
                    'borrador'  => ['Borrador',  '#888'],
                    'enviado'   => ['Enviado',   '#2196f3'],
                    'aprobado'  => ['Aprobado',  '#4caf50'],
                    'rechazado' => ['Rechazado', '#e53935'],
                ];
            @endphp

            @foreach($estados as $val => [$label, $color])
            @if($val !== $presupuesto->estado)
            <form action="{{ route('presupuestos.estado', $presupuesto->id) }}" method="POST">
                @csrf @method('PATCH')
                <input type="hidden" name="estado" value="{{ $val }}">
                <button type="submit" class="gbtn gbtn-ghost gbtn-sm" style="width:100%;text-align:left;color:{{ $color }}">
                    ● Marcar como {{ $label }}
                </button>
            </form>
            @else
            <div style="padding:6px 10px;background:var(--bg-h);border-radius:6px;font-size:12px;color:{{ $color }};font-weight:600">
                ● Estado actual: {{ $label }}
            </div>
            @endif
            @endforeach

        </div>
    </div>

    {{-- Convertir a OT --}}
    @if(!$presupuesto->ordenTrabajo)
    <div class="gcard" style="margin-bottom:16px">
        <div class="gcard-hd"><span class="gcard-title">Acciones</span></div>
        <div class="gcard-bd">
            @if($presupuesto->estado === 'aprobado')
                <form action="{{ route('presupuestos.convertir-ot', $presupuesto->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="gbtn gbtn-primary" style="width:100%"
                            onclick="return confirm('¿Crear una Orden de Trabajo desde este presupuesto?')">
                        → Convertir a OT
                    </button>
                </form>
            @else
                <div class="txd" style="font-size:12px">
                    El presupuesto debe estar <strong>Aprobado</strong> para convertirlo en una OT.
                </div>
            @endif
        </div>
    </div>
    @endif

    {{-- Peligro --}}
    <div class="gcard" style="border-color:#e53935">
        <div class="gcard-bd">
            <form action="{{ route('presupuestos.destroy', $presupuesto->id) }}" method="POST"
                  onsubmit="return confirm('¿Eliminar {{ $presupuesto->numeroFormateado() }}? Esta acción no se puede deshacer.')">
                @csrf @method('DELETE')
                <button type="submit" class="gbtn gbtn-danger gbtn-sm" style="width:100%">
                    Eliminar presupuesto
                </button>
            </form>
        </div>
    </div>

</div>
</div>

@endsection

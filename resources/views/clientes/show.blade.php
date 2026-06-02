@extends('layouts.app')

@section('page-title', $cliente->nombre)

@section('topbar-actions')
    <a href="{{ route('clientes.edit', $cliente->id) }}" class="gbtn gbtn-ghost gbtn-sm">Editar</a>
@endsection

@section('content')
<div style="display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:start;">

    {{-- ── Columna principal ── --}}
    <div style="display:flex;flex-direction:column;gap:16px;">

        <div class="gcard">
            <div class="gcard-hd">
                <span class="gcard-title">Datos del cliente</span>
            </div>
            <div class="gcard-bd">
                <dl style="display:grid;grid-template-columns:160px 1fr;gap:10px 16px;font-size:13px;">
                    <dt style="color:var(--txd);font-size:11px;text-transform:uppercase;letter-spacing:.08em;align-self:start;padding-top:2px;">Nombre</dt>
                    <dd style="margin:0;font-weight:600;color:var(--tx);">{{ $cliente->nombre }}</dd>

                    <dt style="color:var(--txd);font-size:11px;text-transform:uppercase;letter-spacing:.08em;align-self:start;padding-top:2px;">CUIT</dt>
                    <dd style="margin:0;" class="mono">
                        @if($cliente->cuit)
                            {{ $cliente->cuit }}
                        @else
                            <span class="txd">Sin CUIT registrado</span>
                        @endif
                    </dd>

                    <dt style="color:var(--txd);font-size:11px;text-transform:uppercase;letter-spacing:.08em;align-self:start;padding-top:2px;">Condición IVA</dt>
                    <dd style="margin:0;color:var(--txm);">{{ $cliente->condicionIvaLabel() }}</dd>

                    @if($cliente->telefono)
                    <dt style="color:var(--txd);font-size:11px;text-transform:uppercase;letter-spacing:.08em;align-self:start;padding-top:2px;">Teléfono</dt>
                    <dd style="margin:0;color:var(--txm);">{{ $cliente->telefono }}</dd>
                    @endif

                    @if($cliente->email)
                    <dt style="color:var(--txd);font-size:11px;text-transform:uppercase;letter-spacing:.08em;align-self:start;padding-top:2px;">Email</dt>
                    <dd style="margin:0;color:var(--txm);">{{ $cliente->email }}</dd>
                    @endif

                    @if($cliente->direccion)
                    <dt style="color:var(--txd);font-size:11px;text-transform:uppercase;letter-spacing:.08em;align-self:start;padding-top:2px;">Dirección</dt>
                    <dd style="margin:0;color:var(--txm);">{{ $cliente->direccion }}</dd>
                    @endif

                    <dt style="color:var(--txd);font-size:11px;text-transform:uppercase;letter-spacing:.08em;align-self:start;padding-top:2px;">Lista de precios</dt>
                    <dd style="margin:0;color:var(--txm);">{{ $cliente->listaPrecio->nombre ?? '—' }}</dd>
                </dl>
            </div>
        </div>

        {{-- Órdenes de trabajo recientes --}}
        @if($cliente->ordenesTrabajo->isNotEmpty())
        <div class="gcard">
            <div class="gcard-hd">
                <span class="gcard-title">Órdenes de trabajo ({{ $cliente->ordenesTrabajo->count() }})</span>
                <a href="{{ route('ordenes-trabajo.index') }}?cliente={{ $cliente->id }}" class="gbtn gbtn-ghost gbtn-xs">Ver todas</a>
            </div>
            <div class="gcard-bd" style="padding:0;">
                <table class="gtable">
                    <thead>
                        <tr>
                            <th>N°</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($cliente->ordenesTrabajo->sortByDesc('created_at')->take(10) as $ot)
                    <tr>
                        <td>
                            <a href="{{ route('ordenes-trabajo.show', $ot->id) }}"
                               class="mono" style="color:var(--ac);font-size:12px;">
                                OT-{{ str_pad($ot->id, 4, '0', STR_PAD_LEFT) }}
                            </a>
                        </td>
                        <td style="color:var(--txm);font-size:12px;">{{ \Carbon\Carbon::parse($ot->created_at)->format('d/m/Y') }}</td>
                        <td><span class="badge-estado be-{{ $ot->estado }}">{{ $ot->estado }}</span></td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

    </div>

    {{-- ── Sidebar ── --}}
    <div style="display:flex;flex-direction:column;gap:16px;">

        <div class="gcard">
            <div class="gcard-bd" style="display:flex;flex-direction:column;gap:8px;">
                <a href="{{ route('clientes.edit', $cliente->id) }}" class="gbtn gbtn-ghost" style="text-align:center;">
                    ✏️ Editar cliente
                </a>
                <a href="{{ route('presupuestos.create') }}?cliente_id={{ $cliente->id }}"
                   class="gbtn gbtn-ghost" style="text-align:center;">
                    📋 Nuevo presupuesto
                </a>
                <a href="{{ route('remitos.create') }}?cliente_id={{ $cliente->id }}"
                   class="gbtn gbtn-ghost" style="text-align:center;">
                    📦 Nuevo remito
                </a>
            </div>
        </div>

        <div class="gcard">
            <div class="gcard-hd"><span class="gcard-title">Info</span></div>
            <div class="gcard-bd" style="font-size:11.5px;color:var(--txd);display:flex;flex-direction:column;gap:4px;">
                <div>Alta: {{ $cliente->created_at->format('d/m/Y') }}</div>
                <div>ID: <span class="mono">{{ $cliente->id }}</span></div>
            </div>
        </div>

    </div>

</div>
@endsection

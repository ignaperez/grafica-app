@extends('layouts.app')

@section('page-title', 'Presupuestos')

@section('topbar-actions')
    <a href="{{ route('presupuestos.create') }}" class="gbtn gbtn-primary gbtn-sm">+ Nuevo presupuesto</a>
@endsection

@section('content')

<div class="gcard">
    <div class="gcard-hd">
        <span class="gcard-title">Presupuestos</span>
        <span class="txd" style="font-size:12px">{{ $presupuestos->count() }} registros</span>
    </div>
    <div class="gcard-bd" style="padding:0">
        <table class="gtable">
            <thead>
                <tr>
                    <th>N°</th>
                    <th>Cliente</th>
                    <th>Fecha</th>
                    <th>Vence</th>
                    <th style="text-align:right">Total</th>
                    <th>Estado</th>
                    <th>Creado por</th>
                    <th style="width:150px"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($presupuestos as $p)
                <tr>
                    <td class="mono" style="color:var(--ac);font-weight:600">{{ $p->numeroFormateado() }}</td>
                    <td style="color:var(--tx)">{{ $p->cliente->nombre }}</td>
                    <td class="txd">{{ $p->fecha->format('d/m/Y') }}</td>
                    <td class="txd">
                        @if($p->fecha_vencimiento)
                            @if($p->fecha_vencimiento->isPast() && $p->estado === 'enviado')
                                <span style="color:#e53935">{{ $p->fecha_vencimiento->format('d/m/Y') }}</span>
                            @else
                                {{ $p->fecha_vencimiento->format('d/m/Y') }}
                            @endif
                        @else
                            —
                        @endif
                    </td>
                    <td style="text-align:right" class="mono">
                        <strong>${{ number_format($p->total, 2) }}</strong>
                    </td>
                    <td>
                        <span style="color:{{ $p->estadoColor() }};font-size:12px">
                            ● {{ $p->estadoLabel() }}
                        </span>
                    </td>
                    <td>
                        @if($p->createdBy)
                            <span class="txd" style="font-size:12px" title="Modificado por {{ $p->updatedBy?->name ?? '—' }}">
                                {{ $p->createdBy->name }}
                                @if($p->updatedBy && $p->updatedBy->id !== $p->createdBy->id)
                                    <span style="opacity:.5"> / {{ $p->updatedBy->name }}</span>
                                @endif
                            </span>
                        @else
                            <span class="txd">—</span>
                        @endif
                    </td>
                    <td style="text-align:right;white-space:nowrap">
                        <a href="{{ route('presupuestos.show', $p->id) }}" class="gbtn gbtn-ghost gbtn-xs">Ver</a>
                        <a href="{{ route('presupuestos.edit', $p->id) }}" class="gbtn gbtn-ghost gbtn-xs">Editar</a>
                        <a href="{{ route('presupuestos.print', $p->id) }}" class="gbtn gbtn-ghost gbtn-xs" target="_blank">🖨</a>

                        {{-- Dropdown: emitir --}}
                        <span class="pdrop-wrap">
                            <button type="button" class="gbtn gbtn-ghost gbtn-xs pdrop-toggle">
                                Emitir ▾
                            </button>
                            <div class="pdrop-menu">
                                <a href="{{ route('facturas.create', ['presupuesto_id' => $p->id]) }}" class="pdrop-item">
                                    🧾 Crear factura
                                </a>
                                <a href="{{ route('remitos.create', ['presupuesto_id' => $p->id]) }}" class="pdrop-item">
                                    📦 Crear remito
                                </a>
                            </div>
                        </span>

                        <form action="{{ route('presupuestos.destroy', $p->id) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('¿Eliminar {{ $p->numeroFormateado() }}?')">
                            @csrf @method('DELETE')
                            <button class="gbtn gbtn-danger gbtn-xs">×</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align:center;color:var(--txd);padding:32px">
                        No hay presupuestos.
                        <a href="{{ route('presupuestos.create') }}" style="color:var(--ac)">Crear el primero</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

@section('scripts')
<style>
    /* ── Dropdown emitir ─────────────────────────────────────────── */
    .pdrop-wrap {
        position: relative;
        display: inline-block;
    }
    .pdrop-menu {
        display: none;
        position: absolute;
        top: calc(100% + 4px);
        right: 0;
        background: #141414;
        border: 1px solid #2a2a2a;
        border-radius: 8px;
        box-shadow: 0 8px 24px rgba(0,0,0,.5);
        min-width: 160px;
        z-index: 200;
        overflow: hidden;
    }
    .pdrop-menu.open { display: block; }
    .pdrop-item {
        display: block;
        padding: 9px 14px;
        font-size: 12.5px;
        color: #888;
        text-decoration: none;
        transition: background .1s, color .1s;
        white-space: nowrap;
    }
    .pdrop-item:hover {
        background: #1e1e1e;
        color: #e8e4dc;
        text-decoration: none;
    }
    .pdrop-item + .pdrop-item {
        border-top: 1px solid #1c1c1c;
    }
</style>
<script>
(function () {
    // Toggle al hacer click en el botón
    $(document).on('click', '.pdrop-toggle', function (e) {
        e.stopPropagation();
        const menu = $(this).siblings('.pdrop-menu');
        // cerrar todos los demás abiertos
        $('.pdrop-menu.open').not(menu).removeClass('open');
        menu.toggleClass('open');
    });

    // Cerrar al hacer click fuera
    $(document).on('click', function () {
        $('.pdrop-menu.open').removeClass('open');
    });
})();
</script>
@endsection

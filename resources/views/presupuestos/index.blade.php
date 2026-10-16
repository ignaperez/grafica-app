@extends('layouts.app')

@section('page-title', 'Presupuestos')

@section('topbar-actions')
    <a href="{{ route('presupuestos.export') }}" class="gbtn gbtn-ghost gbtn-sm">⬇ Excel</a>
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
                    <th style="width:28px">
                        {{-- Agrupar varios presupuestos en uno nuevo --}}
                        <input type="checkbox" id="chk-todos" title="Seleccionar todos">
                    </th>
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
                <tr data-cliente="{{ $p->cliente_id }}">
                    <td>
                        @if($p->puedeAgruparse())
                            <input type="checkbox" class="chk-presu" value="{{ $p->id }}"
                                   data-cliente="{{ $p->cliente_id }}"
                                   data-cnombre="{{ $p->cliente->nombre }}"
                                   data-numero="{{ $p->numeroFormateado() }}">
                        @endif
                    </td>
                    <td class="mono" style="color:var(--ac);font-weight:600">
                        {{ $p->numeroFormateado() }}
                        @if($p->estaAgrupado())
                            <a href="{{ route('presupuestos.show', $p->agrupado_en_id) }}"
                               style="display:block;font-size:10px;color:#e6a23c;text-decoration:none;font-weight:400"
                               title="Se agrupó en este presupuesto: ya no se factura por separado">
                                ↳ en {{ $p->agrupadoEn?->numeroFormateado() }}
                            </a>
                        @elseif($p->agrupados_count > 0)
                            <span style="display:block;font-size:10px;color:#4caf50;font-weight:400"
                                  title="Agrupa {{ $p->agrupados_count }} presupuestos">
                                agrupa {{ $p->agrupados_count }}
                            </span>
                        @endif
                    </td>
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
                        <a href="{{ route('presupuestos.print', $p->id) }}" class="gbtn gbtn-ghost gbtn-xs" target="_blank" title="Imprimir">🖨</a>
                        <a href="{{ route('presupuestos.pdf', ['presupuesto' => $p->id, 'download' => 1]) }}" class="gbtn gbtn-ghost gbtn-xs" title="Descargar PDF">⬇</a>

                        {{-- Emitir: factura y remito --}}
                        @if($p->facturado_count > 0)
                            <span class="gbtn gbtn-ghost gbtn-xs" style="opacity:.55;cursor:default"
                                  title="Este presupuesto ya fue facturado">✓ Facturado</span>
                        @elseif($p->estaAgrupado())
                            {{-- Se agrupó en otro: facturarlo acá duplicaría el trabajo --}}
                            <span class="gbtn gbtn-ghost gbtn-xs" style="opacity:.55;cursor:default"
                                  title="Se agrupó en {{ $p->agrupadoEn?->numeroFormateado() }} — se factura desde ahí">
                                ⤷ Agrupado
                            </span>
                        @else
                            <a href="{{ route('facturas.create', ['presupuesto_id' => $p->id]) }}"
                               class="gbtn gbtn-primary gbtn-xs">⚡ Factura</a>
                        @endif
                        <a href="{{ route('remitos.create', ['presupuesto_id' => $p->id]) }}"
                           class="gbtn gbtn-ghost gbtn-xs">📦 Remito</a>

                        <form action="{{ route('presupuestos.destroy', $p->id) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('¿Eliminar {{ $p->numeroFormateado() }}?')">
                            @csrf @method('DELETE')
                            <button class="gbtn gbtn-danger gbtn-xs">×</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align:center;color:var(--txd);padding:32px">
                        No hay presupuestos.
                        <a href="{{ route('presupuestos.create') }}" style="color:var(--ac)">Crear el primero</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Barra flotante para agrupar los seleccionados en un presupuesto nuevo --}}
<form method="POST" action="{{ route('presupuestos.agrupar') }}" id="form-agrupar"
      style="position:fixed;bottom:0;left:220px;right:0;background:var(--bg-s);
             border-top:1px solid var(--ac);padding:12px 24px;display:none;
             align-items:center;gap:16px;z-index:50">
    @csrf
    <div id="agrupar-info" style="font-size:13px;color:var(--tx);flex:1"></div>
    <div id="agrupar-ids"></div>
    <button type="submit" class="gbtn gbtn-primary gbtn-sm">⊕ Agrupar en uno nuevo</button>
    <button type="button" class="gbtn gbtn-ghost gbtn-sm" onclick="limpiarSeleccion()">Cancelar</button>
</form>

@endsection

@section('scripts')
<script>
// Agrupar presupuestos: se eligen con los checkbox y la barra de abajo postea
// los ids. Tienen que ser del MISMO cliente (juntar clientes distintos en un
// presupuesto no tiene sentido) y al menos dos. El listado no pagina
// (`->get()` en el controller), así que la selección no necesita persistirse.
(function () {
    const barra  = document.getElementById('form-agrupar');
    const info   = document.getElementById('agrupar-info');
    const cajaId = document.getElementById('agrupar-ids');
    const todos  = document.getElementById('chk-todos');

    function seleccionados() {
        return Array.from(document.querySelectorAll('.chk-presu:checked'));
    }

    function pintar() {
        const sel = seleccionados();

        if (sel.length === 0) {
            barra.style.display = 'none';
            if (todos) todos.checked = false;
            return;
        }

        const clientes = [...new Set(sel.map(c => c.dataset.cliente))];
        const numeros  = sel.map(c => c.dataset.numero).join(', ');
        const btn      = barra.querySelector('button[type="submit"]');

        barra.style.display = 'flex';
        cajaId.innerHTML = sel
            .map(c => '<input type="hidden" name="presupuesto_ids[]" value="' + c.value + '">')
            .join('');

        if (clientes.length > 1) {
            info.innerHTML = '<strong style="color:#e53935">Hay presupuestos de ' + clientes.length +
                ' clientes distintos.</strong> <span class="txd">Tienen que ser todos del mismo cliente.</span>';
            btn.disabled = true;
            btn.style.opacity = .45;
        } else if (sel.length < 2) {
            info.innerHTML = '<strong>' + numeros + '</strong> <span class="txd">· elegí al menos dos para agrupar</span>';
            btn.disabled = true;
            btn.style.opacity = .45;
        } else {
            info.innerHTML = '<strong>' + sel.length + ' presupuestos</strong> de ' +
                sel[0].dataset.cnombre + ' <span class="txd">· ' + numeros + '</span>';
            btn.disabled = false;
            btn.style.opacity = 1;
        }
    }

    window.limpiarSeleccion = function () {
        document.querySelectorAll('.chk-presu').forEach(c => { c.checked = false; });
        pintar();
    };

    document.querySelectorAll('.chk-presu').forEach(c => c.addEventListener('change', pintar));

    if (todos) {
        todos.addEventListener('change', function () {
            document.querySelectorAll('.chk-presu').forEach(c => { c.checked = todos.checked; });
            pintar();
        });
    }

    // Deja aire abajo para que la barra no tape la última fila
    barra.addEventListener('transitionend', pintar);
    pintar();
})();
</script>
@endsection

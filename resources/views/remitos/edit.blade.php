@extends('layouts.app')

@section('page-title', 'Editar Remito ' . $remito->numeroFormateado())

@section('topbar-actions')
    <a href="{{ route('remitos.show', $remito->id) }}" class="gbtn gbtn-ghost gbtn-sm">← Volver</a>
@endsection

@section('content')

<form method="POST" action="{{ route('remitos.update', $remito->id) }}" id="form-remito">
@csrf
@method('PUT')

<div style="display:grid;grid-template-columns:1fr 300px;gap:16px;align-items:start">

{{-- ── COLUMNA PRINCIPAL ──────────────────────────────────────────────── --}}
<div>

    {{-- Ítems --}}
    <div class="gcard" style="margin-bottom:16px">
        <div class="gcard-hd">
            <span class="gcard-title">Detalle de ítems</span>
            <button type="button" class="gbtn gbtn-ghost gbtn-sm" id="btn-add-row">+ Agregar ítem</button>
        </div>
        <div class="gcard-bd" style="padding:0">
            <table class="gtable" id="tabla-items">
                <thead>
                    <tr>
                        <th style="width:55%">Descripción</th>
                        <th style="width:18%;text-align:center">Cantidad</th>
                        <th style="width:20%;text-align:center">Unidad</th>
                        <th style="width:7%"></th>
                    </tr>
                </thead>
                <tbody id="items-body">
                    @php
                        // Vuelta de error → old(); si no, ítems guardados del remito.
                        $filasItems = old('items', $remito->items->map(fn($it) => [
                            'descripcion' => $it->descripcion,
                            'cantidad'    => rtrim(rtrim(number_format($it->cantidad, 3, '.', ''), '0'), '.'),
                            'unidad'      => $it->unidad,
                        ])->toArray());
                        if (empty($filasItems)) {
                            $filasItems = [['descripcion' => '', 'cantidad' => 1, 'unidad' => 'unidades']];
                        }
                    @endphp

                    @foreach($filasItems as $i => $item)
                    <tr class="item-row">
                        <td>
                            <input type="text" name="items[{{ $i }}][descripcion]"
                                class="ginput ginput-sm"
                                value="{{ $item['descripcion'] ?? '' }}"
                                placeholder="Descripción del ítem" required>
                        </td>
                        <td>
                            <input type="number" name="items[{{ $i }}][cantidad]"
                                class="ginput ginput-sm"
                                value="{{ $item['cantidad'] ?? 1 }}"
                                min="0.001" step="0.001" required
                                style="text-align:center;width:80px;margin:0 auto;display:block">
                        </td>
                        <td>
                            @include('remitos._select_unidad', ['name' => "items[{$i}][unidad]", 'selected' => $item['unidad'] ?? 'unidades'])
                        </td>
                        <td style="text-align:center">
                            <button type="button" class="gbtn gbtn-danger gbtn-xs btn-remove-row">×</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Observaciones --}}
    <div class="gcard">
        <div class="gcard-hd"><span class="gcard-title">Observaciones</span></div>
        <div class="gcard-bd">
            <div class="gfg" style="margin-bottom:0">
                <textarea name="observaciones" class="gtextarea" rows="3"
                    placeholder="Condiciones de entrega, aclaraciones...">{{ old('observaciones', $remito->observaciones) }}</textarea>
            </div>
        </div>
    </div>

</div>

{{-- ── COLUMNA LATERAL ─────────────────────────────────────────────────── --}}
<div style="display:flex;flex-direction:column;gap:16px">

    {{-- Datos principales --}}
    <div class="gcard">
        <div class="gcard-hd"><span class="gcard-title">Datos del remito</span></div>
        <div class="gcard-bd">

            <div class="gfg">
                <label class="glabel">Cliente *</label>
                @php
                    $clientePresel = old('cliente_id')
                        ? \App\Models\Cliente::find(old('cliente_id'))
                        : $remito->cliente;
                @endphp
                <select name="cliente_id" class="gselect" id="sel-cliente" required style="width:100%">
                    <option value=""></option>
                    @if($clientePresel)
                        <option value="{{ $clientePresel->id }}" selected>{{ $clientePresel->nombre }}</option>
                    @endif
                </select>
                @error('cliente_id')<div class="gerr">{{ $message }}</div>@enderror
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div class="gfg">
                    <label class="glabel">Fecha *</label>
                    <input type="date" name="fecha" class="ginput"
                        value="{{ old('fecha', $remito->fecha->format('Y-m-d')) }}" required>
                    @error('fecha')<div class="gerr">{{ $message }}</div>@enderror
                </div>
                <div class="gfg">
                    <label class="glabel">N° Remito</label>
                    <input type="text" class="ginput" value="{{ $remito->numeroFormateado() }}" disabled
                        style="opacity:.7">
                    <div class="txd" style="font-size:11px;margin-top:3px">No editable</div>
                </div>
            </div>

            {{-- Tipo (no editable: la numeración fiscal ya está asignada) --}}
            <div class="gfg">
                <label class="glabel">Tipo</label>
                @php
                    $tipoLbl = match($remito->tipo) {
                        'oficial'     => 'Oficial (CAI)',
                        'electronico' => 'Electrónico (ARCA)',
                        default       => 'Interno',
                    };
                @endphp
                <div style="border:1px solid var(--bm);border-radius:8px;padding:10px 12px">
                    <div style="font-size:13px;font-weight:600;color:var(--tx)">{{ $tipoLbl }}</div>
                    <div style="font-size:11px;color:var(--txd);margin-top:2px">
                        El tipo y la numeración no se modifican al editar
                    </div>
                </div>
            </div>

            {{-- Vínculos (solo informativos) --}}
            @if($remito->presupuesto)
                <div style="padding:10px 12px;background:#0d0d0d;border:1px solid var(--bm);border-radius:8px;font-size:12px;color:var(--txd);margin-bottom:12px">
                    Basado en presupuesto
                    <a href="{{ route('presupuestos.show', $remito->presupuesto_id) }}"
                       class="mono" style="color:var(--ac);text-decoration:none">
                        {{ $remito->presupuesto->numeroFormateado() }}
                    </a>
                </div>
            @endif

            @if($remito->factura)
                <div style="padding:10px 12px;background:#0d0d0d;border:1px solid var(--bm);border-radius:8px;font-size:12px;color:var(--txd);margin-bottom:12px">
                    Basado en factura
                    <a href="{{ route('facturas.show', $remito->factura_id) }}"
                       class="mono" style="color:var(--ac);text-decoration:none">
                        {{ $remito->factura->numeroFormateado() }}
                    </a>
                </div>
            @endif

        </div>
    </div>

    <button type="submit" class="gbtn gbtn-primary" style="width:100%;justify-content:center;padding:12px">
        Guardar cambios
    </button>

</div>
</div>{{-- /grid --}}

</form>

@endsection

@section('scripts')
<script>
(function () {
    let rowIndex = {{ max(1, count($filasItems)) }};

    const unidades = ['unidades','m²','ml','kg','hojas','pliegos','rollos','metros'];

    function buildSelectUnidad(name, selected) {
        selected = selected || 'unidades';
        let opts = unidades.map(u =>
            `<option value="${u}"${u === selected ? ' selected' : ''}>${u}</option>`
        ).join('');
        return `<select name="${name}" class="gselect gselect-sm" style="width:100%">${opts}</select>`;
    }

    // ── Agregar fila ─────────────────────────────────────────────────────
    $('#btn-add-row').on('click', function () {
        const i = rowIndex++;
        const row = `
        <tr class="item-row">
            <td>
                <input type="text" name="items[${i}][descripcion]"
                    class="ginput ginput-sm" placeholder="Descripción del ítem" required>
            </td>
            <td>
                <input type="number" name="items[${i}][cantidad]"
                    class="ginput ginput-sm" value="1"
                    min="0.001" step="0.001" required
                    style="text-align:center;width:80px;margin:0 auto;display:block">
            </td>
            <td>${buildSelectUnidad('items[' + i + '][unidad]', 'unidades')}</td>
            <td style="text-align:center">
                <button type="button" class="gbtn gbtn-danger gbtn-xs btn-remove-row">×</button>
            </td>
        </tr>`;
        $('#items-body').append(row);
        $('#items-body tr.item-row:last input[type="text"]').focus();
    });

    // ── Eliminar fila ─────────────────────────────────────────────────────
    $(document).on('click', '.btn-remove-row', function () {
        if ($('.item-row').length === 1) return;
        $(this).closest('tr.item-row').remove();
    });

    // ── Init Select2 AJAX para cliente ───────────────────────────────────
    $('#sel-cliente').select2({
        ajax: {
            url: '{{ route("clientes.search") }}',
            dataType: 'json',
            delay: 250,
            data: params => ({ q: params.term }),
            processResults: data => ({ results: data }),
            cache: true,
        },
        minimumInputLength: 1,
        placeholder: 'Escribí el nombre del cliente...',
        allowClear: true,
        width: 'resolve',
    });
})();
</script>

<style>
.ginput-sm { padding: 6px 9px; font-size: 12.5px; border-radius: 6px; }
.gselect-sm { padding: 6px 9px; font-size: 12.5px; border-radius: 6px; }
</style>
@endsection

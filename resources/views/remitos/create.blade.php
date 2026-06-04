@extends('layouts.app')

@section('page-title', 'Nuevo Remito')

@section('topbar-actions')
    <a href="{{ route('remitos.index') }}" class="gbtn gbtn-ghost gbtn-sm">← Volver</a>
@endsection

@section('content')

<form method="POST" action="{{ route('remitos.store') }}" id="form-remito">
@csrf

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
                        // Mapeo de unidades de PresupuestoItem → RemitoItem
                        $unitMap = ['m2' => 'm²', 'ml' => 'ml', 'unidad' => 'unidades'];
                    @endphp

                    {{-- filas precargadas desde presupuesto --}}
                    @if($presupuesto && $presupuesto->items->count())
                        @foreach($presupuesto->items as $i => $item)
                        @php
                            $cantPre  = old('items.'.$i.'.cantidad', $item->medidaTotal());
                            $unitPre  = old('items.'.$i.'.unidad',   $unitMap[$item->unidad] ?? 'unidades');
                        @endphp
                        <tr class="item-row">
                            <td>
                                <input type="text" name="items[{{ $i }}][descripcion]"
                                    class="ginput ginput-sm"
                                    value="{{ old('items.'.$i.'.descripcion', $item->descripcion) }}"
                                    placeholder="Descripción del ítem" required>
                            </td>
                            <td>
                                <input type="number" name="items[{{ $i }}][cantidad]"
                                    class="ginput ginput-sm"
                                    value="{{ $cantPre }}"
                                    min="0.001" step="0.001" required
                                    style="text-align:center;width:80px;margin:0 auto;display:block">
                            </td>
                            <td>
                                @include('remitos._select_unidad', ['name' => "items[{$i}][unidad]", 'selected' => $unitPre])
                            </td>
                            <td style="text-align:center">
                                <button type="button" class="gbtn gbtn-danger gbtn-xs btn-remove-row">×</button>
                            </td>
                        </tr>
                        @endforeach
                    @elseif($factura && $factura->items->count())
                        @foreach($factura->items as $i => $item)
                        @php
                            // FacturaItem ya tiene cantidad directa (no medidaTotal)
                            $cantFac = old('items.'.$i.'.cantidad', $item->cantidad);
                            $unitFac = old('items.'.$i.'.unidad', 'unidades');
                        @endphp
                        <tr class="item-row">
                            <td>
                                <input type="text" name="items[{{ $i }}][descripcion]"
                                    class="ginput ginput-sm"
                                    value="{{ old('items.'.$i.'.descripcion', $item->descripcion) }}"
                                    placeholder="Descripción del ítem" required>
                            </td>
                            <td>
                                <input type="number" name="items[{{ $i }}][cantidad]"
                                    class="ginput ginput-sm"
                                    value="{{ $cantFac }}"
                                    min="0.001" step="0.001" required
                                    style="text-align:center;width:80px;margin:0 auto;display:block">
                            </td>
                            <td>
                                @include('remitos._select_unidad', ['name' => "items[{$i}][unidad]", 'selected' => $unitFac])
                            </td>
                            <td style="text-align:center">
                                <button type="button" class="gbtn gbtn-danger gbtn-xs btn-remove-row">×</button>
                            </td>
                        </tr>
                        @endforeach
                    @else
                        {{-- fila vacía inicial --}}
                        <tr class="item-row">
                            <td>
                                <input type="text" name="items[0][descripcion]"
                                    class="ginput ginput-sm"
                                    value="{{ old('items.0.descripcion') }}"
                                    placeholder="Descripción del ítem" required>
                            </td>
                            <td>
                                <input type="number" name="items[0][cantidad]"
                                    class="ginput ginput-sm"
                                    value="{{ old('items.0.cantidad', 1) }}"
                                    min="0.001" step="0.001" required
                                    style="text-align:center;width:80px;margin:0 auto;display:block">
                            </td>
                            <td>
                                @include('remitos._select_unidad', ['name' => 'items[0][unidad]', 'selected' => 'unidades'])
                            </td>
                            <td style="text-align:center">
                                <button type="button" class="gbtn gbtn-danger gbtn-xs btn-remove-row">×</button>
                            </td>
                        </tr>
                    @endif
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
                    placeholder="Condiciones de entrega, aclaraciones...">{{ old('observaciones', $presupuesto?->observaciones ?? $factura?->observaciones) }}</textarea>
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
                    $clientePresel = null;
                    if (old('cliente_id')) {
                        $clientePresel = \App\Models\Cliente::find(old('cliente_id'));
                    } elseif ($presupuesto?->cliente) {
                        $clientePresel = $presupuesto->cliente;
                    } elseif ($factura?->cliente) {
                        $clientePresel = $factura->cliente;
                    }
                @endphp
                <select name="cliente_id" class="gselect" id="sel-cliente" required style="width:100%">
                    <option value=""></option>
                    @if($clientePresel)
                        <option value="{{ $clientePresel->id }}" selected>{{ $clientePresel->nombre }}</option>
                    @endif
                </select>
                @error('cliente_id')<div class="gerr">{{ $message }}</div>@enderror
            </div>

            <div class="gfg">
                <label class="glabel">Fecha *</label>
                <input type="date" name="fecha" class="ginput"
                    value="{{ old('fecha', now()->format('Y-m-d')) }}" required>
                @error('fecha')<div class="gerr">{{ $message }}</div>@enderror
            </div>

            {{-- Tipo de remito --}}
            @if($puedeOficial)
            <div class="gfg">
                <label class="glabel">Tipo *</label>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">

                    <label id="lbl-interno" style="border:1px solid var(--bm);border-radius:8px;padding:10px 12px;cursor:pointer;transition:all .12s;{{ old('tipo','interno') === 'interno' ? 'border-color:var(--ac);background:rgba(230,80,42,.08)' : '' }}">
                        <input type="radio" name="tipo" value="interno" {{ old('tipo','interno') === 'interno' ? 'checked' : '' }} style="display:none" class="tipo-radio">
                        <div style="font-size:13px;font-weight:600;color:var(--tx)">Interno</div>
                        <div style="font-size:11px;color:var(--txd);margin-top:2px">Sin validación fiscal</div>
                    </label>

                    <label id="lbl-oficial" style="border:1px solid var(--bm);border-radius:8px;padding:10px 12px;cursor:pointer;transition:all .12s;{{ old('tipo','interno') === 'oficial' ? 'border-color:var(--ac);background:rgba(230,80,42,.08)' : '' }}">
                        <input type="radio" name="tipo" value="oficial" {{ old('tipo','interno') === 'oficial' ? 'checked' : '' }} style="display:none" class="tipo-radio">
                        <div style="font-size:13px;font-weight:600;color:var(--tx)">Oficial (CAI)</div>
                        <div style="font-size:11px;color:var(--txd);margin-top:2px">
                            @if($caiVigente)
                                PV {{ $caiVigente->punto_venta }} · nro {{ $caiVigente->ultimo_numero + 1 }}
                            @else
                                <span style="color:var(--ac)">Sin CAI vigente</span>
                            @endif
                        </div>
                    </label>

                </div>
                @error('tipo')<div class="gerr">{{ $message }}</div>@enderror
            </div>
            @else
            {{-- Producción solo puede hacer internos --}}
            <input type="hidden" name="tipo" value="interno">
            @endif

            {{-- Vínculos opcionales --}}
            @if($presupuesto)
                <input type="hidden" name="presupuesto_id" value="{{ $presupuesto->id }}">
                <div style="padding:10px 12px;background:#0d0d0d;border:1px solid var(--bm);border-radius:8px;font-size:12px;color:var(--txd);margin-bottom:12px">
                    Basado en presupuesto
                    <a href="{{ route('presupuestos.show', $presupuesto->id) }}"
                       class="mono" style="color:var(--ac);text-decoration:none">
                        {{ $presupuesto->numeroFormateado() }}
                    </a>
                </div>
            @endif

            @if($factura)
                <input type="hidden" name="factura_id" value="{{ $factura->id }}">
                <div style="padding:10px 12px;background:#0d0d0d;border:1px solid var(--bm);border-radius:8px;font-size:12px;color:var(--txd);margin-bottom:12px">
                    Basado en factura
                    <a href="{{ route('facturas.show', $factura->id) }}"
                       class="mono" style="color:var(--ac);text-decoration:none">
                        {{ $factura->numeroFormateado() }}
                    </a>
                </div>
            @endif

        </div>
    </div>

    <button type="submit" class="gbtn gbtn-primary" style="width:100%;justify-content:center;padding:12px">
        Crear remito
    </button>

</div>
</div>{{-- /grid --}}

</form>

@endsection

@section('scripts')
<script>
(function () {
    let rowIndex = {{ max(1, $presupuesto?->items->count() ?? $factura?->items->count() ?? 0) }};

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

    // ── Selector tipo remito ─────────────────────────────────────────────
    $(document).on('change', '.tipo-radio', function () {
        $('.tipo-radio').each(function () {
            const lbl = $(this).closest('label');
            if ($(this).is(':checked')) {
                lbl.css({ 'border-color': 'var(--ac)', 'background': 'rgba(230,80,42,.08)' });
            } else {
                lbl.css({ 'border-color': 'var(--bm)', 'background': 'transparent' });
            }
        });
    });
})();
</script>

<style>
.ginput-sm { padding: 6px 9px; font-size: 12.5px; border-radius: 6px; }
.gselect-sm { padding: 6px 9px; font-size: 12.5px; border-radius: 6px; }
</style>
@endsection

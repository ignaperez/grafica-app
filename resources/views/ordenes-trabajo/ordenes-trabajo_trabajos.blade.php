@extends('layouts.app')

@section('page-title', 'Orden #' . $orden->id . ' — Trabajos')

@section('topbar-actions')
    <a href="{{ route('ordenes-trabajo.index') }}" class="gbtn gbtn-ghost gbtn-sm">← Volver</a>
@endsection

@section('content')

<div style="display:grid;grid-template-columns:300px 1fr;gap:20px;align-items:start">

    {{-- DATOS DE LA ORDEN --}}
    <div class="gcard">
        <div class="gcard-hd"><span class="gcard-title">Orden #{{ $orden->id }}</span></div>
        <div class="gcard-bd" style="padding:18px 20px">
            <div style="margin-bottom:14px">
                <div class="glabel">Cliente</div>
                <div style="font-weight:500;color:var(--tx)">{{ $orden->cliente->nombre ?? '—' }}</div>
            </div>
            <div style="margin-bottom:14px">
                <div class="glabel">Fecha</div>
                <div class="mono" style="color:var(--txd);font-size:13px">
                    {{ \Carbon\Carbon::parse($orden->fecha_recibido)->format('d/m/Y') }}
                </div>
            </div>
            @if($orden->observaciones)
            <div style="margin-bottom:14px">
                <div class="glabel">Observaciones</div>
                <div style="font-size:13px;color:var(--txd)">{{ $orden->observaciones }}</div>
            </div>
            @endif
            <div>
                <div class="glabel">Estado</div>
                <form method="POST" action="{{ route('ordenes-trabajo.estado', $orden->id) }}" style="display:flex;gap:8px;align-items:center;margin-top:6px">
                    @csrf @method('PATCH')
                    <select name="estado" class="gselect" style="flex:1;font-size:13px;padding:6px 10px">
                        @foreach(['borrador'=>'Borrador','en_produccion'=>'En producción','lista'=>'Lista','entregada'=>'Entregada','cancelada'=>'Cancelada'] as $val => $label)
                            <option value="{{ $val }}" {{ $orden->estado === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="gbtn gbtn-primary gbtn-sm">OK</button>
                </form>
            </div>

            <div style="border-top:1px solid var(--b);margin-top:18px;padding-top:18px">
                <a href="{{ route('ordenes-trabajo.edit', $orden->id) }}" class="gbtn gbtn-ghost gbtn-sm" style="width:100%;justify-content:center">
                    Editar datos de la orden
                </a>
            </div>
        </div>
    </div>

    {{-- TRABAJOS --}}
    <div>

        {{-- Trabajos existentes --}}
        @if($orden->trabajos->count() > 0)
        <div class="gcard" style="margin-bottom:16px">
            <div class="gcard-hd">
                <span class="gcard-title">Trabajos cargados ({{ $orden->trabajos->count() }})</span>
            </div>
            <table class="gtable">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Descripción</th>
                        <th>Medidas</th>
                        <th>Cant.</th>
                        <th>m²</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orden->trabajos as $trabajo)
                    <tr>
                        <td>
                            <div style="font-weight:500;font-size:13px">{{ $trabajo->producto->nombre ?? '—' }}</div>
                            <div style="font-size:11px;color:#555">{{ $trabajo->tipo }}</div>
                        </td>
                        <td style="font-size:13px;color:var(--txd)">{{ $trabajo->descripcion ?? '—' }}</td>
                        <td class="mono" style="font-size:12px;color:var(--txd)">
                            @if($trabajo->ancho && $trabajo->alto)
                                {{ $trabajo->ancho }}m × {{ $trabajo->alto }}m
                            @else
                                —
                            @endif
                        </td>
                        <td class="mono" style="font-size:13px">{{ $trabajo->cantidad }}</td>
                        <td class="mono" style="font-size:12px;color:var(--txd)">
                            @if($trabajo->ancho && $trabajo->alto)
                                {{ number_format($trabajo->ancho * $trabajo->alto * $trabajo->cantidad, 2) }}
                            @else
                                —
                            @endif
                        </td>
                        <td><span class="badge-estado be-{{ $trabajo->estado }}">{{ $trabajo->estado }}</span></td>
                        <td>
                            <form method="POST" action="{{ route('trabajos.destroy', $trabajo->id) }}" onsubmit="return confirm('¿Eliminar este trabajo?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="gbtn gbtn-danger gbtn-xs">✕</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        {{-- Formulario nuevo trabajo --}}
        <div class="gcard" id="card-nuevo-trabajo">
            <div class="gcard-hd">
                <span class="gcard-title">Agregar trabajo</span>
            </div>
            <div class="gcard-bd">
                <form method="POST" action="{{ route('trabajos.store') }}" id="form-trabajo">
                    @csrf
                    <input type="hidden" name="orden_trabajo_id" value="{{ $orden->id }}">

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">

                        <div class="gfg" style="grid-column:1/-1">
                            <label class="glabel">Producto *</label>
                            <select name="producto_id" id="producto_sel" class="gselect" required style="width:100%">
                                <option value="">Seleccionar producto...</option>
                                @foreach($productos as $p)
                                    <option value="{{ $p->id }}" data-tipo="{{ $p->tipo }}" data-precio="{{ $p->precio }}">
                                        {{ $p->nombre }} — {{ $p->tipo }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="gfg" style="grid-column:1/-1">
                            <label class="glabel">Descripción / detalle</label>
                            <input type="text" name="descripcion" class="ginput" placeholder="Ej: con sangría, fondo blanco, etc.">
                        </div>

                        <div class="gfg">
                            <label class="glabel">Ancho (m) *</label>
                            <input type="number" name="ancho" id="ancho" class="ginput" step="0.01" min="0.01" placeholder="0.00" required>
                        </div>

                        <div class="gfg">
                            <label class="glabel">Alto (m) *</label>
                            <input type="number" name="alto" id="alto" class="ginput" step="0.01" min="0.01" placeholder="0.00" required>
                        </div>

                        <div class="gfg">
                            <label class="glabel">Cantidad *</label>
                            <input type="number" name="cantidad" id="cantidad" class="ginput" min="1" value="1" required>
                        </div>

                        <div class="gfg">
                            <label class="glabel">m² total</label>
                            <input type="text" id="metros2" class="ginput" readonly placeholder="—" style="color:var(--txm)">
                        </div>

                        <div class="gfg" style="grid-column:1/-1">
                            <label class="glabel">Fecha entrega</label>
                            <input type="date" name="fecha_entrega" class="ginput">
                        </div>

                    </div>

                    <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:4px">
                        <button type="submit" class="gbtn gbtn-primary">+ Guardar trabajo</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

@endsection

@section('scripts')
<script>
$(function(){
    // Calcular m²
    function calcM2() {
        var a = parseFloat($('#ancho').val()) || 0;
        var h = parseFloat($('#alto').val()) || 0;
        var c = parseInt($('#cantidad').val()) || 0;
        if (a > 0 && h > 0 && c > 0) {
            $('#metros2').val((a * h * c).toFixed(2) + ' m²');
        } else {
            $('#metros2').val('');
        }
    }
    $('#ancho, #alto, #cantidad').on('input', calcM2);
});
</script>
@endsection

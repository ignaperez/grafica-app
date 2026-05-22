@extends('layouts.app')

@section('page-title', 'Trabajos sin asignar')

@section('topbar-actions')
    <a href="{{ route('trabajos-libres.create') }}" class="gbtn gbtn-primary gbtn-sm">+ Cargar trabajo(s)</a>
@endsection

@section('content')

{{-- Filtros --}}
<form method="GET" style="display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap">
    <select name="cliente_id" class="gselect" style="width:220px">
        <option value="">Todos los clientes</option>
        @foreach($clientes as $c)
            <option value="{{ $c->id }}" {{ request('cliente_id') == $c->id ? 'selected' : '' }}>
                {{ $c->nombre }}
            </option>
        @endforeach
    </select>
    <select name="estado" class="gselect" style="width:180px">
        <option value="">Todos los estados</option>
        <option value="pendiente"     {{ request('estado') === 'pendiente'     ? 'selected' : '' }}>Pendiente</option>
        <option value="en_produccion" {{ request('estado') === 'en_produccion' ? 'selected' : '' }}>En producción</option>
        <option value="terminado"     {{ request('estado') === 'terminado'     ? 'selected' : '' }}>Terminado</option>
    </select>
    <button class="gbtn gbtn-ghost gbtn-sm">Filtrar</button>
    <a href="{{ route('trabajos-libres.index') }}" class="gbtn gbtn-ghost gbtn-sm">Limpiar</a>
</form>

{{-- Panel asignar (empieza oculto, JS lo muestra) --}}
<div id="panel-asignar" style="display:none;margin-bottom:20px">
    <div class="gcard">
        <div class="gcard-hd">
            <span class="gcard-title">Asignar a orden de trabajo</span>
            {{-- Cliente detectado --}}
            <span id="cliente-detectado" style="font-size:12px;color:var(--ac);font-family:var(--mono)"></span>
        </div>
        <div class="gcard-bd">

            {{-- Alerta mezcla de clientes --}}
            <div id="alerta-mezcla" style="display:none;padding:10px 14px;border-radius:7px;
                 background:#1f0a0a;border:1px solid #3d1a1a;color:#e05555;font-size:13px;margin-bottom:16px">
                ✕ &nbsp;Seleccionaste trabajos de <strong>distintos clientes</strong>.
                Una orden de trabajo solo puede tener trabajos del mismo cliente.
            </div>

            <form id="form-asignar" action="{{ route('trabajos-libres.asignar-orden') }}" method="POST">
                @csrf

                {{-- checkboxes seleccionados se agregan acá por JS --}}
                <div id="inputs-seleccionados"></div>

                <div class="row g-3">
                    {{-- Acción --}}
                    <div class="col-md-3">
                        <div class="gfg">
                            <label class="glabel">¿Qué hacer?</label>
                            <div style="display:flex;flex-direction:column;gap:8px;margin-top:4px">
                                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:var(--tx)">
                                    <input type="radio" name="accion" value="nueva" id="accion_nueva" checked>
                                    Crear nueva orden
                                </label>
                                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:var(--tx)">
                                    <input type="radio" name="accion" value="existente" id="accion_existente">
                                    Agregar a orden existente
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Bloque nueva orden --}}
                    <div class="col-md-5" id="bloque-nueva">
                        <div class="gfg">
                            <label class="glabel">Cliente de la orden</label>
                            <div style="padding:9px 12px;background:#0a0a0a;border:1px solid var(--bm);
                                        border-radius:7px;font-size:13.5px;color:var(--ac)"
                                 id="label-cliente-nueva">
                                — seleccioná trabajos primero —
                            </div>
                        </div>
                        <div class="gfg mb-0">
                            <label class="glabel">Observaciones</label>
                            <textarea name="observaciones" class="gtextarea" rows="2"></textarea>
                        </div>
                    </div>

                    {{-- Bloque orden existente --}}
                    <div class="col-md-5" id="bloque-existente" style="display:none">
                        <div class="gfg">
                            <label class="glabel">Número de orden *</label>
                            <input type="number" name="orden_id" class="ginput" min="1"
                                   placeholder="ID de la orden de trabajo">
                        </div>
                        <div style="font-size:12px;color:var(--txd);margin-top:-10px">
                            La orden debe pertenecer al mismo cliente que los trabajos seleccionados.
                        </div>
                    </div>

                    <div class="col-12">
                        <button type="submit" id="btn-confirmar" class="gbtn gbtn-primary" disabled
                                onclick="return confirm('¿Confirmar asignación?')">
                            Confirmar asignación
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>
</div>

{{-- Tabla --}}
<div class="gcard">
    <div class="gcard-bd" style="padding:0">
        <table class="gtable">
            <thead>
                <tr>
                    <th style="width:36px">
                        <input type="checkbox" id="sel-todos" style="cursor:pointer">
                    </th>
                    <th>#</th>
                    <th>Cliente</th>
                    <th>Tipo trabajo</th>
                    <th>Material</th>
                    <th>Máquina</th>
                    <th>Descripción</th>
                    <th>Medidas</th>
                    <th>m²</th>
                    <th>Cant.</th>
                    <th>F. entrega</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($trabajos as $t)
                    <tr>
                        <td>
                            <input type="checkbox"
                                   class="sel-trabajo"
                                   value="{{ $t->id }}"
                                   data-cliente-id="{{ $t->cliente_id }}"
                                   data-cliente-nombre="{{ $t->cliente->nombre ?? '' }}"
                                   style="cursor:pointer">
                        </td>
                        <td><span class="mono txd">{{ $t->id }}</span></td>
                        <td>{{ $t->cliente->nombre ?? '-' }}</td>
                        <td>{{ $t->tipoTrabajo->nombre ?? '-' }}</td>
                        <td>{{ $t->material->nombre ?? '-' }}</td>
                        <td>{{ $t->maquina->nombre ?? '-' }}</td>
                        <td>{{ $t->descripcion ?? '-' }}</td>
                        <td>
                            @if($t->ancho && $t->alto)
                                <span class="mono">{{ $t->ancho }}×{{ $t->alto }}</span>
                            @else
                                <span class="txd">-</span>
                            @endif
                        </td>
                        <td>
                            @if($t->ancho && $t->alto)
                                {{ number_format($t->ancho * $t->alto * $t->cantidad, 2) }}
                            @else
                                <span class="txd">-</span>
                            @endif
                        </td>
                        <td>{{ $t->cantidad }}</td>
                        <td>{{ $t->fecha_entrega ? \Carbon\Carbon::parse($t->fecha_entrega)->format('d/m/Y') : '-' }}</td>
                        <td>
                            <span class="badge-estado be-{{ $t->estado }}">
                                {{ ucfirst(str_replace('_', ' ', $t->estado)) }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('trabajos.edit', $t->id) }}" class="gbtn gbtn-ghost gbtn-xs">Editar</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="13" style="text-align:center;padding:32px;color:var(--txd)">
                            No hay trabajos sin asignar.
                            <a href="{{ route('trabajos-libres.create') }}" style="color:var(--ac)">Cargar el primero</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{ $trabajos->links() }}

@endsection

@section('scripts')
<script>
(function () {

    // ── Detectar selección ────────────────────────────────────────
    function actualizarPanel() {
        const checks   = [...document.querySelectorAll('.sel-trabajo:checked')];
        const panel    = document.getElementById('panel-asignar');
        const alerta   = document.getElementById('alerta-mezcla');
        const btnConf  = document.getElementById('btn-confirmar');
        const labelCli = document.getElementById('label-cliente-nueva');
        const infoCli  = document.getElementById('cliente-detectado');

        if (checks.length === 0) {
            panel.style.display = 'none';
            return;
        }

        panel.style.display = '';

        // Clientes únicos en la selección
        const clientesIds    = [...new Set(checks.map(c => c.dataset.clienteId))];
        const mezclado       = clientesIds.length > 1;
        const nombreCliente  = mezclado ? '' : checks[0].dataset.clienteNombre;

        alerta.style.display  = mezclado ? '' : 'none';
        btnConf.disabled      = mezclado;
        infoCli.textContent   = mezclado ? '⚠ Múltiples clientes' : nombreCliente;
        labelCli.textContent  = mezclado ? '⚠ Clientes mezclados' : nombreCliente;
        labelCli.style.color  = mezclado ? '#e05555' : 'var(--ac)';

        // Sincronizar inputs ocultos con los ids seleccionados
        const cont = document.getElementById('inputs-seleccionados');
        cont.innerHTML = checks
            .map(c => `<input type="hidden" name="trabajo_ids[]" value="${c.value}">`)
            .join('');
    }

    // Checkbox individual
    document.querySelectorAll('.sel-trabajo').forEach(cb => {
        cb.addEventListener('change', actualizarPanel);
    });

    // Seleccionar / deseleccionar todos
    document.getElementById('sel-todos')?.addEventListener('change', function () {
        document.querySelectorAll('.sel-trabajo').forEach(cb => {
            cb.checked = this.checked;
        });
        actualizarPanel();
    });

    // ── Alternar bloque nueva / existente ────────────────────────
    document.querySelectorAll('input[name="accion"]').forEach(r => {
        r.addEventListener('change', function () {
            document.getElementById('bloque-nueva').style.display     = this.value === 'nueva'     ? '' : 'none';
            document.getElementById('bloque-existente').style.display = this.value === 'existente' ? '' : 'none';
        });
    });

})();
</script>
@endsection

@extends('layouts.app')

@section('page-title', 'Editar cliente')

@section('topbar-actions')
    <a href="{{ route('clientes.show', $cliente->id) }}" class="gbtn gbtn-ghost gbtn-sm">← Ver cliente</a>
@endsection

@section('content')
<div style="max-width:680px;">

    <form action="{{ route('clientes.update', $cliente->id) }}" method="POST" id="form-cliente">
        @csrf
        @method('PUT')

        <div class="gcard">
            <div class="gcard-hd">
                <span class="gcard-title">Datos del cliente</span>
                <span class="txd" style="font-size:11px;">ID {{ $cliente->id }}</span>
            </div>
            <div class="gcard-bd" style="display:flex;flex-direction:column;gap:20px;">

                {{-- Nombre --}}
                <div class="gfg">
                    <label class="glabel">Nombre / Razón social *</label>
                    <input type="text" name="nombre" id="nombre" class="ginput"
                           value="{{ old('nombre', $cliente->nombre) }}" required autocomplete="off">
                    @error('nombre')<div class="gerr">{{ $message }}</div>@enderror
                </div>

                {{-- CUIT --}}
                <div class="gfg">
                    <label class="glabel">CUIT
                        <span class="txd" style="font-weight:400;letter-spacing:0;">(opcional — necesario para emitir facturas con CAE)</span>
                    </label>
                    <div style="display:flex;gap:10px;align-items:flex-start;">
                        <div style="flex:1;">
                            <input type="text" name="cuit" id="cuit" class="ginput"
                                   value="{{ old('cuit', $cliente->cuit) }}"
                                   placeholder="Ej: 20-12345678-9 ó 20123456789"
                                   maxlength="20" autocomplete="off">
                        </div>
                        <button type="button" id="btn-consultar" class="gbtn gbtn-ghost gbtn-sm"
                                style="white-space:nowrap;margin-top:1px;"
                                {{ strlen(preg_replace('/\D/', '', $cliente->cuit ?? '')) === 11 ? '' : 'disabled' }}>
                            Consultar ARCA ↗
                        </button>
                    </div>
                    <div id="cuit-status" style="margin-top:6px;font-size:11.5px;"></div>
                    @error('cuit')<div class="gerr">{{ $message }}</div>@enderror
                </div>

                {{-- Condición IVA --}}
                <div class="gfg">
                    <label class="glabel">Condición IVA</label>
                    <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:4px;">
                        @foreach([
                            'consumidor_final'      => 'Consumidor Final',
                            'monotributo'           => 'Monotributo',
                            'responsable_inscripto' => 'Resp. Inscripto',
                            'exento'                => 'Exento',
                        ] as $val => $label)
                        <label style="display:flex;align-items:center;gap:6px;cursor:pointer;
                                      padding:6px 12px;border:1px solid var(--bm);border-radius:6px;
                                      font-size:12px;color:var(--txm);transition:all .15s;"
                               class="iva-opt">
                            <input type="radio" name="condicion_iva" value="{{ $val }}"
                                   {{ old('condicion_iva', $cliente->condicion_iva ?? 'consumidor_final') === $val ? 'checked' : '' }}
                                   style="accent-color:var(--ac);">
                            {{ $label }}
                        </label>
                        @endforeach
                    </div>
                    @error('condicion_iva')<div class="gerr">{{ $message }}</div>@enderror
                </div>

                {{-- Teléfono / Email --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                    <div class="gfg">
                        <label class="glabel">Teléfono</label>
                        <input type="text" name="telefono" class="ginput" value="{{ old('telefono', $cliente->telefono) }}">
                        @error('telefono')<div class="gerr">{{ $message }}</div>@enderror
                    </div>
                    <div class="gfg">
                        <label class="glabel">Email</label>
                        <input type="email" name="email" class="ginput" value="{{ old('email', $cliente->email) }}">
                        @error('email')<div class="gerr">{{ $message }}</div>@enderror
                    </div>
                </div>

                {{-- Dirección --}}
                <div class="gfg">
                    <label class="glabel">Dirección</label>
                    <textarea name="direccion" id="direccion" class="gtextarea" rows="2">{{ old('direccion', $cliente->direccion) }}</textarea>
                    @error('direccion')<div class="gerr">{{ $message }}</div>@enderror
                </div>

                {{-- Lista de precios --}}
                <div class="gfg">
                    <label class="glabel">Lista de precios *</label>
                    <select name="lista_precio_id" id="lista_precio_id" class="gselect" required>
                        <option value="">Seleccioná una lista…</option>
                        @foreach($listas as $lista)
                            <option value="{{ $lista->id }}"
                                {{ old('lista_precio_id', $cliente->lista_precio_id) == $lista->id ? 'selected' : '' }}>
                                {{ $lista->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('lista_precio_id')<div class="gerr">{{ $message }}</div>@enderror
                </div>

            </div>
        </div>

    </form>{{-- ← cierra el form de edición ANTES del botón de eliminar --}}

    <div style="display:flex;gap:10px;margin-top:20px;justify-content:space-between;align-items:center;">
        <div style="display:flex;gap:10px;">
            {{-- form="form-cliente" asocia este botón al form de edición aunque esté fuera --}}
            <button type="submit" form="form-cliente" class="gbtn gbtn-primary">Guardar cambios</button>
            <a href="{{ route('clientes.show', $cliente->id) }}" class="gbtn gbtn-ghost">Cancelar</a>
        </div>
        <form action="{{ route('clientes.destroy', $cliente->id) }}" method="POST"
              onsubmit="return confirm('¿Eliminar este cliente? Esta acción no se puede deshacer.');">
            @csrf @method('DELETE')
            <button type="submit" class="gbtn gbtn-danger gbtn-sm">Eliminar cliente</button>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<style>
    .iva-opt:has(input:checked) {
        border-color: var(--ac);
        color: var(--tx);
        background: rgba(230,80,42,.08);
    }
    #cuit-status .ok      { color: #4caf50; }
    #cuit-status .err     { color: #ef5350; }
    #cuit-status .loading { color: var(--txd); }
</style>
<script>
$(function () {
    // Select2 lista de precios
    $('#lista_precio_id').select2({ width: 'resolve', placeholder: 'Seleccioná una lista…' });

    // Habilitar botón cuando el CUIT tenga 11 dígitos
    $('#cuit').on('input', function () {
        const digits = $(this).val().replace(/\D/g, '');
        $('#btn-consultar').prop('disabled', digits.length !== 11);
    });

    // Consultar ARCA
    $('#btn-consultar').on('click', function () {
        const cuit = $('#cuit').val().replace(/\D/g, '');
        const $status = $('#cuit-status');

        $status.html('<span class="loading">⏳ Consultando padrón ARCA…</span>');
        $(this).prop('disabled', true).text('Consultando…');

        $.get('{{ route('clientes.consultar-cuit') }}', { cuit })
            .done(function (data) {
                if (data.nombre)    $('#nombre').val(data.nombre);
                if (data.direccion) $('#direccion').val(data.direccion);

                if (data.condicion_iva) {
                    $('input[name="condicion_iva"][value="' + data.condicion_iva + '"]').prop('checked', true);
                }

                const estadoBadge = data.estado === 'ACTIVO'
                    ? '<span style="color:#4caf50;">● ACTIVO</span>'
                    : '<span style="color:#ef5350;">● ' + data.estado + '</span>';

                const ivaNote = data.condicion_iva
                    ? ''
                    : ' &nbsp;·&nbsp; <span style="color:#ff9800;">⚠ Condición IVA no disponible en el padrón — seleccionala manualmente</span>';

                $status.html('<span class="ok">✓ Datos actualizados desde ARCA — ' + estadoBadge + ivaNote + '</span>');
            })
            .fail(function (xhr) {
                const msg = xhr.responseJSON?.error ?? 'Error de conexión con ARCA.';
                $status.html('<span class="err">✗ ' + msg + '</span>');
            })
            .always(function () {
                const digits = $('#cuit').val().replace(/\D/g, '');
                $('#btn-consultar').prop('disabled', digits.length !== 11).text('Consultar ARCA ↗');
            });
    });
});
</script>
@endsection

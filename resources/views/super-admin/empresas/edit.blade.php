@extends('super-admin.layout')

@section('title', 'Editar — ' . $tenant->nombre)

@section('content')

<div class="sa-hd">
    <div>
        <h1>Editar empresa</h1>
        <div class="sub">{{ $tenant->nombre }}</div>
    </div>
    <div class="sa-hd-actions">
        <a href="{{ route('super-admin.empresas.show', $tenant->id) }}" class="btn btn-ghost">← Volver</a>
    </div>
</div>

<form method="POST" action="{{ route('super-admin.empresas.update', $tenant->id) }}">
    @csrf @method('PUT')

    @include('super-admin.partials.cuit-lookup', [
        'conSlug'    => false,
        'cuitActual' => $tenant->cuit,
    ])

    <div class="sa-card">
        <div class="sa-card-hd"><span class="sa-card-title">Datos generales</span></div>
        <div class="sa-card-bd">
            <div class="grid-2">
                <div class="fg">
                    <label class="glb">Razón Social *</label>
                    <input class="gin" type="text" name="nombre" id="f-nombre" value="{{ old('nombre', $tenant->nombre) }}" required>
                    @error('nombre') <div class="err-msg">{{ $message }}</div> @enderror
                </div>
                <div class="fg">
                    <label class="glb">Slug (subdominio)</label>
                    <input class="gin" type="text" value="{{ $tenant->id }}" disabled style="opacity:.5">
                    <div class="hint">El slug no se puede cambiar después de creado.</div>
                </div>
            </div>

            <div class="fg">
                <label class="glb">Nombre de Fantasía</label>
                <input class="gin" type="text" name="nombre_fantasia" value="{{ old('nombre_fantasia', $tenant->nombre_fantasia) }}" placeholder="(aparece en facturas si se completa)">
                <div class="hint">Si se completa, se usa en facturas en lugar de la razón social.</div>
            </div>
            <div class="grid-2">
                <div class="fg">
                    <label class="glb">CUIT</label>
                    <input class="gin" type="text" name="cuit" id="f-cuit" value="{{ old('cuit', $tenant->cuit) }}" placeholder="20-12345678-9">
                </div>
                <div class="fg">
                    <label class="glb">Condición IVA *</label>
                    @php $condIvaActual = $tenant->run(fn() => \App\Models\Configuracion::get('empresa_condicion_iva', 'monotributo')); @endphp
                    <select class="gin" name="condicion_iva">
                        <option value="monotributo" {{ old('condicion_iva', $condIvaActual) === 'monotributo' ? 'selected' : '' }}>Monotributista</option>
                        <option value="responsable_inscripto" {{ old('condicion_iva', $condIvaActual) === 'responsable_inscripto' ? 'selected' : '' }}>IVA Responsable Inscripto</option>
                        <option value="exento" {{ old('condicion_iva', $condIvaActual) === 'exento' ? 'selected' : '' }}>IVA Exento</option>
                    </select>
                    <div class="hint">Define si emite Factura C (mono) o A/B (RI).</div>
                </div>
            </div>

            <div class="grid-2">
                <div class="fg">
                    <label class="glb">Email</label>
                    <input class="gin" type="email" name="email" value="{{ old('email', $tenant->email) }}">
                </div>
                <div class="fg"></div>
            </div>
            <div class="grid-2">
                <div class="fg">
                    <label class="glb">Teléfono</label>
                    <input class="gin" type="text" name="telefono" value="{{ old('telefono', $tenant->telefono) }}">
                </div>
                <div class="fg">
                    <label class="glb">Dirección</label>
                    <input class="gin" type="text" name="direccion" id="f-direccion" value="{{ old('direccion', $tenant->direccion) }}">
                </div>
            </div>
        </div>
    </div>

    <div class="sa-card">
        <div class="sa-card-hd"><span class="sa-card-title">Configuración ARCA</span></div>
        <div class="sa-card-bd">
            <div class="grid-2">
                <div class="fg">
                    <label class="glb">CUIT fiscal ARCA</label>
                    <input class="gin" type="text" name="arca_cuit" id="f-arca-cuit" value="{{ old('arca_cuit', $tenant->arca_cuit) }}" placeholder="23252997679">
                </div>
                <div class="fg">
                    <label class="glb">Punto de venta</label>
                    <input class="gin" type="number" name="arca_punto_venta" value="{{ old('arca_punto_venta', $tenant->arca_punto_venta) }}" min="1">
                </div>
            </div>
            <div class="fg">
                <label class="glb">Entorno</label>
                <select class="gin" name="arca_production">
                    <option value="0" {{ !$tenant->arca_production ? 'selected' : '' }}>Homologación (testing)</option>
                    <option value="1" {{ $tenant->arca_production ? 'selected' : '' }}>Producción (real)</option>
                </select>
            </div>
        </div>
    </div>

    <div style="display:flex;gap:12px;justify-content:flex-end">
        <a href="{{ route('super-admin.empresas.show', $tenant->id) }}" class="btn btn-ghost">Cancelar</a>
        <button type="submit" class="btn btn-primary">Guardar cambios</button>
    </div>

</form>

@endsection

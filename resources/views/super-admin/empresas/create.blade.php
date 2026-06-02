@extends('super-admin.layout')

@section('title', 'Nueva empresa')

@section('content')

<div class="sa-hd">
    <div>
        <h1>Nueva empresa</h1>
        <div class="sub">Al guardar se crea la base de datos y se corren las migraciones automáticamente.</div>
    </div>
    <div class="sa-hd-actions">
        <a href="{{ route('super-admin.empresas.index') }}" class="btn btn-ghost">← Volver</a>
    </div>
</div>

<form method="POST" action="{{ route('super-admin.empresas.store') }}">
    @csrf

    @include('super-admin.partials.cuit-lookup', ['conSlug' => true])

    <div class="sa-card">
        <div class="sa-card-hd"><span class="sa-card-title">Datos de la empresa</span></div>
        <div class="sa-card-bd">

            <div class="grid-2">
                <div class="fg">
                    <label class="glb">Nombre *</label>
                    <input class="gin" type="text" name="nombre" id="f-nombre" value="{{ old('nombre') }}" placeholder="Gráfica El Taller" required>
                    @error('nombre') <div class="err-msg">{{ $message }}</div> @enderror
                </div>
                <div class="fg">
                    <label class="glb">Slug (subdominio) *</label>
                    <input class="gin" type="text" name="slug" id="f-slug" value="{{ old('slug') }}" placeholder="eltaller" pattern="[a-z0-9\-]+" required>
                    <div class="hint">Solo letras minúsculas, números y guiones. Será: <strong>slug.plote.ar</strong></div>
                    @error('slug') <div class="err-msg">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="grid-2">
                <div class="fg">
                    <label class="glb">CUIT</label>
                    <input class="gin" type="text" name="cuit" id="f-cuit" value="{{ old('cuit') }}" placeholder="20-12345678-9">
                </div>
                <div class="fg">
                    <label class="glb">Email</label>
                    <input class="gin" type="email" name="email" id="f-email" value="{{ old('email') }}" placeholder="contacto@empresa.com">
                </div>
            </div>

            <div class="grid-2">
                <div class="fg">
                    <label class="glb">Teléfono</label>
                    <input class="gin" type="text" name="telefono" id="f-telefono" value="{{ old('telefono') }}" placeholder="+54 9 341 ...">
                </div>
                <div class="fg">
                    <label class="glb">Dirección</label>
                    <input class="gin" type="text" name="direccion" id="f-direccion" value="{{ old('direccion') }}" placeholder="Av. Siempreviva 742">
                </div>
            </div>

        </div>
    </div>

    <div style="display:flex;gap:12px;justify-content:flex-end">
        <a href="{{ route('super-admin.empresas.index') }}" class="btn btn-ghost">Cancelar</a>
        <button type="submit" class="btn btn-primary">Crear empresa y generar BD →</button>
    </div>

</form>

@endsection

@extends('layouts.app')

@section('page-title', 'Editar — ' . $usuario->name)

@section('topbar-actions')
    <a href="{{ route('usuarios.index') }}" class="gbtn gbtn-ghost gbtn-sm">← Volver</a>
@endsection

@section('content')
<div style="max-width:560px">
<form method="POST" action="{{ route('usuarios.update', $usuario->id) }}">
@csrf @method('PUT')

<div class="gcard">
    <div class="gcard-hd"><span class="gcard-title">Datos del usuario</span></div>
    <div class="gcard-bd">
        <div class="gfg">
            <label class="glabel">Nombre *</label>
            <input type="text" name="name" class="ginput"
                   value="{{ old('name', $usuario->name) }}" required autofocus>
            @error('name')<div class="gerr">{{ $message }}</div>@enderror
        </div>

        <div class="gfg">
            <label class="glabel">Email *</label>
            <input type="email" name="email" class="ginput"
                   value="{{ old('email', $usuario->email) }}" required>
            @error('email')<div class="gerr">{{ $message }}</div>@enderror
        </div>

        <div class="gfg mb-0">
            <label class="glabel">Rol *</label>
            <select name="rol" class="gselect" required>
                @foreach(['admin'=>'Admin','ventas'=>'Ventas','produccion'=>'Producción'] as $val => $lbl)
                    <option value="{{ $val }}" {{ old('rol', $usuario->rol) === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                @endforeach
            </select>
            @error('rol')<div class="gerr">{{ $message }}</div>@enderror
        </div>
    </div>
</div>

<div class="gcard" style="margin-top:12px">
    <div class="gcard-hd">
        <span class="gcard-title">Cambiar contraseña</span>
        <span class="txd" style="font-size:11px">Dejá en blanco para no cambiarla</span>
    </div>
    <div class="gcard-bd">
        <div class="gfg">
            <label class="glabel">Nueva contraseña</label>
            <input type="password" name="password" class="ginput"
                   placeholder="Mínimo 8 caracteres">
            @error('password')<div class="gerr">{{ $message }}</div>@enderror
        </div>

        <div class="gfg mb-0">
            <label class="glabel">Repetir contraseña</label>
            <input type="password" name="password_confirmation" class="ginput"
                   placeholder="Repetir nueva contraseña">
        </div>
    </div>
</div>

@if($usuario->esSuper())
    <div class="gcard" style="margin-top:12px;border-color:rgba(230,80,42,.3)">
        <div class="gcard-bd" style="color:var(--txd);font-size:13px">
            ★ <strong style="color:var(--ac)">Administrador principal</strong> — acceso total a todos los módulos (no se limita).
        </div>
    </div>
@else
    @include('usuarios._modulos', ['seleccionados' => old('modulos', $usuario->modulos ?? [])])
@endif

<div style="margin-top:16px;display:flex;gap:8px">
    <button type="submit" class="gbtn gbtn-primary">Guardar cambios</button>
    <a href="{{ route('usuarios.index') }}" class="gbtn gbtn-ghost">Cancelar</a>
</div>
</form>
</div>
@endsection

@extends('layouts.app')

@section('page-title', 'Nuevo usuario')

@section('topbar-actions')
    <a href="{{ route('usuarios.index') }}" class="gbtn gbtn-ghost gbtn-sm">← Volver</a>
@endsection

@section('content')
<div style="max-width:560px">
<form method="POST" action="{{ route('usuarios.store') }}">
@csrf

<div class="gcard">
    <div class="gcard-hd"><span class="gcard-title">Datos del usuario</span></div>
    <div class="gcard-bd">
        <div class="gfg">
            <label class="glabel">Nombre *</label>
            <input type="text" name="name" class="ginput"
                   value="{{ old('name') }}" placeholder="Nombre completo" required autofocus>
            @error('name')<div class="gerr">{{ $message }}</div>@enderror
        </div>

        <div class="gfg">
            <label class="glabel">Email *</label>
            <input type="email" name="email" class="ginput"
                   value="{{ old('email') }}" placeholder="usuario@ejemplo.com" required>
            @error('email')<div class="gerr">{{ $message }}</div>@enderror
        </div>

        <div class="gfg">
            <label class="glabel">Rol *</label>
            <select name="rol" class="gselect" required>
                <option value="">— Seleccioná —</option>
                @foreach(['admin'=>'Admin','ventas'=>'Ventas','produccion'=>'Producción'] as $val => $lbl)
                    <option value="{{ $val }}" {{ old('rol') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                @endforeach
            </select>
            @error('rol')<div class="gerr">{{ $message }}</div>@enderror
        </div>

        <hr style="border-color:#1e1e1e;margin:20px 0">

        <div class="gfg">
            <label class="glabel">Contraseña *</label>
            <input type="password" name="password" class="ginput"
                   placeholder="Mínimo 8 caracteres" required>
            @error('password')<div class="gerr">{{ $message }}</div>@enderror
        </div>

        <div class="gfg mb-0">
            <label class="glabel">Repetir contraseña *</label>
            <input type="password" name="password_confirmation" class="ginput"
                   placeholder="Repetir contraseña" required>
        </div>
    </div>
</div>

<div style="margin-top:16px;display:flex;gap:8px">
    <button type="submit" class="gbtn gbtn-primary">Crear usuario</button>
    <a href="{{ route('usuarios.index') }}" class="gbtn gbtn-ghost">Cancelar</a>
</div>
</form>
</div>
@endsection

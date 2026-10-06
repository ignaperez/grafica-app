{{-- Perfil propio en el tema de la app. Antes era la vista de Breeze
     (<x-app-layout>, Tailwind + navbar viejo) y además ofrecía eliminar la
     propia cuenta; las bajas se hacen desde Usuarios. --}}
@extends('layouts.app')

@section('page-title', 'Mi perfil')

@section('content')
<div style="max-width:560px">

    @if(session('status') === 'profile-updated')
        <div class="gcard" style="margin-bottom:12px;border-color:rgba(63,185,106,.3)">
            <div class="gcard-bd" style="color:var(--green);font-size:13px">Datos actualizados.</div>
        </div>
    @elseif(session('status') === 'password-updated')
        <div class="gcard" style="margin-bottom:12px;border-color:rgba(63,185,106,.3)">
            <div class="gcard-bd" style="color:var(--green);font-size:13px">Contraseña actualizada.</div>
        </div>
    @endif

    {{-- Datos personales --}}
    <form method="POST" action="{{ route('profile.update') }}">
        @csrf @method('PATCH')
        <div class="gcard">
            <div class="gcard-hd">
                <span class="gcard-title">Mis datos</span>
                <span class="txd" style="font-size:11px">
                    {{ \App\Models\User::ROLES[$user->rol] ?? ucfirst($user->rol) }}@if($user->esSuper()) · ★ Administrador principal @endif
                </span>
            </div>
            <div class="gcard-bd">
                <div class="gfg">
                    <label class="glabel">Nombre *</label>
                    <input type="text" name="name" class="ginput"
                           value="{{ old('name', $user->name) }}" required>
                </div>
                <div class="gfg mb-0">
                    <label class="glabel">Email *</label>
                    <input type="email" name="email" class="ginput" autocomplete="username"
                           value="{{ old('email', $user->email) }}" required>
                </div>
            </div>
        </div>
        <div style="margin-top:12px">
            <button type="submit" class="gbtn gbtn-primary">Guardar datos</button>
        </div>
    </form>

    {{-- Contraseña (PasswordController de Breeze — usa el bag 'updatePassword',
         que el layout no muestra, así que los errores van acá). --}}
    <form method="POST" action="{{ route('password.update') }}" style="margin-top:20px">
        @csrf @method('PUT')
        <div class="gcard">
            <div class="gcard-hd">
                <span class="gcard-title">Cambiar mi contraseña</span>
                <span class="txd" style="font-size:11px">Mínimo 8 caracteres</span>
            </div>
            <div class="gcard-bd">
                <div class="gfg">
                    <label class="glabel">Contraseña actual *</label>
                    <input type="password" name="current_password" class="ginput"
                           autocomplete="current-password" required>
                    @error('current_password', 'updatePassword')<div class="gerr">{{ $message }}</div>@enderror
                </div>
                <div class="gfg">
                    <label class="glabel">Nueva contraseña *</label>
                    <input type="password" name="password" class="ginput"
                           autocomplete="new-password" required>
                    @error('password', 'updatePassword')<div class="gerr">{{ $message }}</div>@enderror
                </div>
                <div class="gfg mb-0">
                    <label class="glabel">Repetir nueva contraseña *</label>
                    <input type="password" name="password_confirmation" class="ginput"
                           autocomplete="new-password" required>
                    @error('password_confirmation', 'updatePassword')<div class="gerr">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
        <div style="margin-top:12px">
            <button type="submit" class="gbtn gbtn-primary">Cambiar contraseña</button>
        </div>
    </form>

</div>
@endsection

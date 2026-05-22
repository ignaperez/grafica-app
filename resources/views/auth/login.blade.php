<x-guest-layout>
    <p class="login-title">Iniciar sesión</p>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="fg">
            <label class="flabel" for="email">Email</label>
            <input id="email" class="finput" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
            @error('email') <div class="ferr">{{ $message }}</div> @enderror
        </div>

        <div class="fg">
            <label class="flabel" for="password">Contraseña</label>
            <input id="password" class="finput" type="password" name="password" required autocomplete="current-password">
            @error('password') <div class="ferr">{{ $message }}</div> @enderror
        </div>

        <div class="fg">
            <label class="fcheck">
                <input type="checkbox" name="remember">
                Recordarme
            </label>
        </div>

        <button type="submit" class="fbtn">Entrar</button>

        <div class="frow">
            @if (Route::has('password.request'))
                <a class="flink" href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</a>
            @endif
        </div>
    </form>
</x-guest-layout>

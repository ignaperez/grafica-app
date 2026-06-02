@extends('super-admin.layout')

@section('title', 'Cambiar contraseña')

@section('content')

<div class="sa-hd">
    <div>
        <h1>Cambiar contraseña</h1>
        <div class="sub">Panel Super Admin · {{ auth()->user()->email }}</div>
    </div>
    <div class="sa-hd-actions">
        <a href="{{ route('super-admin.empresas.index') }}" class="btn btn-ghost">← Volver</a>
    </div>
</div>

<div style="max-width:460px">
    <div class="sa-card">
        <div class="sa-card-hd">
            <span class="sa-card-title">Nueva contraseña</span>
        </div>
        <div class="sa-card-bd">

            @if($errors->any())
            <div class="alert-err" style="margin-bottom:20px">
                @foreach($errors->all() as $e){{ $e }}<br>@endforeach
            </div>
            @endif

            <form method="POST" action="{{ route('super-admin.cambiar-clave.post') }}">
                @csrf

                <div class="fg">
                    <label class="glb">Contraseña actual</label>
                    <input class="gin" type="password" name="clave_actual" autocomplete="current-password" required>
                    @error('clave_actual')
                    <div class="err-msg">{{ $message }}</div>
                    @enderror
                </div>

                <div class="fg">
                    <label class="glb">Nueva contraseña</label>
                    <input class="gin" type="password" name="nueva_clave" id="nueva_clave"
                           autocomplete="new-password" required
                           oninput="checkStrength(this.value)">
                    <div id="strength-bar" style="height:3px;margin-top:6px;border-radius:2px;background:#1e1e1e;overflow:hidden">
                        <div id="strength-fill" style="height:100%;width:0;transition:width .3s,background .3s"></div>
                    </div>
                    <div id="strength-txt" class="hint" style="margin-top:4px"></div>
                    <div class="hint">Mínimo 12 caracteres · mayúsculas, minúsculas y números.</div>
                    @error('nueva_clave')
                    <div class="err-msg">{{ $message }}</div>
                    @enderror
                </div>

                <div class="fg">
                    <label class="glb">Confirmar nueva contraseña</label>
                    <input class="gin" type="password" name="nueva_clave_confirmation"
                           autocomplete="new-password" required>
                </div>

                <button class="btn btn-primary" type="submit" style="width:100%;justify-content:center">
                    Guardar nueva contraseña →
                </button>
            </form>

        </div>
    </div>

    {{-- Nota recuperación --}}
    <div style="margin-top:16px;padding:14px 16px;background:#111;border:1px solid #1e1e1e;border-radius:8px;font-size:.82rem;color:#666;line-height:1.6">
        <span style="color:#888;font-weight:600">¿Olvidaste la contraseña?</span><br>
        Si perdés el acceso, ejecutá este comando en el servidor para restablecerla:<br>
        <code style="font-family:'DM Mono',monospace;color:#e6502a;font-size:.8rem;display:block;margin-top:6px;background:#0a0a0a;padding:8px 10px;border-radius:4px;border:1px solid #1e1e1e">
            php artisan superadmin:reset-password
        </code>
    </div>
</div>

@endsection

@section('scripts')
<script>
function checkStrength(val) {
    let score = 0;
    if (val.length >= 12) score++;
    if (val.length >= 16) score++;
    if (/[A-Z]/.test(val) && /[a-z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;

    const fill = document.getElementById('strength-fill');
    const txt  = document.getElementById('strength-txt');
    const pct  = Math.min(score * 20, 100);
    const colors = ['#e05050','#e08050','#e6c42a','#3fb96a','#3fb96a'];
    const labels = ['Muy débil','Débil','Regular','Fuerte','Muy fuerte'];

    fill.style.width      = pct + '%';
    fill.style.background = colors[score - 1] || '#1e1e1e';
    txt.textContent       = val.length ? (labels[score - 1] || '') : '';
    txt.style.color       = colors[score - 1] || '#666';
}
</script>
@endsection

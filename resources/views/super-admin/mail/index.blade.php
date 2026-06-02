@extends('super-admin.layout')

@section('title', 'Cuentas de Email')

@section('content')

<div class="sa-hd">
    <div>
        <h1>Cuentas de Email</h1>
        <div class="sub mono">mail.plote.ar</div>
    </div>
    <div class="sa-hd-actions">
        <a href="{{ route('super-admin.empresas.index') }}" class="btn btn-ghost">← Volver</a>
    </div>
</div>

{{-- Flash contraseña reseteada --}}
@if(session('password_reset_mail'))
@php $pr = session('password_reset_mail'); @endphp
<div style="background:#0d1a10;border:1px solid #1d4a30;border-radius:8px;padding:18px 20px;margin-bottom:20px">
    <div style="font-size:.82rem;color:#3fb96a;font-weight:600;margin-bottom:8px">✓ Contraseña actualizada</div>
    <div style="display:flex;gap:24px;flex-wrap:wrap">
        <div>
            <div style="font-size:.72rem;color:#666;text-transform:uppercase;letter-spacing:.06em">Email</div>
            <div class="mono" style="color:#e8e4dc">{{ $pr['email'] }}</div>
        </div>
        <div>
            <div style="font-size:.72rem;color:#666;text-transform:uppercase;letter-spacing:.06em">Nueva contraseña</div>
            <div class="mono" style="color:#f5c842;font-size:1.05rem">{{ $pr['password'] }}</div>
        </div>
    </div>
</div>
@endif

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start">

{{-- Lista de cuentas --}}
<div class="sa-card">
    <div class="sa-card-hd"><span class="sa-card-title">{{ $cuentas->count() }} {{ $cuentas->count() === 1 ? 'cuenta' : 'cuentas' }}</span></div>

    @if($cuentas->isEmpty())
    <div style="padding:40px;text-align:center;color:#555;font-size:.88rem">No hay cuentas creadas todavía.</div>
    @else
    <table class="sa-table">
        <thead>
            <tr>
                <th>Email</th>
                <th>Estado</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($cuentas as $cuenta)
            <tr>
                <td class="mono" style="font-size:.85rem">{{ $cuenta->email }}</td>
                <td>
                    <form method="POST" action="{{ route('super-admin.email.toggle', $cuenta) }}" style="display:inline">
                        @csrf
                        <button type="submit" class="badge {{ $cuenta->active ? 'badge-ok' : 'badge-del' }}"
                                style="cursor:pointer;border:none;background:inherit">
                            {{ $cuenta->active ? '✓ Activa' : '✗ Inactiva' }}
                        </button>
                    </form>
                </td>
                <td style="text-align:right">
                    <button class="btn btn-ghost btn-sm"
                            onclick="abrirReset({{ $cuenta->id }}, '{{ $cuenta->email }}')"
                            style="border-color:#2a2a2a;color:#888;margin-right:6px">
                        Cambiar clave
                    </button>
                    <form method="POST" action="{{ route('super-admin.email.destroy', $cuenta) }}" style="display:inline"
                          onsubmit="return confirm('¿Eliminar {{ $cuenta->email }}?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

{{-- Nueva cuenta --}}
<div>
    <div class="sa-card" style="margin-bottom:16px">
        <div class="sa-card-hd"><span class="sa-card-title">Nueva cuenta</span></div>
        <div class="sa-card-bd">
            <form method="POST" action="{{ route('super-admin.email.store') }}">
                @csrf
                <div class="fg">
                    <label class="glb">Dirección de email</label>
                    <div style="display:flex;align-items:center;gap:0">
                        <input type="text" name="usuario" class="gin" placeholder="ventas"
                               value="{{ old('usuario') }}"
                               style="border-radius:6px 0 0 6px;flex:1"
                               pattern="[a-z0-9._-]+" title="Solo letras minúsculas, números, puntos, guiones">
                        <span style="background:#0f0f0f;border:1px solid #2a2a2a;border-left:none;padding:9px 12px;color:#666;border-radius:0 6px 6px 0;white-space:nowrap;font-family:monospace;font-size:.88rem">@plote.ar</span>
                    </div>
                    <input type="hidden" name="dominio" value="plote.ar">
                    @error('usuario')<div class="err-msg">{{ $message }}</div>@enderror
                </div>
                <div class="fg">
                    <label class="glb">Contraseña</label>
                    <input type="password" name="password" class="gin" placeholder="Mín. 8 caracteres">
                    @error('password')<div class="err-msg">{{ $message }}</div>@enderror
                </div>
                <div class="fg">
                    <label class="glb">Confirmar contraseña</label>
                    <input type="password" name="password_confirmation" class="gin">
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%">Crear cuenta</button>
            </form>
        </div>
    </div>

    {{-- Info IMAP/SMTP --}}
    <div class="sa-card">
        <div class="sa-card-hd"><span class="sa-card-title">Datos de conexión</span></div>
        <div class="sa-card-bd" style="display:grid;gap:10px;font-size:.85rem">
            <div>
                <div style="font-size:.72rem;color:#666;text-transform:uppercase;letter-spacing:.06em;margin-bottom:3px">IMAP (recibir)</div>
                <div class="mono">mail.plote.ar <span style="color:#555">puerto</span> 993 SSL</div>
            </div>
            <div>
                <div style="font-size:.72rem;color:#666;text-transform:uppercase;letter-spacing:.06em;margin-bottom:3px">SMTP (enviar)</div>
                <div class="mono">mail.plote.ar <span style="color:#555">puerto</span> 587 TLS</div>
            </div>
            <div style="font-size:.78rem;color:#555;line-height:1.5;margin-top:4px">
                Usá estas credenciales en Outlook, Thunderbird, Gmail o cualquier cliente de correo.
            </div>
        </div>
    </div>
</div>

</div>

{{-- Modal cambiar contraseña --}}
<div id="modal-reset" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:200;align-items:center;justify-content:center">
    <div style="background:#111;border:1px solid #1e1e1e;border-radius:10px;padding:28px;width:100%;max-width:400px;margin:20px">
        <div style="font-weight:600;margin-bottom:16px">Cambiar contraseña</div>
        <div id="modal-email" class="mono" style="color:#888;font-size:.85rem;margin-bottom:16px"></div>
        <form id="form-reset" method="POST">
            @csrf
            <div class="fg">
                <label class="glb">Nueva contraseña</label>
                <input type="password" name="password" class="gin" placeholder="Mín. 8 caracteres" required minlength="8">
            </div>
            <div class="fg">
                <label class="glb">Confirmar contraseña</label>
                <input type="password" name="password_confirmation" class="gin" required>
            </div>
            <div style="display:flex;gap:10px;margin-top:20px">
                <button type="button" onclick="cerrarReset()" class="btn btn-ghost" style="flex:1">Cancelar</button>
                <button type="submit" class="btn btn-primary" style="flex:1">Guardar</button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
const routes = @json($cuentas->mapWithKeys(fn($c) => [$c->id => route('super-admin.email.reset-password', $c)]));

function abrirReset(id, email) {
    document.getElementById('modal-email').textContent = email;
    document.getElementById('form-reset').action = routes[id];
    document.getElementById('form-reset').querySelector('[name=password]').value = '';
    document.getElementById('form-reset').querySelector('[name=password_confirmation]').value = '';
    const modal = document.getElementById('modal-reset');
    modal.style.display = 'flex';
}
function cerrarReset() {
    document.getElementById('modal-reset').style.display = 'none';
}
document.getElementById('modal-reset').addEventListener('click', function(e) {
    if (e.target === this) cerrarReset();
});
</script>
@endsection

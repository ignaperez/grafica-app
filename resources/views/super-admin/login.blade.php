<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Super Admin — plote.ar</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=DM+Mono&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{background:#0a0a0a;color:#e8e4dc;font-family:'DM Sans',sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center}
.card{background:#141414;border:1px solid #1e1e1e;border-radius:12px;padding:40px;width:100%;max-width:380px}
.logo{font-family:'DM Mono',monospace;font-size:1.4rem;color:#e6502a;margin-bottom:8px}
h1{font-size:1rem;color:#888;font-weight:400;margin-bottom:32px}
label{display:block;font-size:.78rem;color:#888;letter-spacing:.06em;text-transform:uppercase;margin-bottom:6px}
input{width:100%;background:#0f0f0f;border:1px solid #2a2a2a;color:#e8e4dc;padding:10px 14px;border-radius:6px;font-size:.95rem;font-family:inherit;outline:none;transition:border-color .15s}
input:focus{border-color:#e6502a}
.fg{margin-bottom:18px}
.err{color:#e05050;font-size:.82rem;margin-top:6px}
button{width:100%;background:#e6502a;color:#fff;border:none;padding:12px;border-radius:6px;font-size:.95rem;font-weight:600;cursor:pointer;font-family:inherit;margin-top:8px}
button:hover{background:#d44520}
.alert{background:#1a0a0a;border:1px solid #e05050;color:#e05050;border-radius:6px;padding:12px 14px;font-size:.88rem;margin-bottom:20px}
</style>
</head>
<body>
<div class="card">
    <div class="logo">plote.ar</div>
    <h1>Panel de administración</h1>

    @if(session('error'))
    <div class="alert">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('super-admin.login.post') }}">
        @csrf
        <div class="fg">
            <label>Email</label>
            <input type="email" name="email" value="{{ old('email') }}" autofocus required>
            @error('email') <div class="err">{{ $message }}</div> @enderror
        </div>
        <div class="fg">
            <label>Contraseña</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit">Ingresar →</button>
    </form>
</div>
</body>
</html>

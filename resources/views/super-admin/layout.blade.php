<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'Super Admin') — plote.ar</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--bg:#0a0a0a;--bgs:#111;--b:#1e1e1e;--bm:#2a2a2a;--tx:#e8e4dc;--txm:#888;--ac:#e6502a}
body{background:var(--bg);color:var(--tx);font-family:'DM Sans',sans-serif;min-height:100vh;display:flex;flex-direction:column}
a{color:inherit;text-decoration:none}

/* Nav */
.sa-nav{background:var(--bgs);border-bottom:1px solid var(--b);padding:0 32px;display:flex;align-items:center;gap:24px;height:52px}
.sa-brand{font-family:'DM Mono',monospace;font-size:1rem;color:var(--ac);font-weight:500}
.sa-brand span{color:var(--txm)}
.sa-sep{flex:1}
.sa-user{font-size:.82rem;color:var(--txm)}
.sa-logout{font-size:.82rem;color:var(--txm);background:none;border:1px solid var(--bm);padding:4px 12px;border-radius:4px;cursor:pointer;font-family:inherit;transition:.15s}
.sa-logout:hover{border-color:var(--ac);color:var(--ac)}

/* Main */
.sa-wrap{max-width:1100px;margin:0 auto;padding:40px 24px;width:100%}
.sa-hd{display:flex;align-items:center;gap:16px;margin-bottom:32px}
.sa-hd h1{font-size:1.35rem;font-weight:700;letter-spacing:-.02em}
.sa-hd .sub{color:var(--txm);font-size:.85rem;margin-top:2px}
.sa-hd-actions{margin-left:auto;display:flex;gap:10px}

/* Cards */
.sa-card{background:var(--bgs);border:1px solid var(--b);border-radius:10px;overflow:hidden;margin-bottom:20px}
.sa-card-hd{padding:16px 20px;border-bottom:1px solid var(--b);display:flex;align-items:center;gap:12px}
.sa-card-title{font-weight:600;font-size:.95rem}
.sa-card-bd{padding:20px}

/* Table */
.sa-table{width:100%;border-collapse:collapse}
.sa-table th{text-align:left;font-size:.75rem;letter-spacing:.1em;text-transform:uppercase;color:var(--txm);padding:10px 16px;border-bottom:1px solid var(--b)}
.sa-table td{padding:13px 16px;border-bottom:1px solid var(--b);font-size:.9rem;vertical-align:middle}
.sa-table tr:last-child td{border-bottom:none}
.sa-table tr:hover td{background:#161616}
.mono{font-family:'DM Mono',monospace;font-size:.85rem}

/* Badges */
.badge{display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:20px;font-size:.75rem;font-weight:600}
.badge-ok{background:#0d2218;color:#3fb96a}
.badge-del{background:#1a0a0a;color:#e05050}
.badge-warn{background:#1a1200;color:#d4a017}

/* Buttons */
.btn{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:6px;font-size:.85rem;font-weight:600;cursor:pointer;border:none;font-family:inherit;transition:.15s}
.btn-primary{background:var(--ac);color:#fff}
.btn-primary:hover{background:#d44520}
.btn-ghost{background:transparent;border:1px solid var(--bm);color:var(--tx)}
.btn-ghost:hover{border-color:var(--ac);color:var(--ac)}
.btn-danger{background:transparent;border:1px solid #e05050;color:#e05050}
.btn-danger:hover{background:#e05050;color:#fff}
.btn-sm{padding:5px 12px;font-size:.78rem}

/* Forms */
.fg{margin-bottom:18px}
label.glb{display:block;font-size:.78rem;color:var(--txm);letter-spacing:.06em;text-transform:uppercase;margin-bottom:6px}
input.gin,select.gin,textarea.gin{width:100%;background:#0f0f0f;border:1px solid var(--bm);color:var(--tx);padding:9px 13px;border-radius:6px;font-size:.9rem;font-family:inherit;outline:none;transition:.15s}
input.gin:focus,select.gin:focus,textarea.gin:focus{border-color:var(--ac)}
.hint{font-size:.78rem;color:var(--txm);margin-top:5px}
.err-msg{color:#e05050;font-size:.82rem;margin-top:5px}

/* Alerts */
.alert-ok{background:#0d2218;border:1px solid #1d4a30;color:#3fb96a;border-radius:6px;padding:12px 16px;margin-bottom:20px;font-size:.88rem}
.alert-err{background:#1a0a0a;border:1px solid #4a1d1d;color:#e05050;border-radius:6px;padding:12px 16px;margin-bottom:20px;font-size:.88rem}

/* Grid */
.grid-2{display:grid;grid-template-columns:1fr 1fr;gap:16px}
@media(max-width:600px){.grid-2{grid-template-columns:1fr}}
</style>
</head>
<body>

<nav class="sa-nav">
    <div class="sa-brand">plote<span>.ar</span> <span style="color:#333;margin:0 6px">|</span> <span style="font-size:.75rem">Super Admin</span></div>
    <div class="sa-sep"></div>
    <span class="sa-user">{{ auth()->user()->name }}</span>
    <a href="{{ route('super-admin.cambiar-clave') }}" class="sa-logout" style="text-decoration:none">Cambiar clave</a>
    <form method="POST" action="{{ route('super-admin.logout') }}" style="display:inline">
        @csrf
        <button class="sa-logout" type="submit">Cerrar sesión</button>
    </form>
</nav>

<div class="sa-wrap">

    @if(session('success'))
    <div class="alert-ok">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="alert-err">{{ session('error') }}</div>
    @endif

    @yield('content')

</div>

@yield('scripts')
</body>
</html>

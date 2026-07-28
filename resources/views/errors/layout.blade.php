<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('code') · @yield('title', 'Error')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --bg:#0f0f0f; --bg-s:#141414; --b:#1e1e1e; --bm:#2a2a2a; --tx:#e8e4dc; --txd:#888; --ac:#e6502a; }
        * { box-sizing:border-box; margin:0; padding:0; }
        body {
            background: radial-gradient(1200px 600px at 50% -10%, #191512 0%, var(--bg) 55%);
            color: var(--tx); font-family:'DM Sans', system-ui, sans-serif;
            min-height:100vh; display:flex; align-items:center; justify-content:center; padding:24px;
        }
        .wrap { text-align:center; max-width:520px; }
        .code {
            font-family:'DM Mono', monospace; font-size:clamp(84px, 22vw, 160px); font-weight:500;
            line-height:1; letter-spacing:-4px; color:var(--ac); text-shadow:0 8px 40px rgba(230,80,42,.25);
        }
        .dot { color:var(--txd); }
        .title { font-size:22px; font-weight:700; margin-top:6px; letter-spacing:-.01em; }
        .msg { color:var(--txd); font-size:14.5px; line-height:1.6; margin-top:12px; }
        .btn {
            display:inline-flex; align-items:center; gap:8px; margin-top:28px;
            background:var(--ac); color:#fff; text-decoration:none; font-weight:600; font-size:14px;
            padding:11px 22px; border-radius:999px; transition:transform .12s, box-shadow .12s;
        }
        .btn:hover { transform:translateY(-1px); box-shadow:0 8px 24px rgba(230,80,42,.35); }
        .btn.ghost { background:transparent; color:var(--txd); border:1px solid var(--bm); margin-left:8px; }
        .btn.ghost:hover { color:var(--tx); box-shadow:none; }
        .brand { margin-top:44px; display:inline-flex; align-items:center; gap:9px; opacity:.75; }
        .brand-mark {
            width:30px; height:30px; border-radius:8px; background:var(--ac);
            display:inline-flex; align-items:center; justify-content:center;
            box-shadow:0 4px 16px rgba(230,80,42,.35);
        }
        .brand-mark span { width:8px; height:8px; border-radius:50%; background:#fff; display:block; }
        .brand-txt { font-family:'DM Sans', sans-serif; font-weight:700; font-size:17px; letter-spacing:-.02em; color:var(--tx); }
        .brand-txt em { font-style:normal; color:var(--txd); font-weight:500; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="code"><span class="dot">●</span> @yield('code')</div>
        <div class="title">@yield('title', 'Algo salió mal')</div>
        <div class="msg">@yield('message', 'Ocurrió un error inesperado.')</div>
        <div>
            <a href="{{ url('/') }}" class="btn">← Volver al inicio</a>
            <a href="javascript:history.back()" class="btn ghost">Volver atrás</a>
        </div>
        <div class="brand">
            <span class="brand-mark"><span></span></span>
            <span class="brand-txt">plote<em>.ar</em></span>
        </div>
    </div>
</body>
</html>

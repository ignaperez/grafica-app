<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Gráfica') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        :root{
            --bg:#0a0a0a;
            --bg-s:#111111;
            --bg-h:#161616;
            --b:#1c1c1c;
            --bm:#262626;
            --tx:#e8e4dc;
            --txm:#4a4a4a;
            --txd:#666;
            --ac:#e6502a;
            --green:#3fb96a;
            --amber:#e0960a;
            --blue:#3d8fd4;
            --red:#e05050;
            --mono:'DM Mono',monospace;
            --sans:'DM Sans',sans-serif;
            --sw:220px;
            --rad:10px;
        }
        html,body{height:100%;background:var(--bg);color:var(--tx);font-family:var(--sans);font-size:14px;line-height:1.6}

        /* ── SIDEBAR ─────────────────────────────────────────── */
        #sidebar{position:fixed;top:0;left:0;bottom:0;width:var(--sw);background:var(--bg-s);border-right:1px solid var(--b);display:flex;flex-direction:column;z-index:100;transition:transform .2s}
        .s-logo{padding:12px 18px 10px;border-bottom:1px solid var(--b);display:flex;align-items:center;justify-content:center}
        .s-logo img{max-width:70px;max-height:70px;width:auto;height:auto;display:block}
        .s-mark{font-family:var(--mono);font-size:9px;letter-spacing:3px;text-transform:uppercase;color:var(--ac);margin-bottom:4px}
        .s-name{font-size:14px;font-weight:600;color:var(--tx);letter-spacing:-.3px}
        .s-nav{flex:1;padding:10px 8px;overflow-y:auto}
        .s-section{font-size:9px;letter-spacing:2px;text-transform:uppercase;color:#333;padding:12px 10px 4px;font-weight:700}
        .s-item{display:flex;align-items:center;gap:9px;padding:7px 10px;border-radius:7px;font-size:13px;color:var(--txd);text-decoration:none;transition:all .12s;margin-bottom:1px}
        .s-item:hover{background:var(--bg-h);color:var(--tx);text-decoration:none}
        .s-item.on{background:#191919;color:var(--tx);font-weight:500}
        .s-item.on .dot{background:var(--ac)}
        .dot{width:5px;height:5px;border-radius:50%;background:#252525;flex-shrink:0;transition:background .12s}
        .s-trigger{display:flex;align-items:center;justify-content:space-between;width:100%;padding:6px 10px;border-radius:7px;font-size:10px;letter-spacing:.12em;text-transform:uppercase;font-weight:700;color:#585858;background:none;border:none;cursor:pointer;font-family:var(--sans);transition:all .12s;margin-bottom:1px;margin-top:8px}
        .s-trigger:hover{background:var(--bg-h);color:#888}
        .s-trigger.open{color:#888}
        .s-trigger .tl{display:flex;align-items:center;gap:7px}
        .s-arrow{font-size:11px;color:#585858;transition:transform .18s;line-height:1}
        .s-trigger.open .s-arrow{transform:rotate(90deg);color:#888}
        .s-sub{display:none}
        .s-sub.open{display:block}
        .s-user{padding:12px 16px;border-top:1px solid var(--b);display:flex;align-items:center;gap:10px}
        .s-avatar{width:28px;height:28px;border-radius:50%;background:#1e100a;border:1px solid #3a1e10;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:600;color:var(--ac);flex-shrink:0}
        .s-uname{font-size:12.5px;font-weight:500;color:var(--tx)}
        .s-logout{font-size:11px;color:var(--txd);background:none;border:none;cursor:pointer;font-family:var(--sans);padding:0;transition:color .12s;text-align:left}
        .s-logout:hover{color:var(--ac)}

        /* ── MAIN / TOPBAR ───────────────────────────────────── */
        #main{margin-left:var(--sw);min-height:100vh;display:flex;flex-direction:column}
        .topbar{height:52px;border-bottom:1px solid var(--b);display:flex;align-items:center;justify-content:space-between;padding:0 24px;background:var(--bg-s);position:sticky;top:0;z-index:50}
        .topbar-bc{font-size:12.5px;color:#333;display:flex;align-items:center;gap:8px}
        .topbar-bc span{color:#262626}
        .topbar-bc strong{color:var(--txd);font-weight:500}
        .page-content{flex:1;padding:24px 24px 48px}

        /* ── FLASH ───────────────────────────────────────────── */
        .flash{margin-bottom:16px;padding:10px 16px;border-radius:8px;font-size:13px;display:flex;align-items:center;gap:8px}
        .flash-ok{background:#091a0c;border:1px solid #163320;color:var(--green)}
        .flash-err{background:#1a0909;border:1px solid #331616;color:var(--red)}

        /* ── CARDS ───────────────────────────────────────────── */
        .gcard{background:var(--bg-s);border:1px solid var(--b);border-radius:var(--rad);overflow:hidden;transition:border-color .15s}
        .gcard-hd{padding:13px 18px;border-bottom:1px solid var(--b);display:flex;align-items:center;justify-content:space-between;gap:12px}
        .gcard-title{font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--txd)}
        .gcard-bd{padding:18px}

        /* ── BUTTONS ─────────────────────────────────────────── */
        .gbtn{display:inline-flex;align-items:center;gap:5px;padding:7px 14px;border-radius:8px;font-size:12.5px;font-weight:500;font-family:var(--sans);cursor:pointer;text-decoration:none;transition:all .15s;border:none;line-height:1;white-space:nowrap}
        .gbtn-primary{background:var(--ac);color:#fff}
        .gbtn-primary:hover{background:#cb4120;color:#fff;text-decoration:none}
        .gbtn-ghost{background:transparent;color:var(--txd);border:1px solid var(--bm)}
        .gbtn-ghost:hover{background:var(--bg-h);color:var(--tx);border-color:#333;text-decoration:none}
        .gbtn-danger{background:rgba(224,80,80,.08);color:var(--red);border:1px solid rgba(224,80,80,.2)}
        .gbtn-danger:hover{background:rgba(224,80,80,.14);color:#ef6666;text-decoration:none}
        .gbtn-sm{padding:5px 11px;font-size:11.5px;border-radius:7px}
        .gbtn-xs{padding:3px 9px;font-size:11px;border-radius:6px}

        /* ── BADGES ──────────────────────────────────────────── */
        .badge-estado{display:inline-flex;align-items:center;gap:5px;padding:3px 9px;border-radius:20px;font-size:10.5px;font-weight:600;white-space:nowrap;letter-spacing:.2px}
        .badge-estado::before{content:'';width:5px;height:5px;border-radius:50%;flex-shrink:0;background:currentColor}
        .be-borrador     {color:#4a4a4a;background:rgba(255,255,255,.03);border:1px solid #222}
        .be-en_produccion{color:var(--amber);background:rgba(224,150,10,.08);border:1px solid rgba(224,150,10,.18)}
        .be-lista        {color:var(--green);background:rgba(63,185,106,.08);border:1px solid rgba(63,185,106,.18)}
        .be-entregada    {color:var(--blue); background:rgba(61,143,212,.08);border:1px solid rgba(61,143,212,.18)}
        .be-cancelada    {color:var(--red);  background:rgba(224,80,80,.08); border:1px solid rgba(224,80,80,.18)}
        .be-pendiente    {color:#4a4a4a;background:rgba(255,255,255,.03);border:1px solid #222}
        .be-en_proceso   {color:var(--amber);background:rgba(224,150,10,.08);border:1px solid rgba(224,150,10,.18)}
        .be-terminado    {color:var(--green);background:rgba(63,185,106,.08);border:1px solid rgba(63,185,106,.18)}
        .be-entregado    {color:var(--blue); background:rgba(61,143,212,.08);border:1px solid rgba(61,143,212,.18)}

        /* ── TABLES ──────────────────────────────────────────── */
        .gtable{width:100%;border-collapse:collapse}
        .gtable th{font-size:9px;letter-spacing:1.5px;text-transform:uppercase;color:#2e2e2e;font-weight:700;padding:9px 16px;border-bottom:1px solid var(--b);text-align:left}
        .gtable td{padding:12px 16px;border-bottom:1px solid #141414;font-size:13px;color:var(--tx);vertical-align:middle}
        .gtable tr:last-child td{border-bottom:none}
        .gtable tbody tr{transition:background .1s}
        .gtable tbody tr:hover{background:var(--bg-h)}

        /* ── FORMS ───────────────────────────────────────────── */
        .gfg{margin-bottom:16px}
        .glabel{display:block;font-size:9.5px;letter-spacing:1px;text-transform:uppercase;color:var(--txm);margin-bottom:6px;font-weight:700}
        .ginput,.gselect,.gtextarea{width:100%;background:#0d0d0d;border:1px solid var(--bm);border-radius:8px;padding:9px 12px;font-size:13px;color:var(--tx);font-family:var(--sans);transition:border-color .15s,box-shadow .15s;outline:none}
        .ginput:focus,.gselect:focus,.gtextarea:focus{border-color:var(--ac);box-shadow:0 0 0 3px rgba(230,80,42,.08)}
        .ginput::placeholder{color:#2a2a2a}
        input[type="date"].ginput,input[type="date"].ginput-date{color-scheme:dark}
        .gtextarea{resize:vertical;min-height:80px}
        .gerr{font-size:11.5px;color:var(--red);margin-top:5px}

        /* ── PROGRESS ────────────────────────────────────────── */
        .gprog{height:3px;background:#1e1e1e;border-radius:2px;overflow:hidden}
        .gprog-fill{height:100%;border-radius:2px;background:var(--ac);transition:width .4s ease}
        .gprog-fill.done{background:var(--green)}

        /* ── MISC ────────────────────────────────────────────── */
        .mono{font-family:var(--mono)}
        .txm{color:var(--txm)}
        .txd{color:var(--txd)}

        /* ── SELECT2 DARK ────────────────────────────────────── */
        .select2-container--default .select2-selection--single{background:#0d0d0d;border:1px solid var(--bm);border-radius:8px;height:38px;display:flex;align-items:center;transition:border-color .15s}
        .select2-container--default.select2-container--focus .select2-selection--single{border-color:var(--ac);box-shadow:0 0 0 3px rgba(230,80,42,.08)}
        .select2-container--default .select2-selection--single .select2-selection__rendered{color:var(--tx);line-height:38px;padding-left:12px}
        .select2-container--default .select2-selection--single .select2-selection__arrow{height:36px}
        .select2-dropdown{background:#141414;border:1px solid var(--bm);border-radius:8px;box-shadow:0 8px 24px rgba(0,0,0,.4)}
        .select2-container--default .select2-results__option{color:var(--txd);font-size:13px;padding:8px 12px}
        .select2-container--default .select2-results__option--highlighted{background:var(--bg-h);color:var(--tx)}
        .select2-search--dropdown .select2-search__field{background:#0d0d0d;border:1px solid var(--bm);color:var(--tx);border-radius:6px;padding:7px 10px;font-family:var(--sans)}

        /* ── MOBILE ──────────────────────────────────────────── */
        .s-toggle{display:none;background:none;border:none;color:var(--tx);cursor:pointer;font-size:20px;line-height:1}
        @media(max-width:768px){
            #sidebar{transform:translateX(-100%)}
            #sidebar.open{transform:translateX(0);box-shadow:4px 0 20px rgba(0,0,0,.6)}
            #main{margin-left:0}
            .s-toggle{display:block}
            .page-content{padding:16px 14px 36px}
            .topbar{padding:0 16px}
        }
    </style>
</head>
<body>

<aside id="sidebar">
    <div class="s-logo">
        <img src="{{ asset('images/logo.png') }}" alt="Plote.ar Gráfica">
    </div>
    @php
        $rol = auth()->user()->rol;
        $produccionOn = request()->routeIs('dashboard') || request()->routeIs('inicio') || request()->routeIs('reportes.*') || request()->routeIs('ordenes-trabajo.*') || request()->routeIs('trabajos.*') || request()->routeIs('trabajos-libres.*') || request()->routeIs('vehiculos-ploteo.*') || request()->routeIs('remitos.*');
        $ventasOn     = request()->routeIs('clientes.*') || request()->routeIs('presupuestos.*') || request()->routeIs('facturas.*') || request()->routeIs('catalogo.*') || request()->routeIs('listas-precios.*') || (request()->routeIs('remitos.*') && in_array(auth()->user()->rol ?? '', ['admin','ventas']));
        $configOn     = request()->routeIs('tipo-trabajos.*') || request()->routeIs('materiales.*') || request()->routeIs('maquinas.*') || request()->routeIs('remito-cais.*');
        $sistemaOn    = request()->routeIs('usuarios.*') || request()->routeIs('configuracion.*');
        $rrhhOn       = request()->is('rrhh/*');
    @endphp
    <nav class="s-nav">

        {{-- ══ PRODUCCIÓN ══ --}}
        <button class="s-trigger {{ $produccionOn ? 'open' : '' }}" onclick="toggleGroup(this)">
            <span class="tl">Producción</span><span class="s-arrow">›</span>
        </button>
        <div class="s-sub {{ $produccionOn ? 'open' : '' }}">
            @if($rol === 'admin')
                <a href="{{ route('dashboard') }}" class="s-item {{ request()->routeIs('dashboard') ? 'on' : '' }}">
                    <span class="dot"></span> Dashboard
                </a>
                <a href="{{ route('reportes.produccion') }}" class="s-item {{ request()->routeIs('reportes.*') ? 'on' : '' }}">
                    <span class="dot"></span> Reportes
                </a>
            @else
                <a href="{{ route('inicio') }}" class="s-item {{ request()->routeIs('inicio') ? 'on' : '' }}">
                    <span class="dot"></span> Inicio
                </a>
            @endif
            <a href="{{ route('ordenes-trabajo.index') }}" class="s-item {{ request()->routeIs('ordenes-trabajo.*') ? 'on' : '' }}">
                <span class="dot"></span> Órdenes de trabajo
            </a>
            <a href="{{ route('trabajos-libres.index') }}" class="s-item {{ request()->routeIs('trabajos-libres.*') ? 'on' : '' }}">
                <span class="dot"></span> Trabajos
            </a>
            <a href="{{ route('vehiculos-ploteo.index') }}" class="s-item {{ request()->routeIs('vehiculos-ploteo.*') ? 'on' : '' }}">
                <span class="dot"></span> Vehículos
            </a>
            {{-- Remitos solo aparece aquí para producción; admin/ventas lo ven en Ventas --}}
            @if($rol === 'produccion')
            <a href="{{ route('remitos.index') }}" class="s-item {{ request()->routeIs('remitos.*') ? 'on' : '' }}">
                <span class="dot"></span> Remitos
            </a>
            @endif
        </div>

        {{-- ══ VENTAS ══ --}}
        @if(in_array($rol, ['admin', 'ventas']))
        <button class="s-trigger {{ $ventasOn ? 'open' : '' }}" onclick="toggleGroup(this)">
            <span class="tl">Ventas</span><span class="s-arrow">›</span>
        </button>
        <div class="s-sub {{ $ventasOn ? 'open' : '' }}">
            <a href="{{ route('clientes.index') }}" class="s-item {{ request()->routeIs('clientes.*') ? 'on' : '' }}">
                <span class="dot"></span> Clientes
            </a>
            <a href="{{ route('presupuestos.index') }}" class="s-item {{ request()->routeIs('presupuestos.*') ? 'on' : '' }}">
                <span class="dot"></span> Presupuestos
            </a>
            <a href="{{ route('facturas.index') }}" class="s-item {{ request()->routeIs('facturas.*') ? 'on' : '' }}">
                <span class="dot"></span> Facturas
            </a>
            <a href="{{ route('remitos.index') }}" class="s-item {{ request()->routeIs('remitos.*') && !request()->routeIs('remito-cais.*') ? 'on' : '' }}">
                <span class="dot"></span> Remitos
            </a>
            <a href="{{ route('catalogo.index') }}" class="s-item {{ request()->routeIs('catalogo.*') ? 'on' : '' }}">
                <span class="dot"></span> Catálogo
            </a>
            @if($rol === 'admin')
            <a href="{{ route('listas-precios.index') }}" class="s-item {{ request()->routeIs('listas-precios.*') ? 'on' : '' }}">
                <span class="dot"></span> Listas de precios
            </a>
            @endif
        </div>

        {{-- ══ CONFIGURACIÓN ══ --}}
        <button class="s-trigger {{ $configOn ? 'open' : '' }}" onclick="toggleGroup(this)">
            <span class="tl">Configuración</span><span class="s-arrow">›</span>
        </button>
        <div class="s-sub {{ $configOn ? 'open' : '' }}">
            <a href="{{ route('tipo-trabajos.index') }}" class="s-item {{ request()->routeIs('tipo-trabajos.*') ? 'on' : '' }}">
                <span class="dot"></span> Tipos de trabajo
            </a>
            <a href="{{ route('materiales.index') }}" class="s-item {{ request()->routeIs('materiales.*') ? 'on' : '' }}">
                <span class="dot"></span> Materiales
            </a>
            <a href="{{ route('maquinas.index') }}" class="s-item {{ request()->routeIs('maquinas.*') ? 'on' : '' }}">
                <span class="dot"></span> Máquinas
            </a>
            @if($rol === 'admin')
            <a href="{{ route('remito-cais.index') }}" class="s-item {{ request()->routeIs('remito-cais.*') ? 'on' : '' }}">
                <span class="dot"></span> CAI remitos
            </a>
            @endif
        </div>
        @endif

        {{-- ══ SISTEMA + RRHH (solo admin) ══ --}}
        @if($rol === 'admin')
        <button class="s-trigger {{ $sistemaOn ? 'open' : '' }}" onclick="toggleGroup(this)">
            <span class="tl">Sistema</span><span class="s-arrow">›</span>
        </button>
        <div class="s-sub {{ $sistemaOn ? 'open' : '' }}">
            <a href="{{ route('usuarios.index') }}" class="s-item {{ request()->routeIs('usuarios.*') ? 'on' : '' }}">
                <span class="dot"></span> Usuarios
            </a>
            <a href="{{ route('configuracion.edit') }}" class="s-item {{ request()->routeIs('configuracion.*') ? 'on' : '' }}">
                <span class="dot"></span> Configuración
            </a>
        </div>

        <button class="s-trigger {{ $rrhhOn ? 'open' : '' }}" onclick="toggleGroup(this)">
            <span class="tl">RRHH</span><span class="s-arrow">›</span>
        </button>
        <div class="s-sub {{ $rrhhOn ? 'open' : '' }}">
            <a href="{{ route('rrhh.dashboard') }}" class="s-item {{ request()->routeIs('rrhh.dashboard') ? 'on' : '' }}">
                <span class="dot"></span> Panel
            </a>
            <a href="{{ route('rrhh.empleados.index') }}" class="s-item {{ request()->routeIs('rrhh.empleados.*') ? 'on' : '' }}">
                <span class="dot"></span> Empleados
            </a>
            <a href="{{ route('rrhh.fichadas.index') }}" class="s-item {{ request()->routeIs('rrhh.fichadas.*') ? 'on' : '' }}">
                <span class="dot"></span> Fichadas
            </a>
            <a href="{{ route('fichar.form') }}" target="_blank" class="s-item">
                <span class="dot"></span> Reloj tablet ↗
            </a>
        </div>
        @endif

    </nav>
    <div class="s-user">
        <div class="s-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
        <div>
            <div class="s-uname">{{ auth()->user()->name }}</div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="s-logout">Cerrar sesión</button>
            </form>
        </div>
    </div>
</aside>

<div id="main">
    <div class="topbar">
        <div style="display:flex;align-items:center;gap:12px">
            <button class="s-toggle" onclick="document.getElementById('sidebar').classList.toggle('open')">☰</button>
            <div class="topbar-bc">
                <span>{{ config('app.name') }}</span>
                <span>›</span>
                <strong>@yield('page-title', 'Dashboard')</strong>
            </div>
        </div>
        <div>@yield('topbar-actions')</div>
    </div>

    <div class="page-content">
        @if(session('success'))
            <div class="flash flash-ok">✓ &nbsp;{{ session('success') }}</div>
        @endif
        @if(session('ok'))
            <div class="flash flash-ok">✓ &nbsp;{{ session('ok') }}</div>
        @endif
        @if(session('error'))
            <div class="flash flash-err">✕ &nbsp;{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="flash flash-err">
                <div>
                @foreach($errors->all() as $e)
                    <div>✕ &nbsp;{{ $e }}</div>
                @endforeach
                </div>
            </div>
        @endif

        {{-- Alerta global de CAI de remito (solo admin) --}}
        @if($rol === 'admin' && !request()->routeIs('remito-cais.*'))
        @php
            $caiVigente = \App\Models\RemitoCai::vigente();
        @endphp
        @if(!$caiVigente)
            <div class="flash flash-err" style="font-size:12px">
                ⚠️ <strong>Sin CAI vigente para remitos.</strong>
                Los remitos que emitas no tendrán número fiscal.
                <a href="{{ route('remito-cais.create') }}" style="color:inherit;text-decoration:underline">Cargar CAI →</a>
            </div>
        @elseif($caiVigente->casiAgotado())
            <div class="flash flash-warn" style="font-size:12px;background:rgba(245,158,11,.15);border-color:#f59e0b;color:#f59e0b">
                ⚠️ <strong>CAI casi agotado:</strong> quedan {{ $caiVigente->restantes() }} números.
                <a href="{{ route('remito-cais.create') }}" style="color:inherit;text-decoration:underline">Cargar nuevo CAI →</a>
            </div>
        @endif
        @endif

        @yield('content')
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
function toggleGroup(btn) {
    btn.classList.toggle('open');
    btn.nextElementSibling.classList.toggle('open');
}
</script>
@yield('scripts')
</body>
</html>

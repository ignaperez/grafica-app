<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Fichada - Gráfica</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: #0d0d0d;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .wrap { width: 100%; max-width: 480px; padding: 20px; }
        .card {
            background: #1c1c1c;
            border-radius: 18px;
            padding: 24px;
            box-shadow: 0 12px 35px rgba(0,0,0,.6);
        }
        h1 { text-align: center; margin: 0 0 10px; }
        .hora { text-align: center; font-size: 1.5rem; opacity: .85; margin-bottom: 16px; }

        .msg {
            margin-bottom: 14px;
            padding: 10px 12px;
            border-radius: 10px;
            font-size: .9rem;
        }
        .msg.ok  { background:#033b0a; border:1px solid #3ddc84; }
        .msg.err { background:#3b0c0c; border:1px solid #ff5a5a; }

        label { display:block; margin-bottom:6px; font-size:.9rem; opacity:.9; }
        input[type=text]{
            width:100%; padding:14px; border-radius:10px;
            border:1px solid #333; background:#000; color:#fff;
            font-size:1.1rem; text-align:center; margin-bottom:14px;
        }

        .tip { text-align:center; font-size:.8rem; opacity:.7; margin-bottom:14px; }

        .tipo-group{ display:flex; gap:8px; margin-bottom:18px; }
        .tipo-btn{
            flex:1; padding:10px; border-radius:10px;
            border:1px solid #333; background:#202020; text-align:center;
            cursor:pointer; user-select:none;
        }
        .tipo-btn.selected{ border-color:#3ddc84; background:#022f12; }

        button{
            width:100%; padding:14px; border-radius:999px;
            border:none; font-size:1.1rem; font-weight:700;
            background:#3ddc84; color:#000; cursor:pointer;
        }

        #reader{ margin-top:18px; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">

        <h1>Fichada</h1>
        <div class="hora" id="clock">--:--</div>

        @if(session('ok'))
            <div class="msg ok">{{ session('ok') }}</div>
        @endif

        @if(session('error'))
            <div class="msg err">{{ session('error') }}</div>
        @endif

        @if($errors->any())
            <div class="msg err">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('fichar.store') }}">
            @csrf

            <label for="codigo">Código / QR</label>
            <input
                type="text"
                id="codigo"
                name="codigo"
                autocomplete="off"
                value="{{ old('codigo') }}"
                autofocus
            >

            <div class="tip">
                Escaneá tu <strong>QR</strong> o escribí tu <strong>PIN</strong>.
            </div>

            <input type="hidden" name="tipo" id="tipo-input" value="{{ old('tipo', 'entrada') }}">

            <div class="tipo-group" id="tipo-group">
                <div class="tipo-btn" data-tipo="entrada">ENTRADA</div>
                <div class="tipo-btn" data-tipo="salida">SALIDA</div>
                <div class="tipo-btn" data-tipo="pausa_inicio">INICIO PAUSA</div>
                <div class="tipo-btn" data-tipo="pausa_fin">FIN PAUSA</div>
            </div>

            <button type="submit">FICHAR</button>
        </form>

        <div class="tip" style="margin-top:14px;">
            Si querés, activá el lector de QR debajo.
        </div>

        <div id="reader"></div>

    </div>
</div>

<script>
    // reloj
    const clock = document.getElementById('clock');
    function tick() {
        const d = new Date();
        clock.textContent =
            d.toLocaleTimeString('es-AR', { hour: '2-digit', minute: '2-digit' });
    }
    setInterval(tick, 1000); tick();

    // selección de tipo
    const tipoInput = document.getElementById('tipo-input');
    const buttons = document.querySelectorAll('.tipo-btn');

    function refreshButtons() {
        buttons.forEach(b =>
            b.classList.toggle('selected', b.dataset.tipo === tipoInput.value)
        );
    }
    buttons.forEach(b => b.addEventListener('click', () => {
        tipoInput.value = b.dataset.tipo;
        refreshButtons();
    }));
    refreshButtons();
</script>

<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
    if (window.Html5Qrcode) {
        const scanner = new Html5Qrcode("reader");
        
        Html5Qrcode.getCameras().then(cameras => {
            if (!cameras?.length) return;
            scanner.start(
                cameras[0].id,
                { fps: 10, qrbox: 220 },
                decodedText => {
                    document.getElementById('codigo').value = decodedText;
                }
            );
        }).catch(console.error);
    }
</script>
</body>
</html>

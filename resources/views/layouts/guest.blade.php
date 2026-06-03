<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Gráfica') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        :root{--ac:#e6502a;--mono:'DM Mono',monospace;--sans:'DM Sans',sans-serif}
        html,body{height:100%;background:#0f0f0f;font-family:var(--sans);font-size:14px}
        .login-wrap{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}
        .login-box{width:100%;max-width:380px}
        .login-logo{text-align:center;margin-bottom:28px}
        .login-logo img{max-width:200px;max-height:160px;width:auto;height:auto;display:inline-block}
        .login-mark{font-family:var(--mono);font-size:11px;letter-spacing:3px;text-transform:uppercase;color:var(--ac);margin-bottom:6px}
        .login-name{font-size:22px;font-weight:500;color:#e8e4dc;letter-spacing:-.5px}
        .login-card{background:#141414;border:1px solid #1e1e1e;border-radius:14px;padding:32px}
        .login-title{font-size:15px;font-weight:500;color:#e8e4dc;margin-bottom:24px}
        .fg{margin-bottom:16px}
        .flabel{display:block;font-size:10px;letter-spacing:1px;text-transform:uppercase;color:#555;margin-bottom:6px}
        .finput{width:100%;background:#0a0a0a;border:1px solid #2a2a2a;border-radius:7px;padding:10px 13px;font-size:13.5px;color:#e8e4dc;font-family:var(--sans);outline:none;transition:border-color .12s}
        .finput:focus{border-color:var(--ac)}
        .finput::placeholder{color:#333}
        .fcheck{display:flex;align-items:center;gap:8px;font-size:13px;color:#555;cursor:pointer}
        .fcheck input{accent-color:var(--ac)}
        .fbtn{width:100%;background:var(--ac);color:#fff;border:none;border-radius:7px;padding:11px;font-size:14px;font-weight:500;font-family:var(--sans);cursor:pointer;transition:background .12s;margin-top:8px}
        .fbtn:hover{background:#cc4424}
        .flink{font-size:12px;color:#444;text-decoration:none;transition:color .12s}
        .flink:hover{color:var(--ac)}
        .ferr{font-size:12px;color:#e05555;margin-top:4px}
        .frow{display:flex;align-items:center;justify-content:space-between;margin-top:18px}
    </style>
</head>
<body>
<div class="login-wrap">
    <div class="login-box">
        <div class="login-logo">
            <img src="{{ asset('images/logo.png') }}" alt="Plote.ar Gráfica">
        </div>
        <div class="login-card">
            {{ $slot }}
        </div>
    </div>
</div>
<script>
(function(){
    var s=document.createElement('style');
    s.textContent='.pw-wrap{position:relative;display:block}.pw-wrap input{padding-right:36px!important}.pw-eye{position:absolute;right:9px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#666;padding:2px;line-height:0;transition:color .15s}.pw-eye:hover{color:#e6502a}';
    document.head.appendChild(s);
    var eye='<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
    var eyeOff='<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>';
    document.querySelectorAll('input[type="password"]').forEach(function(inp){
        var p=inp.parentNode,w=document.createElement('div');
        w.className='pw-wrap';p.insertBefore(w,inp);w.appendChild(inp);
        var b=document.createElement('button');
        b.type='button';b.className='pw-eye';b.title='Mostrar/ocultar';b.innerHTML=eye;
        b.addEventListener('click',function(){var v=inp.type==='password';inp.type=v?'text':'password';b.innerHTML=v?eyeOff:eye;});
        w.appendChild(b);
    });
})();
</script>
</body>
</html>

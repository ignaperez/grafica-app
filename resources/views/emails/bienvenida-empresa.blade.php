<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Bienvenido a plote.ar</title>
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body { background:#f4f4f4; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; color:#1a1a1a; }
  .wrap { max-width:600px; margin:40px auto; background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 2px 12px rgba(0,0,0,.08); }

  /* Header */
  .hd { background:#0a0a0a; padding:32px 40px; text-align:center; }
  .hd .brand { font-size:2rem; font-weight:900; letter-spacing:-.02em; color:#fff; font-family:monospace; }
  .hd .brand span { color:#e6502a; }
  .hd .tagline { color:#666; font-size:.82rem; margin-top:4px; letter-spacing:.08em; text-transform:uppercase; }

  /* Body */
  .bd { padding:40px; }
  .bd h1 { font-size:1.3rem; font-weight:700; margin-bottom:8px; }
  .bd .empresa { color:#e6502a; }
  .bd p { color:#555; font-size:.95rem; line-height:1.6; margin-bottom:20px; }

  /* Login URL */
  .login-box { background:#f8f8f8; border:2px solid #e6502a; border-radius:6px; padding:18px 22px; margin:24px 0; text-align:center; }
  .login-box .label { font-size:.72rem; text-transform:uppercase; letter-spacing:.1em; color:#888; margin-bottom:6px; }
  .login-box a { font-size:1.1rem; font-weight:700; color:#e6502a; text-decoration:none; word-break:break-all; }

  /* Users table */
  .section-title { font-size:.72rem; text-transform:uppercase; letter-spacing:.1em; color:#888; font-weight:600; margin:28px 0 10px; }
  table { width:100%; border-collapse:collapse; font-size:.88rem; }
  th { text-align:left; padding:9px 12px; background:#f0f0f0; font-size:.72rem; text-transform:uppercase; letter-spacing:.08em; color:#666; border-bottom:2px solid #e0e0e0; }
  td { padding:11px 12px; border-bottom:1px solid #f0f0f0; vertical-align:middle; }
  tr:last-child td { border-bottom:none; }
  .rol-badge { display:inline-block; padding:2px 8px; border-radius:3px; font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; }
  .rol-admin { background:#fff3e0; color:#e65100; }
  .rol-ventas { background:#e8f5e9; color:#2e7d32; }
  .rol-produccion { background:#e3f2fd; color:#1565c0; }
  .pwd { font-family:monospace; font-size:.9rem; background:#f8f8f8; padding:3px 7px; border-radius:3px; border:1px solid #e0e0e0; color:#1a1a1a; letter-spacing:.05em; }

  /* Alert */
  .alert { background:#fff8e1; border-left:4px solid #ffc107; padding:14px 18px; border-radius:0 4px 4px 0; margin:24px 0; font-size:.88rem; color:#5d4037; line-height:1.5; }
  .alert strong { color:#e65100; }

  /* Footer */
  .ft { background:#f8f8f8; border-top:1px solid #ebebeb; padding:22px 40px; text-align:center; font-size:.78rem; color:#999; line-height:1.6; }
  .ft a { color:#e6502a; text-decoration:none; }
</style>
</head>
<body>
<div class="wrap">

  <!-- Header -->
  <div class="hd">
    <div class="brand">plote<span>.ar</span></div>
    <div class="tagline">Sistema de gestión gráfica</div>
  </div>

  <!-- Body -->
  <div class="bd">
    <h1>¡Bienvenido, <span class="empresa">{{ $nombreEmpresa }}</span>!</h1>
    <p>Tu empresa fue registrada en el sistema de plote.ar. A continuación encontrás las credenciales de acceso para los tres usuarios de tu cuenta.</p>

    <!-- Login URL -->
    <div class="login-box">
      <div class="label">URL de acceso a tu panel</div>
      <a href="{{ $loginUrl }}">{{ $loginUrl }}</a>
    </div>

    <!-- Usuarios -->
    <div class="section-title">Credenciales de acceso</div>
    <table>
      <thead>
        <tr>
          <th>Rol</th>
          <th>Email (usuario)</th>
          <th>Contraseña</th>
        </tr>
      </thead>
      <tbody>
        @foreach($usuarios as $u)
        <tr>
          <td>
            <span class="rol-badge rol-{{ $u['rol'] }}">{{ ucfirst($u['rol']) }}</span>
          </td>
          <td>{{ $u['email'] }}</td>
          <td><span class="pwd">{{ $u['password'] }}</span></td>
        </tr>
        @endforeach
      </tbody>
    </table>

    <!-- Aviso seguridad -->
    <div class="alert">
      <strong>Importante:</strong> Cambiá las contraseñas la primera vez que ingreses.
      Cada usuario puede actualizarla desde su perfil una vez logueado.
      Guardá este email en un lugar seguro — no se vuelve a enviar.
    </div>

    <p style="margin-bottom:0">
      Si tenés dudas, respondé este correo o escribinos por
      <a href="https://wa.me/5493410000000" style="color:#e6502a">WhatsApp</a>.
    </p>
  </div>

  <!-- Footer -->
  <div class="ft">
    <strong>plote.ar</strong> — Gráfica integral · Victoria, San Fernando · Buenos Aires<br>
    Carlos Casares 2088 · <a href="mailto:hola@plote.ar">hola@plote.ar</a><br><br>
    Este es un correo automático. No lo reenvíes a terceros.
  </div>

</div>
</body>
</html>

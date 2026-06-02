<!DOCTYPE html>
<html lang="es-AR">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>plote.ar — Gráfica integral · Ploteo vehicular, gran formato y cartelería</title>
<meta name="description" content="plote.ar — gráfica integral. Ploteo vehicular, impresión en gran formato, cartelería, letras corpóreas y stickers." />
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=Anton&family=Archivo:wght@400;500;600;700;900&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet" />
<link rel="stylesheet" href="{{ asset('landing/styles.css') }}" />
</head>
<body>

<header class="site-header">
  <div class="wrap header-inner">
    <a class="logo" href="#top" aria-label="plote.ar inicio">
      <img src="{{ asset('landing/assets/logo.png') }}" alt="plote.ar — gráfica" />
    </a>
    <nav class="nav" id="nav">
      <a href="#servicios">Servicios</a>
      <a href="#galeria">Galería</a>
      <a href="#contacto">Contacto</a>
    </nav>
    <div style="display:flex; align-items:center; gap:12px;">
      <a class="login-btn" href="{{ $loginUrl }}" title="Acceso empleados — back office">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><rect x="4" y="11" width="16" height="9" rx="0"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>
        Empleados
      </a>
      <button class="burger" id="burger" aria-label="Menú">≡</button>
    </div>
  </div>
</header>

<main id="top">

  <section class="hero">
    <div class="wrap hero-inner">
      <div class="hero-top">
        <div class="hero-text">
          <p class="kicker reveal">Gráfica integral · Victoria, San Fernando · Buenos Aires</p>
          <h1 class="reveal">
            <span class="l-c">PLOTE</span><span class="l-m">AMOS</span><br />
            <span class="stroke">TODO LO</span><br />
            <span class="l-y">QUE IMAGINES</span>
          </h1>
        </div>
        <img class="hero-logo reveal" src="{{ asset('landing/assets/logo.png') }}" alt="plote.ar — gráfica integral" />
      </div>
      <div class="hero-sub">
        <p class="reveal">
          Convertimos tu marca en algo que se ve a la distancia. <b>Ploteo vehicular, gran formato, cartelería, letras corpóreas y stickers</b> — diseño, producción y colocación, todo en un mismo lugar.
        </p>
        <div class="hero-cta reveal">
          <a class="btn" href="#contacto">Pedir presupuesto →</a>
          <a class="btn alt" href="#galeria">Ver trabajos</a>
        </div>
      </div>
    </div>
    <div class="marquee" aria-hidden="true">
      <div class="marquee-track">
        <span>Ploteo Vehicular</span><span>Gran Formato</span><span>Cartelería</span><span>Letras Corpóreas</span><span>Stickers</span><span>Diseño Gráfico</span>
        <span>Ploteo Vehicular</span><span>Gran Formato</span><span>Cartelería</span><span>Letras Corpóreas</span><span>Stickers</span><span>Diseño Gráfico</span>
      </div>
    </div>
  </section>

  <section id="servicios">
    <div class="wrap">
      <div class="section-head reveal">
        <h2>Qué<br />hacemos</h2>
        <span class="idx">[ 01 ] — SERVICIOS</span>
      </div>
      <div class="services-grid reveal">
        <article class="service" style="--accent: var(--c);">
          <span class="mk"></span><span class="num">01</span>
          <h3>Ploteo<br />Vehicular</h3>
          <p>Rotulado de autos, camionetas, flotas y unidades comerciales. Vinilos de alta duración, recortes y wrapping total.</p>
          <div class="tags"><span>Flotas</span><span>Wrapping</span><span>Vinilo</span></div>
        </article>
        <article class="service" style="--accent: var(--m);">
          <span class="mk"></span><span class="num">02</span>
          <h3>Gran<br />Formato</h3>
          <p>Impresión de banners, lonas, pasacalles y backings en alta resolución para interior y exterior.</p>
          <div class="tags"><span>Lonas</span><span>Banners</span><span>Backings</span></div>
        </article>
        <article class="service" style="--accent: var(--y);">
          <span class="mk"></span><span class="num">03</span>
          <h3>Carte<br />lería</h3>
          <p>Carteles frontales, tótems, señalética y chapas. Diseño, fabricación y montaje llave en mano.</p>
          <div class="tags"><span>Señalética</span><span>Tótems</span><span>Montaje</span></div>
        </article>
        <article class="service" style="--accent: var(--m);">
          <span class="mk"></span><span class="num">04</span>
          <h3>Letras<br />Corpóreas</h3>
          <p>Letras y logos en volumen: acrílico, PVC, acero y luminosos con LED para fachadas que se imponen.</p>
          <div class="tags"><span>Acrílico</span><span>LED</span><span>Fachadas</span></div>
        </article>
        <article class="service" style="--accent: var(--c);">
          <span class="mk"></span><span class="num">05</span>
          <h3>Stickers</h3>
          <p>Troquelados, etiquetas, calcos y packaging. Tiradas chicas o grandes, con corte a medida.</p>
          <div class="tags"><span>Troquel</span><span>Etiquetas</span><span>Packaging</span></div>
        </article>
        <article class="service" style="--accent: var(--y);">
          <span class="mk"></span><span class="num">06</span>
          <h3>Diseño<br />Gráfico</h3>
          <p>¿No tenés el arte? Lo creamos. Identidad, adaptación de logos y diseño listo para producción.</p>
          <div class="tags"><span>Identidad</span><span>Arte final</span><span>Branding</span></div>
        </article>
      </div>
    </div>
  </section>

  <section id="galeria">
    <div class="wrap">
      <div class="section-head reveal">
        <h2>Trabajos</h2>
        <span class="idx">[ 02 ] — GALERÍA</span>
      </div>
      <div class="gallery-grid reveal">
        <div class="tile wide" data-cap="Ploteo vehicular — flota" style="--accent: var(--c);">
          <image-slot id="g1" placeholder="Arrastrá una foto de ploteo vehicular"></image-slot>
          <span class="cap">Ploteo vehicular</span>
        </div>
        <div class="tile tall" data-cap="Letras corpóreas iluminadas" style="--accent: var(--m);">
          <image-slot id="g2" placeholder="Arrastrá una foto"></image-slot>
          <span class="cap">Letras corpóreas</span>
        </div>
        <div class="tile" data-cap="Cartelería frontal" style="--accent: var(--y);">
          <image-slot id="g3" placeholder="Arrastrá una foto"></image-slot>
          <span class="cap">Cartelería</span>
        </div>
        <div class="tile" data-cap="Gran formato — lona" style="--accent: var(--c);">
          <image-slot id="g4" placeholder="Arrastrá una foto"></image-slot>
          <span class="cap">Gran formato</span>
        </div>
        <div class="tile" data-cap="Stickers troquelados" style="--accent: var(--m);">
          <image-slot id="g5" placeholder="Arrastrá una foto"></image-slot>
          <span class="cap">Stickers</span>
        </div>
        <div class="tile tall" data-cap="Wrapping total" style="--accent: var(--y);">
          <image-slot id="g6" placeholder="Arrastrá una foto"></image-slot>
          <span class="cap">Wrapping</span>
        </div>
        <div class="tile wide" data-cap="Señalética y tótems" style="--accent: var(--c);">
          <image-slot id="g7" placeholder="Arrastrá una foto"></image-slot>
          <span class="cap">Señalética</span>
        </div>
        <div class="tile" data-cap="Vidriera" style="--accent: var(--m);">
          <image-slot id="g8" placeholder="Arrastrá una foto"></image-slot>
          <span class="cap">Vidrieras</span>
        </div>
      </div>
      <p class="gallery-note">↑ Arrastrá tus propias fotos a cada recuadro · clic para ampliar</p>
    </div>
  </section>

  <section id="contacto">
    <div class="wrap">
      <div class="section-head reveal">
        <h2>Hablemos</h2>
        <span class="idx">[ 03 ] — CONTACTO</span>
      </div>
      <div class="contact-grid reveal">
        <form class="contact-form" id="contactForm" novalidate>
          <h3>Pedí tu presupuesto</h3>
          <p class="sub">Contanos qué necesitás. Te respondemos en el día.</p>
          <div class="field">
            <label for="nombre">Nombre / Empresa</label>
            <input type="text" id="nombre" name="nombre" placeholder="Tu nombre o el de tu negocio" />
            <span class="err">Completá este campo.</span>
          </div>
          <div class="field row">
            <div class="field" style="margin:0;">
              <label for="email">Email</label>
              <input type="email" id="email" name="email" placeholder="vos@email.com" />
              <span class="err">Email inválido.</span>
            </div>
            <div class="field" style="margin:0;">
              <label for="tel">WhatsApp / Teléfono</label>
              <input type="tel" id="tel" name="tel" placeholder="+54 9 341 ..." />
              <span class="err">Completá este campo.</span>
            </div>
          </div>
          <div class="field">
            <label for="servicio">Servicio que te interesa</label>
            <select id="servicio" name="servicio">
              <option value="">Elegí un servicio…</option>
              <option>Ploteo Vehicular</option>
              <option>Gran Formato</option>
              <option>Cartelería</option>
              <option>Letras Corpóreas</option>
              <option>Stickers</option>
              <option>Diseño Gráfico</option>
              <option>Otro / Varios</option>
            </select>
            <span class="err">Elegí una opción.</span>
          </div>
          <div class="field">
            <label for="mensaje">Mensaje</label>
            <textarea id="mensaje" name="mensaje" placeholder="Medidas, cantidades, plazos, lo que tengas en mente…"></textarea>
            <span class="err">Contanos un poco más.</span>
          </div>
          <button class="btn full" type="submit">Enviar consulta →</button>
          <div class="form-success" id="formSuccess">✓ ¡Recibido! Te contactamos a la brevedad.</div>
        </form>

        <aside class="contact-info">
          <div class="info-item">
            <div class="lab">WhatsApp</div>
            <a class="val" href="https://wa.me/5493410000000" target="_blank" rel="noopener">+54 9 341 000 0000</a>
          </div>
          <div class="info-item">
            <div class="lab">Email</div>
            <a class="val" href="mailto:hola@plote.ar">hola@plote.ar</a>
          </div>
          <div class="info-item">
            <div class="lab">Taller / Showroom</div>
            <div class="val">Carlos Casares 2088 · Victoria, San Fernando</div>
          </div>
          <div class="info-item">
            <div class="lab">Horarios</div>
            <div class="val">Lun a Vie · 9 a 18 h</div>
          </div>
          <div class="socials">
            <a href="#" target="_blank" rel="noopener">Instagram</a>
            <a href="#" target="_blank" rel="noopener">Facebook</a>
          </div>
        </aside>
      </div>
    </div>
  </section>

</main>

<footer class="site-footer">
  <div class="wrap">
    <div class="footer-logo">plote<span class="dot">.</span><span class="ar">ar</span></div>
    <div class="footer-bar">
      <span>© {{ date('Y') }} plote.ar — Gráfica integral</span>
      <nav class="links">
        <a href="#servicios">Servicios</a>
        <a href="#galeria">Galería</a>
        <a href="#contacto">Contacto</a>
        <a href="{{ $loginUrl }}" class="footer-login">· Acceso empleados</a>
      </nav>
    </div>
  </div>
</footer>

<a class="wa-float" href="https://wa.me/5493410000000" target="_blank" rel="noopener" aria-label="Escribinos por WhatsApp">
  <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 0 0-8.6 15l-1.3 4.8 4.9-1.3A10 10 0 1 0 12 2zm0 2a8 8 0 1 1-4.1 14.9l-.3-.2-2.9.8.8-2.8-.2-.3A8 8 0 0 1 12 4zm-2.7 3.6c-.2 0-.5 0-.7.3-.3.3-1 1-1 2.4s1 2.8 1.2 3 .2.3.3.5c.6 1 1.6 1.9 2.8 2.4 1.6.6 1.9.5 2.3.5s1.2-.5 1.4-1c.2-.5.2-.9.1-1l-.6-.3s-1-.5-1.2-.6c-.2-.1-.3-.1-.5.1l-.6.8c-.1.2-.3.2-.5.1s-.9-.3-1.7-1c-.6-.6-1-1.3-1.2-1.5s0-.4.1-.5l.4-.4.2-.4v-.4l-.7-1.6c-.1-.3-.3-.3-.4-.3z"/></svg>
  WhatsApp
</a>

<div class="modal-overlay" id="lightbox">
  <div style="max-width:90vw; max-height:90vh; position:relative;">
    <button class="x lb-close" style="position:absolute; top:-14px; right:-14px; z-index:2; background:var(--m); color:var(--black); border:3px solid var(--ink); width:40px; height:40px; font-size:1.1rem;">✕</button>
    <img id="lightboxImg" alt="" style="max-width:90vw; max-height:80vh; border:3px solid var(--ink);" />
    <div id="lightboxCap" class="mono" style="background:var(--black); color:var(--ink); padding:10px 14px; border:3px solid var(--ink); border-top:none; font-size:0.78rem; letter-spacing:0.08em; text-transform:uppercase;"></div>
  </div>
</div>

<script src="{{ asset('landing/image-slot.js') }}"></script>
<script src="{{ asset('landing/script.js') }}"></script>

</body>
</html>

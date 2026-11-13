{{--
    Visor de imágenes (lightbox) reutilizable.

    Uso: incluir una vez por pantalla —@include('partials._lightbox')— y marcar
    cada miniatura CLICKEABLE con:

        <a href="{{ $url }}" data-lb-src="{{ $url }}" data-lb-cap="nombre.jpg"> … </a>

    Todas las marcadas de la página forman UNA galería, en el orden del DOM, y se
    recorren con ← → o con las flechas de pantalla. Se enganchan por delegación,
    así que sirve igual si las miniaturas se agregan después por JS.

    El `href` se deja a propósito: sin JS, o con clic del medio / "abrir en pestaña
    nueva", el archivo se sigue abriendo como antes. El clic normal lo intercepta
    el visor.

    Solo IMÁGENES: un PDF o un .ai no se puede mostrar acá, esas miniaturas no
    llevan `data-lb-src` y siguen abriendo en pestaña nueva.
--}}

<div id="lightbox" class="lb" role="dialog" aria-modal="true" aria-label="Visor de imágenes" hidden>
    <button type="button" class="lb-btn lb-close" data-lb-close title="Cerrar (Esc)" aria-label="Cerrar">&times;</button>
    <button type="button" class="lb-btn lb-prev"  data-lb-mover="-1" title="Anterior (←)" aria-label="Anterior">&#8249;</button>
    <button type="button" class="lb-btn lb-next"  data-lb-mover="1"  title="Siguiente (→)" aria-label="Siguiente">&#8250;</button>

    <img id="lb-img" class="lb-img" src="" alt="">
    <div class="lb-cap"><span id="lb-nombre"></span> <span id="lb-contador" class="lb-contador"></span></div>
</div>

<style>
.lb{position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.93);
    display:flex;align-items:center;justify-content:center}
.lb[hidden]{display:none}
.lb-img{max-width:88vw;max-height:82vh;object-fit:contain;border-radius:8px;
        box-shadow:0 12px 50px rgba(0,0,0,.6)}

.lb-cap{position:absolute;bottom:18px;left:50%;transform:translateX(-50%);
        max-width:80vw;text-align:center;color:#e8e4dc;font-size:12.5px;
        font-family:var(--sans,sans-serif);overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.lb-contador{color:#8e8e8e;font-family:var(--mono,monospace);margin-left:6px}

/* Botones siempre visibles (el usuario los pidió en pantalla, no al pasar el mouse) */
.lb-btn{position:absolute;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.25);
        color:#fff;cursor:pointer;border-radius:50%;line-height:1;padding:0;
        display:flex;align-items:center;justify-content:center;
        transition:background .12s,border-color .12s}
.lb-btn:hover{background:rgba(255,255,255,.22);border-color:rgba(255,255,255,.5)}
.lb-close{top:18px;right:18px;width:42px;height:42px;font-size:26px}
.lb-prev,.lb-next{top:50%;margin-top:-26px;width:52px;height:52px;font-size:34px}
.lb-prev{left:18px}
.lb-next{right:18px}
/* Con una sola imagen las flechas no tienen a dónde ir */
.lb.lb-sola .lb-prev,.lb.lb-sola .lb-next{display:none}

@media (max-width:640px){
    .lb-img{max-width:94vw;max-height:74vh}
    .lb-close{width:38px;height:38px;font-size:23px;top:12px;right:12px}
    .lb-prev,.lb-next{width:44px;height:44px;font-size:29px;margin-top:-22px}
    .lb-prev{left:8px}
    .lb-next{right:8px}
}
</style>

<script>
(function () {
    const caja     = document.getElementById('lightbox');
    const img      = document.getElementById('lb-img');
    const nombre   = document.getElementById('lb-nombre');
    const contador = document.getElementById('lb-contador');

    let galeria = [];   // los <a> con data-lb-src, en orden del DOM
    let i       = 0;

    const abierto = () => !caja.hidden;

    function mostrar(n) {
        if (!galeria.length) return;
        // Da la vuelta: del último pasa al primero y al revés.
        i = (n + galeria.length) % galeria.length;
        const el = galeria[i];
        img.src   = el.dataset.lbSrc;
        img.alt   = el.dataset.lbCap || '';
        nombre.textContent   = el.dataset.lbCap || '';
        contador.textContent = galeria.length > 1 ? (i + 1) + ' / ' + galeria.length : '';
        caja.classList.toggle('lb-sola', galeria.length < 2);
    }

    function abrir(el) {
        // La galería se arma al abrir, no al cargar: si la pantalla agregó
        // miniaturas por JS, entran igual.
        galeria = [...document.querySelectorAll('[data-lb-src]')];
        mostrar(galeria.indexOf(el));
        caja.hidden = false;
        document.body.style.overflow = 'hidden';   // que no scrollee la página de atrás
    }

    function cerrar() {
        caja.hidden = true;
        img.src = '';
        document.body.style.overflow = '';
    }

    // Delegación: sirve para miniaturas que aún no existen al cargar la página.
    document.addEventListener('click', function (e) {
        const disparador = e.target.closest('[data-lb-src]');
        if (disparador) {
            // Clic del medio o con modificador → que el navegador haga lo suyo
            // (abrir en pestaña nueva), como antes.
            if (e.metaKey || e.ctrlKey || e.shiftKey || e.button !== 0) return;
            e.preventDefault();
            abrir(disparador);
            return;
        }

        if (!abierto()) return;
        if (e.target.closest('[data-lb-close]')) return cerrar();

        const mover = e.target.closest('[data-lb-mover]');
        if (mover) return mostrar(i + Number(mover.dataset.lbMover));

        // Clic en el fondo (no en la imagen ni en un botón) → cerrar.
        if (e.target === caja) cerrar();
    });

    document.addEventListener('keydown', function (e) {
        if (!abierto()) return;   // con el visor cerrado no se tocan las flechas de la página
        if (e.key === 'Escape')     { e.preventDefault(); cerrar(); }
        if (e.key === 'ArrowLeft')  { e.preventDefault(); mostrar(i - 1); }
        if (e.key === 'ArrowRight') { e.preventDefault(); mostrar(i + 1); }
    });
})();
</script>

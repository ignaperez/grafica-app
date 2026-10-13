/* ============================================================
   plote.ar — interacciones de la landing
   ============================================================ */
(function () {
  "use strict";

  /* ---------- Placeholders de galería (rayado on-brand) ---------- */
  // El image-slot trae placeholder oscuro pensado para fondos claros; sobre
  // negro no se ve. Le damos a cada slot un src de respaldo: una textura
  // rayada con la etiqueta. Cuando el usuario arrastra una foto, la reemplaza.
  function makePlaceholder(accent, label) {
    var svg =
      "<svg xmlns='http://www.w3.org/2000/svg' width='120' height='120'>" +
      "<defs><pattern id='p' width='34' height='34' patternTransform='rotate(45)' patternUnits='userSpaceOnUse'>" +
      "<rect width='34' height='34' fill='#1b1b1b'/>" +
      "<rect width='17' height='34' fill='#262626'/>" +
      "<rect x='30' width='4' height='34' fill='" + accent + "' opacity='0.55'/></pattern></defs>" +
      "<rect width='120' height='120' fill='url(#p)'/>" +
      "</svg>";
    return "data:image/svg+xml;utf8," + encodeURIComponent(svg);
  }
  document.querySelectorAll(".tile").forEach(function (tile) {
    var slot = tile.querySelector("image-slot");
    if (!slot) return;
    var accent = getComputedStyle(tile).getPropertyValue("--accent").trim() || "#00e6f6";
    var cap = (tile.getAttribute("data-cap") || "").toUpperCase();
    slot.setAttribute("src", makePlaceholder(accent, cap.split(" — ")[0]));
  });

  /* ---------- Menú mobile ---------- */
  var burger = document.querySelector(".burger");
  var nav = document.getElementById("nav");
  if (burger && nav) {
    burger.addEventListener("click", function () {
      nav.classList.toggle("open");
    });
    nav.querySelectorAll("a").forEach(function (a) {
      a.addEventListener("click", function () { nav.classList.remove("open"); });
    });
  }

  /* ---------- Modal login (back office) ---------- */
  var overlay = document.getElementById("loginModal");
  var openers = document.querySelectorAll("[data-login]");
  var closer = document.getElementById("loginClose");
  var loginForm = document.getElementById("loginForm");
  var loginMsg = document.getElementById("loginMsg");

  function openModal() {
    if (!overlay) return;
    overlay.classList.add("show");
    var u = document.getElementById("usuario");
    if (u) setTimeout(function () { u.focus(); }, 60);
  }
  function closeModal() {
    if (!overlay) return;
    overlay.classList.remove("show");
    if (loginMsg) loginMsg.classList.remove("show");
  }
  openers.forEach(function (b) {
    b.addEventListener("click", function (e) { e.preventDefault(); openModal(); });
  });
  if (closer) closer.addEventListener("click", closeModal);
  if (overlay) overlay.addEventListener("click", function (e) {
    if (e.target === overlay) closeModal();
  });
  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") { closeModal(); closeLightbox(); }
  });
  if (loginForm) {
    loginForm.addEventListener("submit", function (e) {
      e.preventDefault();
      // Prototipo: el ingreso real lo maneja la app del back office.
      if (loginMsg) {
        loginMsg.textContent = "Acceso de demo — conectar con la app del back office.";
        loginMsg.classList.add("show");
      }
    });
  }

  /* ---------- Form de contacto ---------- */
  var form = document.getElementById("contactForm");
  if (form) {
    var success = document.getElementById("formSuccess");
    var emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    function setInvalid(field, bad) {
      var wrap = field.closest(".field");
      if (!wrap) return;
      wrap.classList.toggle("invalid", bad);
    }

    form.addEventListener("submit", function (e) {
      e.preventDefault();
      var ok = true;
      var nombre = form.querySelector("#nombre");
      var email = form.querySelector("#email");
      var servicio = form.querySelector("#servicio");
      var mensaje = form.querySelector("#mensaje");

      if (!nombre.value.trim()) { setInvalid(nombre, true); ok = false; } else setInvalid(nombre, false);
      if (!emailRe.test(email.value.trim())) { setInvalid(email, true); ok = false; } else setInvalid(email, false);
      if (!servicio.value) { setInvalid(servicio, true); ok = false; } else setInvalid(servicio, false);
      if (!mensaje.value.trim()) { setInvalid(mensaje, true); ok = false; } else setInvalid(mensaje, false);

      if (!ok) return;

      // Hasta acá el handler validaba, limpiaba el formulario y mostraba
      // "¡Recibido!" SIN enviar nada: los datos se descartaban en el navegador.
      // Ahora se postean de verdad, y recién con la respuesta del servidor se
      // limpia el formulario y se abre WhatsApp con la consulta ya escrita.
      var endpoint = form.getAttribute("data-endpoint");
      var btn = form.querySelector('button[type="submit"]');
      var textoBtn = btn ? btn.textContent : "";

      if (!endpoint) return;               // sin endpoint no se finge éxito

      if (btn) { btn.disabled = true; btn.textContent = "Enviando…"; }

      var datos = new FormData(form);

      fetch(endpoint, {
        method: "POST",
        headers: {
          "X-CSRF-TOKEN": form.getAttribute("data-csrf") || "",
          "X-Requested-With": "XMLHttpRequest",
          "Accept": "application/json"
        },
        body: datos
      })
        .then(function (r) {
          // 429 = tope por IP/día o el throttle de la ruta. Trae un mensaje
          // para mostrarle al visitante en vez del error genérico.
          if (r.status === 429) {
            return r.json().catch(function () { return {}; }).then(function (d) {
              var e = new Error("limite");
              e.mensaje = (d && d.mensaje) ||
                "Recibimos varias consultas desde esta conexión. Escribinos por WhatsApp.";
              throw e;
            });
          }
          if (!r.ok) throw new Error("HTTP " + r.status);
          return r.json();
        })
        .then(function (data) {
          form.reset();
          if (success) {
            success.classList.add("show");
            setTimeout(function () { success.classList.remove("show"); }, 10000);
          }

          // Y además por WhatsApp, para que quede por los dos canales.
          //
          // window.open() acá lo bloquean casi todos los navegadores: estamos
          // dentro del .then() de un fetch, así que el "gesto del usuario" del
          // clic ya se perdió y la ventana cuenta como popup no solicitado. Se
          // intenta igual (si pasa, mejor), pero la que vale es el botón: un
          // clic directo del usuario nunca se bloquea.
          if (data && data.whatsapp_url) {
            var win = null;
            try { win = window.open(data.whatsapp_url, "_blank", "noopener"); } catch (err) { win = null; }

            if (!win || win.closed || typeof win.closed === "undefined") {
              var link = document.getElementById("waLink");
              if (link) {
                link.href = data.whatsapp_url;
                link.style.display = "inline-block";
              }
            }
          }
        })
        .catch(function (e) {
          // No se limpia el formulario: lo escrito queda para reintentar.
          var err = document.getElementById("formError");
          if (err) {
            if (e && e.mensaje) err.textContent = "✕ " + e.mensaje;
            err.classList.add("show");
            setTimeout(function () { err.classList.remove("show"); }, 9000);
          }
        })
        .finally(function () {
          if (btn) { btn.disabled = false; btn.textContent = textoBtn; }
        });
    });

    // Limpiar error al tipear
    form.querySelectorAll("input, select, textarea").forEach(function (el) {
      el.addEventListener("input", function () {
        var w = el.closest(".field");
        if (w) w.classList.remove("invalid");
      });
    });
  }

  /* ---------- Lightbox de galería ---------- */
  var lb = document.getElementById("lightbox");
  var lbImg = document.getElementById("lightboxImg");
  var lbCap = document.getElementById("lightboxCap");

  function closeLightbox() { if (lb) lb.classList.remove("show"); }

  document.querySelectorAll(".tile").forEach(function (tile) {
    tile.addEventListener("click", function (e) {
      // dejar que image-slot maneje su propio drop/reframe
      var slot = tile.querySelector("image-slot");
      if (!slot || !lb) return;
      // Buscar imagen renderizada dentro del slot
      var inner = slot.shadowRoot ? slot.shadowRoot.querySelector("img") : null;
      var bg = inner ? inner.src : null;
      if (!bg || bg.indexOf("data:image/svg") === 0) return; // placeholder: no abrir
      lbImg.src = bg;
      lbCap.textContent = tile.getAttribute("data-cap") || "";
      lb.classList.add("show");
    });
  });
  if (lb) lb.addEventListener("click", function (e) {
    if (e.target === lb || e.target.classList.contains("lb-close")) closeLightbox();
  });

  /* ---------- Scroll reveal (resiliente a transiciones congeladas) ---------- */
  var reveals = Array.prototype.slice.call(document.querySelectorAll(".reveal"));

  // Muestra al instante, sin animación (para contenido ya visible al cargar)
  function snapIn(el) {
    el.style.transition = "none";
    el.classList.add("in");
    void el.offsetHeight;        // forzar reflow
    el.style.transition = "";
  }
  function showAllInstant() { reveals.forEach(snapIn); }

  if (!("IntersectionObserver" in window)) {
    showAllInstant();
  } else {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (en.isIntersecting) { en.target.classList.add("in"); io.unobserve(en.target); }
      });
    }, { threshold: 0, rootMargin: "0px 0px -8% 0px" });

    function initialPass() {
      var vh = window.innerHeight || document.documentElement.clientHeight;
      reveals.forEach(function (el) {
        var r = el.getBoundingClientRect();
        if (r.top < vh * 1.05) snapIn(el);   // arriba del fold: instantáneo
        else io.observe(el);                  // abajo: animado al hacer scroll
      });
    }
    initialPass();
    // Red de seguridad: cualquier .reveal que siga oculto, mostrarlo al instante
    // (defiende contra iframes sin scroll y transiciones congeladas)
    setTimeout(function () {
      reveals.forEach(function (el) { if (!el.classList.contains("in")) snapIn(el); });
    }, 1500);
  }

  /* ---------- Año dinámico ---------- */
  var y = document.getElementById("year");
  if (y) y.textContent = new Date().getFullYear();
})();

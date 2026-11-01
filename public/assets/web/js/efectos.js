/* =====================================================================
   efectos.js · Base de movimiento del sitio público (sin dependencias).
   Se carga con defer. Hace dos cosas:
   1. Revelados al entrar en pantalla ([data-reveal], ver efectos.css)
   2. Telón entre páginas al seguir un enlace interno
   ===================================================================== */
(function () {
  'use strict';

  var raiz = document.documentElement;
  window.__efectos = true;   // el script del <head> lo revisa para su respaldo

  var reducido = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ---------------- 1. Revelados ---------------- */

  var MAX_BLUR = 10;        // el desenfoque es caro: no más de 10 a la vez
  var DURACION = 800;       // igual a --t-reveal
  var blurActivos = 0;

  function mostrar(el) {
    el.classList.add('es-visible');
  }

  // Parte un título en líneas (según cómo se ve ahora) para revelarlas una por una.
  // Al terminar devuelve el texto original, así un cambio de tamaño no deja líneas rotas.
  function revelarLineas(el) {
    // Solo títulos de texto plano; si tiene etiquetas dentro, se usa fade-up
    if (el.children.length > 0) {
      el.setAttribute('data-reveal', 'fade-up');
      void el.offsetWidth;
      mostrar(el);
      return;
    }

    var original = el.textContent;
    var palabras = original.trim().split(/\s+/);

    // Medir: cada palabra en un span para saber en qué línea cae
    el.textContent = '';
    var spans = palabras.map(function (p, i) {
      var s = document.createElement('span');
      s.textContent = p;
      el.appendChild(s);
      if (i < palabras.length - 1) { el.appendChild(document.createTextNode(' ')); }
      return s;
    });

    var lineas = [];
    var tope = null;
    spans.forEach(function (s) {
      if (s.offsetTop !== tope) { lineas.push([]); tope = s.offsetTop; }
      lineas[lineas.length - 1].push(s.textContent);
    });

    // Reconstruir: una máscara por línea
    el.textContent = '';
    el.setAttribute('aria-label', original.trim());
    lineas.forEach(function (l, i) {
      var linea = document.createElement('span');
      linea.className = 'rl-linea';
      linea.setAttribute('aria-hidden', 'true');
      var texto = document.createElement('span');
      texto.className = 'rl-texto';
      texto.style.setProperty('--l', i);
      texto.textContent = l.join(' ');
      linea.appendChild(texto);
      el.appendChild(linea);
    });

    el.classList.add('lineas-listas');
    void el.offsetWidth;   // aplica el estado inicial antes de animar
    mostrar(el);

    setTimeout(function () {
      el.textContent = original;
      el.removeAttribute('aria-label');
    }, DURACION + lineas.length * 90 + 100);
  }

  function revelar(el) {
    var tipo = el.getAttribute('data-reveal');

    if (tipo === 'lineas') { revelarLineas(el); return; }

    if (tipo === 'blur-in') {
      if (blurActivos >= MAX_BLUR) {
        el.setAttribute('data-reveal', 'fade-up');
      } else {
        blurActivos++;
        setTimeout(function () { blurActivos--; }, DURACION);
      }
    }
    mostrar(el);
  }

  function preparar(el) {
    if (el.getAttribute('data-reveal') === 'stagger') {
      Array.prototype.forEach.call(el.children, function (hijo, i) {
        hijo.style.setProperty('--i', i);
      });
    }
  }

  var observador = null;

  function observar(el) {
    if (!el || el.classList.contains('es-visible')) { return; }
    preparar(el);
    if (reducido || !observador) { el.classList.add('es-visible'); return; }
    observador.observe(el);
  }

  function iniciarRevelados() {
    if (!reducido && 'IntersectionObserver' in window) {
      observador = new IntersectionObserver(function (entradas) {
        entradas.forEach(function (e) {
          if (!e.isIntersecting) { return; }
          observador.unobserve(e.target);   // una sola vez
          revelar(e.target);
        });
      }, { rootMargin: '0px 0px -10% 0px', threshold: 0 });
    }

    document.querySelectorAll('[data-reveal]').forEach(observar);
  }

  // Para contenido que otro script agregue después (menú, ventana de pedido)
  window.Efectos = { observar: observar, reducido: reducido };

  // Las líneas dependen de la fuente: esperar a que cargue (máximo 1.5s)
  var iniciado = false;
  function arrancar() {
    if (iniciado) { return; }
    iniciado = true;
    iniciarRevelados();
  }
  if (document.fonts && document.fonts.ready) {
    document.fonts.ready.then(arrancar);
    setTimeout(arrancar, 1500);
  } else {
    arrancar();
  }


  /* ---------------- 2. Telón entre páginas ---------------- */

  var ARCHIVO = /\.(jpe?g|png|gif|webp|avif|svg|pdf|zip|rar|docx?|xlsx?|csv|mp4)$/i;
  var NO_PUBLICO = /^\/(admin|pwa|storage|manifest)/;

  function debeUsarTelon(e, a) {
    if (e.defaultPrevented || e.button !== 0) { return false; }
    if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) { return false; }
    if (a.target && a.target !== '_self') { return false; }
    if (a.hasAttribute('download')) { return false; }

    // Enlaces que maneja otro script (galería, pestañas, desplegables)
    if (a.hasAttribute('data-fancybox') || a.hasAttribute('data-toggle') ||
        a.hasAttribute('data-bs-toggle') || a.hasAttribute('data-sin-telon') ||
        a.getAttribute('role') === 'button') { return false; }

    var href = a.getAttribute('href') || '';
    if (href === '' || href.charAt(0) === '#') { return false; }

    var url;
    try { url = new URL(a.href, location.href); } catch (x) { return false; }

    // Otro sitio, mailto:, tel:, javascript: (su origin es distinto o "null")
    if (url.origin !== location.origin) { return false; }
    if (NO_PUBLICO.test(url.pathname) || ARCHIVO.test(url.pathname)) { return false; }

    // Ancla dentro de la misma página
    if (url.pathname === location.pathname && url.search === location.search && url.hash) { return false; }

    return true;
  }

  if (!reducido) {
    document.addEventListener('click', function (e) {
      var a = e.target.closest && e.target.closest('a[href]');
      if (!a || !debeUsarTelon(e, a)) { return; }

      e.preventDefault();
      raiz.classList.add('saliendo');
      setTimeout(function () { location.href = a.href; }, 250);
    });
  }

  // Botón "atrás" (bfcache): la página vuelve tal cual estaba; quitar el telón
  window.addEventListener('pageshow', function () {
    raiz.classList.remove('saliendo');
  });
})();

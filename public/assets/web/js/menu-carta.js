/* =====================================================================
   menu-carta.js · Barra de categorías fija (páginas Menú y Mi pedido).
   Sin dependencias. Se carga con defer; no hace nada si no existe #carta.
   - Aparece cuando la carta está en pantalla y se va al llegar al pie
   - Marca la categoría visible y, si la barra no cabe, la centra
   - Clic: desplazamiento suave a la categoría
   - Píldora "Ver pedido · N" cuando hay productos en el carrito
   - Clic en una tarjeta: abre la ventana de pedido (o agrega 1 si no existe)
   ===================================================================== */
(function () {
  'use strict';

  var carta = document.getElementById('carta');
  var dock = document.getElementById('cartaDock');
  var barra = document.getElementById('cartaBarra');
  if (!carta || !dock || !barra) { return; }

  var lista = barra.querySelector('.carta-barra__lista');
  var enlaces = Array.prototype.slice.call(barra.querySelectorAll('a[href^="#"]'));
  var reducido = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var comportamiento = reducido ? 'auto' : 'smooth';

  function seccionDe(enlace) {
    return document.getElementById(decodeURIComponent(enlace.hash.slice(1)));
  }

  /* ---------------- Categoría activa ---------------- */

  var actual = null;

  function marcar(enlace) {
    if (!enlace || enlace === actual) { return; }
    if (actual) { actual.removeAttribute('aria-current'); }
    enlace.setAttribute('aria-current', 'true');
    actual = enlace;

    // Si la barra tiene scroll interno (celular), centrar la categoría activa
    if (lista.scrollWidth > lista.clientWidth) {
      var izquierda = enlace.parentElement.offsetLeft - (lista.clientWidth - enlace.parentElement.offsetWidth) / 2;
      lista.scrollTo({ left: Math.max(0, izquierda), behavior: comportamiento });
    }
  }

  /* ---------------- Clic: ir a la categoría ---------------- */

  barra.addEventListener('click', function (e) {
    var enlace = e.target.closest('a[href^="#"]');
    if (!enlace) { return; }
    var seccion = seccionDe(enlace);
    if (!seccion) { return; }

    e.preventDefault();
    seccion.scrollIntoView({ behavior: comportamiento, block: 'start' });
    history.replaceState(null, '', enlace.hash);
    marcar(enlace);

    // El foco pasa al título de la categoría (lectores de pantalla y teclado)
    var titulo = seccion.querySelector('.carta-cat__titulo');
    if (titulo) { titulo.focus({ preventScroll: true }); }
  });

  /* ---------------- Mostrar / ocultar la barra ---------------- */

  function mostrarDock(si) {
    dock.classList.toggle('es-activa', si);
    if ('inert' in dock) { dock.inert = !si; }   // oculta = no se puede enfocar
  }

  if (!('IntersectionObserver' in window)) {
    mostrarDock(true);
    if (enlaces[0]) { marcar(enlaces[0]); }
  } else {
    var pie = document.querySelector('.ftco-footer');
    var cartaVisible = false;
    var pieVisible = false;

    var observaZona = new IntersectionObserver(function (entradas) {
      entradas.forEach(function (e) {
        if (e.target === carta) { cartaVisible = e.isIntersecting; }
        else { pieVisible = e.isIntersecting; }
      });
      mostrarDock(cartaVisible && !pieVisible);
    });
    observaZona.observe(carta);
    if (pie) { observaZona.observe(pie); }

    // Una franja en el centro de la pantalla decide cuál categoría está "visible"
    var observaCategorias = new IntersectionObserver(function (entradas) {
      entradas.forEach(function (e) {
        if (!e.isIntersecting) { return; }
        var enlace = enlaces.filter(function (a) { return seccionDe(a) === e.target; })[0];
        marcar(enlace);
      });
    }, { rootMargin: '-40% 0px -55% 0px' });

    enlaces.forEach(function (a) {
      var s = seccionDe(a);
      if (s) { observaCategorias.observe(s); }
    });
  }

  /* ---------------- Píldora "Ver pedido · N" ---------------- */

  var pildora = document.getElementById('cartaPildora');
  if (pildora) {
    var actualizarPildora = function () {
      var n = window.CarritoPedido ? window.CarritoPedido.contar() : 0;
      pildora.hidden = n < 1;
    };

    // pedido.js actualiza el número en .contador-carrito; al cambiar, se revisa la píldora
    var contador = pildora.querySelector('.contador-carrito');
    if (contador && 'MutationObserver' in window) {
      new MutationObserver(actualizarPildora).observe(contador, { childList: true, characterData: true, subtree: true });
    }
    // Otra pestaña cambió el carrito
    window.addEventListener('storage', function (e) {
      if (e.key === 'carrito_pedido' && window.CarritoPedido) { window.CarritoPedido.actualizarContador(); }
    });

    // En Mi pedido la píldora abre el panel lateral (o, si no existe, baja al resumen)
    pildora.addEventListener('click', function (e) {
      if (window.PanelPedido && typeof window.PanelPedido.abrir === 'function') {
        e.preventDefault();
        window.PanelPedido.abrir(pildora);
        return;
      }
      var href = pildora.getAttribute('href') || '';
      if (href.charAt(0) !== '#') { return; }
      var destino = document.getElementById(href.slice(1));
      if (!destino) { return; }
      e.preventDefault();
      destino.scrollIntoView({ behavior: comportamiento, block: 'start' });
    });

    actualizarPildora();
  }

  /* ---------------- Resumen "Tu pedido" (solo en Mi pedido) ---------------- */

  // PUENTE con pedido.js (que no se modifica): su tabla #lineasPedido se redibuja
  // después de un clic en cualquier botón [data-accion] dentro de ella; con una
  // acción que no reconoce ("refrescar") solo redibuja, sin cambiar cantidades.
  // Si algún día pedido.js expone un "redibujar", basta con cambiar esta función.
  function refrescarResumen() {
    var cuerpo = document.getElementById('lineasPedido');
    if (!cuerpo) { return; }
    var b = document.createElement('button');
    b.type = 'button';
    b.hidden = true;
    b.setAttribute('data-accion', 'refrescar');
    cuerpo.appendChild(b);
    b.click();
    if (b.parentNode) { b.parentNode.removeChild(b); }
  }
  window.MenuCarta = { refrescarResumen: refrescarResumen };

  /* ---------------- Tarjetas de plato (.abrir-pedido) ---------------- */

  // Avisos para lectores de pantalla ("Agregado", "No quedan más unidades")
  var aviso = document.createElement('p');
  aviso.className = 'sr-only';   // clase de la plantilla (Bootstrap 4)
  aviso.setAttribute('aria-live', 'polite');
  document.body.appendChild(aviso);

  function avisar(tarjeta, texto) {
    aviso.textContent = texto;
    tarjeta.classList.add('es-agregado');
    clearTimeout(tarjeta._t);
    tarjeta._t = setTimeout(function () { tarjeta.classList.remove('es-agregado'); }, 1200);
  }

  // Respaldo mientras no exista la ventana de pedido (o si no cargó): agrega 1
  // sin pasar del stock ni de 99, con la misma forma de carrito de siempre.
  function agregarDirecto(tarjeta) {
    if (!window.CarritoPedido) {
      console.error('menu-carta: window.CarritoPedido no existe; no se puede agregar.');
      aviso.textContent = 'No se pudo agregar. Recarga la página e intenta de nuevo.';
      return;
    }
    var id = tarjeta.dataset.id;
    var stock = tarjeta.dataset.stock === '' ? null : parseInt(tarjeta.dataset.stock, 10);
    var tope = Math.min(stock === null ? 99 : stock, 99);
    var enCarrito = window.CarritoPedido.leer()[id] || 0;

    if (enCarrito >= tope) {
      aviso.textContent = 'Ya tienes todas las unidades disponibles de ' + tarjeta.dataset.nombre + '.';
      return;
    }
    window.CarritoPedido.agregar(id, 1);
    refrescarResumen();
    avisar(tarjeta, tarjeta.dataset.nombre + ' agregado a tu pedido.');
  }

  carta.addEventListener('click', function (e) {
    var tarjeta = e.target.closest('.abrir-pedido');
    if (!tarjeta) { return; }

    // La ventana de pedido (Fase C) se registra en window.ModalPedido
    if (window.ModalPedido && typeof window.ModalPedido.abrir === 'function') {
      window.ModalPedido.abrir(tarjeta);
    } else {
      agregarDirecto(tarjeta);
    }
  });
})();

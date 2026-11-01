/* =====================================================================
   modal-pedido.js · Ventana de pedido de la página Mi pedido.
   Sin dependencias. Se carga con defer; no hace nada si no existe #modalPedido.

   - Se abre desde una tarjeta .abrir-pedido (menu-carta.js llama a ModalPedido.abrir)
   - Lee los datos del producto de los data-* de la tarjeta (sin pedir nada al servidor)
   - Cantidad con tope: min(stock, 99) menos lo que ya está en el carrito
   - Nota por producto ("Instrucciones especiales"): window.CarritoPedido.fijarNota
   - Confirmar: window.CarritoPedido.agregar(id, cantidad), con la forma de siempre
   - Vista de confirmación con "También piden" (otras tarjetas de la página)
   ===================================================================== */
(function () {
  'use strict';

  var dialogo = document.getElementById('modalPedido');
  if (!dialogo) { return; }

  // Sin <dialog> nativo no se registra: menu-carta.js agrega 1 directamente
  if (typeof dialogo.showModal !== 'function') { return; }

  var MAX = 99;
  var raiz = document.documentElement;
  var reducido = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  // Precios como en el resto del sitio (Blade: number_format($n, 0, ',', '.')): ₡1.200
  var moneda = {
    format: function (n) { return '₡' + String(Math.round(n)).replace(/\B(?=(\d{3})+(?!\d))/g, '.'); }
  };

  var vistaDetalle = dialogo.querySelector('[data-vista="detalle"]');
  var vistaConfirmacion = dialogo.querySelector('[data-vista="confirmacion"]');
  var cuerpoDetalle = vistaDetalle.querySelector('.modal-pedido__cuerpo');
  var foto = dialogo.querySelector('.modal-pedido__foto img');
  var titulo = dialogo.querySelector('#modalPedidoTitulo');
  var numero = dialogo.querySelector('.modal-pedido__numero');
  var btnMenos = dialogo.querySelector('[data-menos]');
  var btnMas = dialogo.querySelector('[data-mas]');
  var btnConfirmar = dialogo.querySelector('.modal-pedido__confirmar');
  var aviso = dialogo.querySelector('.modal-pedido__error');
  var tambien = dialogo.querySelector('.modal-pedido__tambien');
  var lista = dialogo.querySelector('.modal-pedido__lista');
  var campoNota = dialogo.querySelector('#modalNota');
  var contadorNota = dialogo.querySelector('#modalNotaContador');
  var notaPrevia = '';   // nota que ya tenía el producto en el carrito

  var producto = null;   // datos de la tarjeta abierta
  var cantidad = 1;
  var tope = 1;
  var origen = null;     // elemento que abrió la ventana (recibe el foco al cerrar)
  var cerrando = false;

  function campos(nombre) { return dialogo.querySelectorAll('[data-campo="' + nombre + '"]'); }
  function poner(nombre, texto) { campos(nombre).forEach(function (el) { el.textContent = texto; }); }

  function datosDe(tarjeta) {
    var d = tarjeta.dataset;
    return {
      id: d.id,
      nombre: d.nombre,
      precio: parseFloat(d.precio) || 0,
      descripcion: d.descripcion || '',
      imagen: d.imagen || '',
      stock: d.stock === '' || d.stock === undefined ? null : parseInt(d.stock, 10),
      categoria: d.categoria
    };
  }

  function enCarrito(id) {
    return window.CarritoPedido ? (window.CarritoPedido.leer()[id] || 0) : 0;
  }

  // Cuántas unidades MÁS se pueden agregar de un producto
  function disponibles(p) {
    var maximo = Math.min(p.stock === null ? MAX : p.stock, MAX);
    return Math.max(0, maximo - enCarrito(p.id));
  }

  function refrescarResumen() {
    if (window.MenuCarta && window.MenuCarta.refrescarResumen) { window.MenuCarta.refrescarResumen(); }
  }

  /* ---------------- Cantidad y total ---------------- */

  function pintarCantidad() {
    numero.textContent = cantidad;
    btnMenos.disabled = cantidad <= 1;
    btnMas.disabled = cantidad >= tope;
    btnConfirmar.disabled = tope < 1;
    poner('total', moneda.format(producto.precio * Math.max(cantidad, 1)));

    var texto = '';
    if (tope < 1) {
      texto = 'Ya tienes en tu pedido todas las unidades disponibles.';
    } else if (cantidad >= tope) {
      texto = producto.stock === null && tope === MAX - enCarrito(producto.id)
        ? 'Máximo ' + MAX + ' unidades por producto.'
        : 'Quedan ' + tope + (tope === 1 ? ' unidad.' : ' unidades.');
    }
    campos('tope').forEach(function (el) { el.textContent = texto; el.hidden = !texto; });
  }

  /* ---------------- Instrucciones especiales ---------------- */

  function notasDisponibles() {
    return window.CarritoPedido && typeof window.CarritoPedido.nota === 'function';
  }
  function pintarContador() {
    if (contadorNota) { contadorNota.textContent = campoNota.value.length + '/' + campoNota.maxLength; }
  }
  if (campoNota) { campoNota.addEventListener('input', pintarContador); }

  btnMenos.addEventListener('click', function () { if (cantidad > 1) { cantidad--; pintarCantidad(); } });
  btnMas.addEventListener('click', function () { if (cantidad < tope) { cantidad++; pintarCantidad(); } });

  /* ---------------- Cabecera compacta al desplazarse ---------------- */

  if ('IntersectionObserver' in window) {
    new IntersectionObserver(function (entradas) {
      dialogo.classList.toggle('es-compacta', !entradas[0].isIntersecting);
    }, { root: cuerpoDetalle, threshold: 0 }).observe(dialogo.querySelector('.modal-pedido__centinela'));
  }

  /* ---------------- Abrir y cerrar ---------------- */

  function bloquearFondo(si) {
    if (si) {
      raiz.style.setProperty('--ancho-barra-scroll', (window.innerWidth - raiz.clientWidth) + 'px');
      raiz.classList.add('modal-abierto');
    } else {
      raiz.classList.remove('modal-abierto');
      raiz.style.removeProperty('--ancho-barra-scroll');
    }
  }

  function mostrarVista(cual) {
    vistaDetalle.hidden = cual !== 'detalle';
    vistaConfirmacion.hidden = cual !== 'confirmacion';
    vistaDetalle.classList.remove('es-oculta');
    vistaConfirmacion.classList.remove('es-oculta');
    dialogo.classList.toggle('es-confirmado', cual === 'confirmacion');
  }

  function abrir(tarjeta) {
    producto = datosDe(tarjeta);
    origen = tarjeta;
    tope = disponibles(producto);
    cantidad = 1;
    aviso.hidden = true;

    poner('nombre', producto.nombre);
    poner('precio', moneda.format(producto.precio));
    poner('descripcion', producto.descripcion);

    // Si el producto ya tiene nota en el carrito, aparece precargada
    notaPrevia = notasDisponibles() ? window.CarritoPedido.nota(producto.id) : '';
    if (campoNota) { campoNota.value = notaPrevia; pintarContador(); }

    if (producto.imagen) {
      foto.src = producto.imagen;
      dialogo.classList.remove('sin-foto');
    } else {
      foto.removeAttribute('src');
      dialogo.classList.add('sin-foto');
    }
    dialogo.classList.remove('es-compacta');

    mostrarVista('detalle');
    pintarCantidad();
    cuerpoDetalle.scrollTop = 0;

    bloquearFondo(true);
    cerrando = false;
    dialogo.showModal();
    titulo.focus({ preventScroll: true });   // los lectores de pantalla leen el nombre

    if (reducido) { dialogo.classList.add('es-abierto'); return; }
    requestAnimationFrame(function () {
      requestAnimationFrame(function () { dialogo.classList.add('es-abierto'); });
    });
  }

  function terminarCierre(enfocar) {
    dialogo.close();
    bloquearFondo(false);
    cerrando = false;
    if (enfocar !== false && origen && document.contains(origen)) { origen.focus(); }
  }

  // Salida inversa: se anima y luego se llama close()
  function cerrar(enfocar) {
    if (!dialogo.open || cerrando) { return; }
    cerrando = true;
    dialogo.classList.remove('es-abierto');
    if (reducido) { terminarCierre(enfocar); return; }

    var listo = false;
    var fin = function () { if (listo) { return; } listo = true; terminarCierre(enfocar); };
    dialogo.addEventListener('transitionend', function alTerminar(e) {
      if (e.target !== dialogo || e.propertyName !== 'opacity') { return; }
      dialogo.removeEventListener('transitionend', alTerminar);
      fin();
    });
    setTimeout(fin, 600);   // respaldo si no llega transitionend
  }

  // Escape: misma salida animada
  dialogo.addEventListener('cancel', function (e) { e.preventDefault(); cerrar(); });

  // Clic en el fondo (el ::backdrop se reporta como clic en el propio <dialog>)
  dialogo.addEventListener('click', function (e) {
    if (e.target === dialogo) { cerrar(); return; }
    if (e.target.closest('[data-cerrar]')) { cerrar(); }
  });

  /* ---------------- Confirmar ---------------- */

  btnConfirmar.addEventListener('click', function () {
    if (!window.CarritoPedido || typeof window.CarritoPedido.agregar !== 'function') {
      console.error('modal-pedido: window.CarritoPedido no existe; no se agregó nada.');
      aviso.textContent = 'No se pudo agregar. Recarga la página e intenta de nuevo.';
      aviso.hidden = false;
      return;
    }
    // Se vuelve a calcular por si el carrito cambió en otra pestaña
    tope = disponibles(producto);
    if (tope < 1) { pintarCantidad(); return; }
    cantidad = Math.min(cantidad, tope);

    // Nota escrita: reemplaza la anterior. Vacía: se conserva la que había,
    // salvo que el cliente haya borrado la nota precargada (entonces se quita).
    var nota = campoNota ? campoNota.value.trim() : '';
    window.CarritoPedido.agregar(producto.id, cantidad, nota);
    if (!nota && notaPrevia && notasDisponibles()) { window.CarritoPedido.fijarNota(producto.id, ''); }
    refrescarResumen();
    mostrarConfirmacion();
  });

  function mostrarConfirmacion() {
    poner('mensaje', cantidad + ' × ' + producto.nombre + ' agregado a tu pedido');
    llenarTambien();

    var mensaje = dialogo.querySelector('.modal-pedido__mensaje');
    if (reducido) {
      mostrarVista('confirmacion');
      mensaje.focus({ preventScroll: true });
      return;
    }
    // Fundido cruzado: sale el detalle (.15s) y entra la confirmación (.15s)
    vistaDetalle.classList.add('es-oculta');
    setTimeout(function () {
      mostrarVista('confirmacion');
      vistaConfirmacion.classList.add('es-oculta');
      void vistaConfirmacion.offsetWidth;   // aplica la opacidad 0 antes de animar
      vistaConfirmacion.classList.remove('es-oculta');
      mensaje.focus({ preventScroll: true });
    }, 150);
  }

  /* ---------------- "También piden" ---------------- */

  var svgMas = '<svg class="icono-mas" viewBox="0 0 16 16" width="14" height="14" aria-hidden="true"><path d="M8 3v10M3 8h10" /></svg>';
  var svgCheck = '<svg class="icono-check" viewBox="0 0 16 16" width="14" height="14" aria-hidden="true"><path d="M2.5 8.5l3.5 3.5 7.5-8" /></svg>';

  function llenarTambien() {
    var todas = Array.prototype.slice.call(document.querySelectorAll('.abrir-pedido'))
      .map(datosDe)
      .filter(function (p) { return p.id !== producto.id && disponibles(p) > 0; });

    // Primero de la misma categoría, luego de otras; sin repetir
    var elegidas = todas.filter(function (p) { return p.categoria === producto.categoria; })
      .concat(todas.filter(function (p) { return p.categoria !== producto.categoria; }))
      .slice(0, 3);

    lista.textContent = '';
    elegidas.forEach(function (p) {
      var li = document.createElement('li');
      var nombre = document.createElement('span');
      nombre.className = 'nombre';
      nombre.textContent = p.nombre;
      var precio = document.createElement('span');
      precio.className = 'precio';
      precio.textContent = moneda.format(p.precio);
      var mas = document.createElement('button');
      mas.type = 'button';
      mas.className = 'modal-pedido__paso';
      mas.setAttribute('aria-label', 'Agregar 1 ' + p.nombre);
      mas.innerHTML = svgMas + svgCheck;
      mas.addEventListener('click', function () { agregarUno(p, mas); });
      li.append(nombre, precio, mas);
      lista.appendChild(li);
    });
    tambien.hidden = elegidas.length === 0;
  }

  function agregarUno(p, boton) {
    if (!window.CarritoPedido || disponibles(p) < 1) {
      boton.disabled = true;
      return;
    }
    window.CarritoPedido.agregar(p.id, 1);
    refrescarResumen();
    boton.classList.add('es-agregado');
    boton.setAttribute('aria-label', p.nombre + ' agregado');
    clearTimeout(boton._t);
    boton._t = setTimeout(function () {
      boton.classList.remove('es-agregado');
      boton.setAttribute('aria-label', 'Agregar 1 ' + p.nombre);
      boton.disabled = disponibles(p) < 1;
    }, 1200);
  }

  /* ---------------- "Ver mi pedido" ---------------- */

  dialogo.querySelector('[data-ver-pedido]').addEventListener('click', function (e) {
    // Con panel lateral: se cierra la ventana sin animar y el panel se abre en el mismo
    // instante, con el fondo ya oscuro (sin parpadeo). El foco volverá a la tarjeta.
    if (window.PanelPedido && typeof window.PanelPedido.abrir === 'function') {
      e.preventDefault();
      var tarjeta = origen;
      dialogo.classList.remove('es-abierto');
      terminarCierre(false);
      window.PanelPedido.abrir(tarjeta, { fondoInmediato: true });
      return;
    }

    var url = new URL(dialogo.dataset.urlPedido || this.href, location.href);
    // Ya estamos en Mi pedido: cerrar y bajar al resumen
    if (url.pathname === location.pathname && url.hash) {
      e.preventDefault();
      var destino = document.getElementById(url.hash.slice(1));
      cerrar(false);
      if (destino) {
        setTimeout(function () {
          destino.setAttribute('tabindex', '-1');
          destino.scrollIntoView({ behavior: reducido ? 'auto' : 'smooth', block: 'start' });
          destino.focus({ preventScroll: true });
        }, reducido ? 0 : 450);
      }
    }
  });

  window.ModalPedido = { abrir: abrir, cerrar: cerrar };
})();

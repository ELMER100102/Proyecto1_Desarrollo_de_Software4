/* =====================================================================
   pedido.js  ·  Carrito y pedidos del sitio público
   Sin dependencias (no necesita jQuery). Cárgalo al final del <body>.

   Hace tres cosas, cada una solo si la página tiene lo que necesita:
   1. Botones "Agregar" (.btn-agregar-pedido) y contador del carrito (.contador-carrito)
   2. Página "Mi pedido"  (existe #datos-pedido)
   3. Página de seguimiento (existe #seguimiento)
   ===================================================================== */
(function () {
  'use strict';

  var CLAVE = 'carrito_pedido';
  var MAX_POR_PRODUCTO = 99;
  var memoria = {};   // respaldo si el navegador no deja usar localStorage

  var moneda = new Intl.NumberFormat('es-CR', {
    style: 'currency', currency: 'CRC', maximumFractionDigits: 0
  });

  /* ---------------- Carrito (guardado en el navegador) ---------------- */

  var Carrito = {
    leer: function () {
      var crudo = null;
      try { crudo = localStorage.getItem(CLAVE); } catch (e) { return memoria; }

      var datos = {};
      try { datos = JSON.parse(crudo || '{}'); } catch (e) { datos = {}; }
      if (!datos || typeof datos !== 'object' || Array.isArray(datos)) { datos = {}; }

      // Solo se aceptan ids numéricos y cantidades enteras de 1 a 99
      var limpio = {};
      Object.keys(datos).forEach(function (id) {
        var n = parseInt(datos[id], 10);
        if (/^\d+$/.test(id) && n >= 1) { limpio[id] = Math.min(n, MAX_POR_PRODUCTO); }
      });
      return limpio;
    },

    guardar: function (carrito) {
      memoria = carrito;
      try { localStorage.setItem(CLAVE, JSON.stringify(carrito)); } catch (e) { /* queda en memoria */ }
      Carrito.actualizarContador();
    },

    agregar: function (id, cantidad) {
      var c = Carrito.leer();
      c[id] = Math.min(MAX_POR_PRODUCTO, (c[id] || 0) + (cantidad || 1));
      Carrito.guardar(c);
    },

    fijar: function (id, cantidad) {
      var c = Carrito.leer();
      if (cantidad < 1) { delete c[id]; } else { c[id] = Math.min(MAX_POR_PRODUCTO, cantidad); }
      Carrito.guardar(c);
    },

    quitar: function (id) {
      var c = Carrito.leer();
      delete c[id];
      Carrito.guardar(c);
    },

    vaciar: function () { Carrito.guardar({}); },

    contar: function () {
      var c = Carrito.leer();
      return Object.keys(c).reduce(function (suma, id) { return suma + c[id]; }, 0);
    },

    actualizarContador: function () {
      var n = Carrito.contar();
      document.querySelectorAll('.contador-carrito').forEach(function (el) {
        el.textContent = n;
        el.style.display = n > 0 ? '' : 'none';
      });
    }
  };

  window.CarritoPedido = Carrito;   // por si otra página lo necesita

  /* ---------------- 1. Botones "Agregar" ---------------- */

  document.addEventListener('click', function (e) {
    var boton = e.target.closest('.btn-agregar-pedido');
    if (!boton) { return; }

    Carrito.agregar(boton.dataset.id, 1);

    // Confirmación breve en el mismo botón
    if (!boton.dataset.original) { boton.dataset.original = boton.textContent; }
    boton.textContent = '✓ Agregado';
    clearTimeout(boton._t);
    boton._t = setTimeout(function () { boton.textContent = boton.dataset.original; }, 1200);
  });

  /* ---------------- 2. Página "Mi pedido" ---------------- */

  function iniciarPedido(datosEl) {
    var datos = JSON.parse(datosEl.textContent);
    var productos = datos.productos || {};

    var cuerpo = document.getElementById('lineasPedido');
    var vacio = document.getElementById('pedidoVacio');
    var tablaWrap = document.getElementById('tablaPedido');
    var totalEl = document.getElementById('totalPedido');
    var aviso = document.getElementById('avisoCarrito');
    var form = document.getElementById('formPedido');
    var itemsOcultos = document.getElementById('itemsOcultos');
    var btnEnviar = document.getElementById('btnEnviarPedido');

    // Quita del carrito lo que ya no se puede pedir y ajusta cantidades al stock
    function depurar() {
      var c = Carrito.leer();
      var cambio = false;

      Object.keys(c).forEach(function (id) {
        var p = productos[id];
        if (!p) { delete c[id]; cambio = true; return; }
        if (p.stock !== null && c[id] > p.stock) { c[id] = p.stock; cambio = true; }
        if (c[id] < 1) { delete c[id]; cambio = true; }
      });

      if (cambio) {
        Carrito.guardar(c);
        aviso.textContent = 'Actualizamos tu pedido: algunos productos ya no estaban disponibles o cambió su existencia.';
        aviso.classList.remove('d-none');
      }
      return c;
    }

    function crear(tag, clase, texto) {
      var el = document.createElement(tag);
      if (clase) { el.className = clase; }
      if (texto !== undefined) { el.textContent = texto; }
      return el;
    }

    function dibujar() {
      var c = depurar();
      var ids = Object.keys(c);
      var total = 0;

      cuerpo.replaceChildren();

      ids.forEach(function (id) {
        var p = productos[id];
        var cantidad = c[id];
        var subtotal = Math.round(p.precio * cantidad * 100) / 100;
        total += subtotal;

        var tr = document.createElement('tr');

        var tdNombre = crear('td', 'align-middle');
        tdNombre.appendChild(crear('strong', '', p.nombre));
        tdNombre.appendChild(crear('div', 'small text-muted', moneda.format(p.precio) + ' c/u'));

        var tdCant = crear('td', 'align-middle text-nowrap');
        var menos = crear('button', 'btn btn-sm btn-outline-secondary', '−');
        menos.type = 'button'; menos.dataset.accion = 'menos'; menos.dataset.id = id;
        menos.setAttribute('aria-label', 'Quitar una unidad de ' + p.nombre);
        var cantEl = crear('span', 'mx-2', cantidad);
        var mas = crear('button', 'btn btn-sm btn-outline-secondary', '+');
        mas.type = 'button'; mas.dataset.accion = 'mas'; mas.dataset.id = id;
        mas.setAttribute('aria-label', 'Agregar una unidad de ' + p.nombre);
        if (p.stock !== null && cantidad >= p.stock) { mas.disabled = true; }
        tdCant.append(menos, cantEl, mas);

        var tdSub = crear('td', 'align-middle text-right text-end', moneda.format(subtotal));

        var tdQuitar = crear('td', 'align-middle text-right text-end');
        var quitar = crear('button', 'btn btn-sm btn-link text-danger', 'Quitar');
        quitar.type = 'button'; quitar.dataset.accion = 'quitar'; quitar.dataset.id = id;
        quitar.setAttribute('aria-label', 'Quitar ' + p.nombre + ' del pedido');
        tdQuitar.appendChild(quitar);

        tr.append(tdNombre, tdCant, tdSub, tdQuitar);
        cuerpo.appendChild(tr);
      });

      totalEl.textContent = moneda.format(Math.round(total * 100) / 100);
      tablaWrap.classList.toggle('d-none', ids.length === 0);
      vacio.classList.toggle('d-none', ids.length > 0);
      btnEnviar.disabled = ids.length === 0;
    }

    // Botones − + Quitar de cada línea (delegados: las líneas se redibujan)
    cuerpo.addEventListener('click', function (e) {
      var b = e.target.closest('button[data-accion]');
      if (!b) { return; }

      var id = b.dataset.id;
      var actual = Carrito.leer()[id] || 0;

      if (b.dataset.accion === 'mas') { Carrito.fijar(id, actual + 1); }
      if (b.dataset.accion === 'menos') { Carrito.fijar(id, actual - 1); }
      if (b.dataset.accion === 'quitar') { Carrito.quitar(id); }

      dibujar();
    });

    /* ---- Campos que dependen del tipo de pedido ---- */
    var radios = form.querySelectorAll('input[name="tipo"]');

    function tipoActual() {
      var marcado = form.querySelector('input[name="tipo"]:checked');
      return marcado ? marcado.value : null;
    }

    function ajustarCampos() {
      var tipo = tipoActual();

      form.querySelectorAll('[data-tipos]').forEach(function (grupo) {
        var visible = grupo.dataset.tipos.split(',').indexOf(tipo) !== -1;
        grupo.classList.toggle('d-none', !visible);

        // Un campo oculto no debe bloquear el envío
        var campo = grupo.querySelector('input, textarea');
        if (campo) { campo.disabled = !visible; }
      });

      var telefono = document.getElementById('campoTelefono');
      var obligatorio = ['llevar', 'domicilio'].indexOf(tipo) !== -1;
      telefono.required = obligatorio;
      document.getElementById('etiquetaTelefono').textContent = obligatorio ? 'Teléfono' : 'Teléfono (opcional)';

      var mesa = document.getElementById('campoMesa');
      if (mesa) { mesa.required = !mesa.disabled; }

      var direccion = document.getElementById('campoDireccion');
      if (direccion) { direccion.required = !direccion.disabled; }
    }

    radios.forEach(function (r) { r.addEventListener('change', ajustarCampos); });

    /* ---- Envío: el servidor recibe solo "producto => cantidad" ---- */
    form.addEventListener('submit', function (e) {
      var c = depurar();
      var ids = Object.keys(c);

      if (ids.length === 0) { e.preventDefault(); dibujar(); return; }

      itemsOcultos.replaceChildren();
      ids.forEach(function (id) {
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'items[' + id + ']';
        input.value = c[id];
        itemsOcultos.appendChild(input);
      });

      btnEnviar.disabled = true;               // evita enviarlo dos veces
      btnEnviar.textContent = 'Enviando…';
    });

    ajustarCampos();
    dibujar();
  }

  /* ---------------- 3. Seguimiento ---------------- */

  function iniciarSeguimiento(el) {
    var PASOS = ['pendiente', 'preparando', 'listo', 'entregado'];
    var estadoEl = document.getElementById('estadoTexto');
    var mensajeEl = document.getElementById('estadoMensaje');
    var pasosEl = document.getElementById('pasosPedido');
    var cancelado = document.getElementById('avisoCancelado');

    // Si el pedido acaba de crearse, el carrito ya cumplió su función
    if (el.dataset.limpiar === '1') { Carrito.vaciar(); }

    // Recordar el pedido en este navegador: el código solo vive en la URL y, si se pierde, no hay forma de volver
    try { localStorage.setItem('pedido_activo', location.pathname); } catch (e) { /* sin almacenamiento */ }
    function olvidar() { try { localStorage.removeItem('pedido_activo'); } catch (e) { } }
    if (el.dataset.abierto === '0') { olvidar(); }

    /* ----- Avisos: notificación + vibración cuando cambia el estado ----- */
    var ultimoEstado = el.dataset.estado;
    var bloqueAvisos = document.getElementById('bloqueAvisos');
    var btnAvisos = document.getElementById('btnAvisos');
    var soporta = 'Notification' in window;

    function actualizarBoton() {
      if (!soporta) { return; }
      // Se muestra solo mientras falta decidir; el permiso hay que pedirlo dentro de un clic
      bloqueAvisos.classList.toggle('d-none', Notification.permission !== 'default');
    }
    actualizarBoton();

    if (btnAvisos) {
      btnAvisos.addEventListener('click', function () {
        Notification.requestPermission().then(actualizarBoton);
      });
    }

    function avisar(d) {
      try { if (navigator.vibrate) { navigator.vibrate([200, 100, 200]); } } catch (e) { }

      if (!soporta || Notification.permission !== 'granted') { return; }
      var titulo = 'Pedido ' + el.dataset.turno + ': ' + d.etiqueta;
      var opciones = { body: d.mensaje, tag: 'pedido-' + el.dataset.turno, icon: '/favicon.ico' };

      // En celulares Android solo funciona desde el service worker
      if ('serviceWorker' in navigator) {
        navigator.serviceWorker.getRegistration().then(function (reg) {
          if (reg) { reg.showNotification(titulo, opciones); } else { new Notification(titulo, opciones); }
        }).catch(function () { });
      } else {
        try { new Notification(titulo, opciones); } catch (e) { }
      }
    }

    if ('serviceWorker' in navigator) {
      navigator.serviceWorker.register('/sw.js').catch(function () { });
    }

    function pintar(d) {
      if (d.estado !== ultimoEstado) {
        ultimoEstado = d.estado;
        avisar(d);
      }

      estadoEl.textContent = d.etiqueta;
      mensajeEl.textContent = d.mensaje;

      if (!d.abierto) { olvidar(); }

      var indice = PASOS.indexOf(d.estado);
      pasosEl.classList.toggle('d-none', d.estado === 'cancelado');
      cancelado.classList.toggle('d-none', d.estado !== 'cancelado');

      pasosEl.querySelectorAll('[data-paso]').forEach(function (li, i) {
        li.classList.toggle('hecho', i < indice);
        li.classList.toggle('actual', i === indice);
        if (i === indice) { li.setAttribute('aria-current', 'step'); } else { li.removeAttribute('aria-current'); }
      });
    }

    function consultar() {
      fetch(el.dataset.url, { headers: { 'Accept': 'application/json' }, cache: 'no-store' })
        .then(function (r) { return r.ok ? r.json() : Promise.reject(r.status); })
        .then(function (d) {
          pintar(d);
          if (!d.abierto) { clearInterval(timer); }
        })
        .catch(function () { /* sin conexión: se reintenta en el siguiente ciclo */ });
    }

    var timer = setInterval(consultar, 8000);
    document.addEventListener('visibilitychange', function () {
      if (!document.hidden) { consultar(); }
    });
  }

  /* ---------------- Arranque ---------------- */

  function arrancar() {
    Carrito.actualizarContador();

    var datosEl = document.getElementById('datos-pedido');
    if (datosEl) { iniciarPedido(datosEl); }

    var seg = document.getElementById('seguimiento');
    if (seg) { iniciarSeguimiento(seg); }

    // En la página de pedir: avisar si ya hay un pedido en curso
    var aviso = document.getElementById('pedidoActivo');
    if (aviso && !seg) {
      var ruta = null;
      try { ruta = localStorage.getItem('pedido_activo'); } catch (e) { }
      if (ruta) {
        document.getElementById('pedidoActivoEnlace').setAttribute('href', ruta);
        aviso.classList.remove('d-none');
      }
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', arrancar);
  } else {
    arrancar();
  }
})();

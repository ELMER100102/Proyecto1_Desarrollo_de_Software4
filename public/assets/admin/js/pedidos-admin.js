/* =====================================================================
   pedidos-admin.js  ·  Avisos de pedidos nuevos en el panel administrativo
   Cárgalo en TODAS las pantallas del admin (admin/parts/js.blade.php).
   Sin dependencias. Si la pantalla no tiene #badgePedidos, no hace nada.

   Cada pocos segundos pregunta al servidor un resumen liviano y:
   - actualiza el globito de pendientes del menú y el título de la pestaña
   - si llegó un pedido nuevo, muestra una notificación del navegador
   - si está abierta la pantalla de Pedidos, vuelve a pedir las tarjetas cuando algo cambió
   ===================================================================== */
(function () {
  'use strict';

  var badge = document.getElementById('badgePedidos');
  if (!badge || !badge.dataset.url) { return; }

  var INTERVALO = 15000;              // cada 15 segundos
  var MAX_ERRORES = 5;                // si falla tantas veces seguidas, se detiene (sesión vencida, sin red)

  var lista = document.getElementById('listaPedidos');   // solo existe en la pantalla de Pedidos
  var botonAvisos = document.getElementById('btnAvisos');
  var tituloOriginal = document.title;

  var ultimoVisto = null;             // id del último pedido conocido (null = aún no sabemos)
  var version = null;
  var errores = 0;
  var timer = null;

  /* ---------------- Avisos ---------------- */

  function avisosActivos() {
    return 'Notification' in window && Notification.permission === 'granted';
  }

  function notificar(titulo, cuerpo) {
    if (!avisosActivos()) { return; }
    var opciones = { body: cuerpo, tag: 'pedido-nuevo' };

    // Desde el service worker (Android lo exige); si no hay, desde la página
    var reg = (navigator.serviceWorker && navigator.serviceWorker.getRegistration)
      ? navigator.serviceWorker.getRegistration() : Promise.resolve(null);

    reg.then(function (r) {
      if (r && r.showNotification) { return r.showNotification(titulo, opciones); }
      new Notification(titulo, opciones);
    }).catch(function () {
      try { new Notification(titulo, opciones); } catch (e) { /* sin avisos */ }
    });
  }

  // Pitido corto (puede no sonar si el navegador aún no permite audio)
  function pitido() {
    try {
      var Ctx = window.AudioContext || window.webkitAudioContext;
      if (!Ctx) { return; }
      var ctx = new Ctx(), osc = ctx.createOscillator(), gain = ctx.createGain();
      osc.frequency.value = 880; gain.gain.value = 0.08;
      osc.connect(gain); gain.connect(ctx.destination);
      osc.start(); osc.stop(ctx.currentTime + 0.25);
    } catch (e) { /* sin sonido */ }
  }

  function actualizarBotonAvisos() {
    if (!botonAvisos) { return; }
    var puede = 'Notification' in window && Notification.permission === 'default';
    botonAvisos.classList.toggle('d-none', !puede);
  }

  if (botonAvisos) {
    botonAvisos.addEventListener('click', function () {
      Notification.requestPermission().then(actualizarBotonAvisos);
    });
    actualizarBotonAvisos();
  }

  /* ---------------- Consulta periódica ---------------- */

  function pintar(d) {
    // Globito del menú y título de la pestaña
    badge.textContent = d.pendientes;
    badge.classList.toggle('d-none', d.pendientes === 0);
    document.title = (d.pendientes > 0 ? '(' + d.pendientes + ') ' : '') + tituloOriginal;

    // Pedidos nuevos: la primera respuesta solo "memoriza", no avisa
    if (ultimoVisto !== null && d.nuevos > 0) {
      var titulo = d.nuevos === 1 ? 'Nuevo pedido' : d.nuevos + ' pedidos nuevos';
      notificar(titulo, d.texto || '');
      pitido();
    }
    ultimoVisto = d.ultimo;

    // Si esta pantalla muestra tarjetas y algo cambió, se vuelven a pedir
    if (lista && version !== null && d.version !== version) { refrescarLista(); }
    version = d.version;
  }

  function refrescarLista() {
    var url = lista.dataset.url + '?ver=' + encodeURIComponent(lista.dataset.ver || 'abiertos');

    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, cache: 'no-store', credentials: 'same-origin' })
      .then(function (r) { return r.ok && !r.redirected ? r.text() : Promise.reject(r.status); })
      .then(function (html) { lista.innerHTML = html; })
      .catch(function () { /* se reintenta cuando vuelva a cambiar */ });
  }

  function consultar() {
    var url = badge.dataset.url + (ultimoVisto !== null ? '?desde=' + ultimoVisto : '');

    fetch(url, { headers: { 'Accept': 'application/json' }, cache: 'no-store', credentials: 'same-origin' })
      .then(function (r) {
        // Si la sesión venció, Laravel redirige al login (HTML): no es el JSON esperado
        if (!r.ok || r.redirected) { return Promise.reject(r.status); }
        return r.json();
      })
      .then(function (d) { errores = 0; pintar(d); })
      .catch(function () {
        errores++;
        if (errores >= MAX_ERRORES) { clearInterval(timer); }
      });
  }

  // Al volver a la pestaña se consulta de inmediato
  document.addEventListener('visibilitychange', function () {
    if (!document.hidden && errores < MAX_ERRORES) { consultar(); }
  });

  consultar();
  timer = setInterval(consultar, INTERVALO);
})();

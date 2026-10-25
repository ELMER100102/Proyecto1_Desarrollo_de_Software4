/* =====================================================================
   PWA: registra el service worker y maneja el botón "Instalar app".
   Se incluye en el layout del sitio y en el del admin.
   Botón opcional:  <button id="btnInstalar" class="d-none">Instalar app</button>
   ===================================================================== */
(function () {
  'use strict';

  if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
      navigator.serviceWorker.register('/sw.js').catch(function () { /* sin HTTPS/localhost no aplica */ });
    });
  }

  var btn = document.getElementById('btnInstalar');
  if (!btn) { return; }

  var yaInstalada = window.matchMedia('(display-mode: standalone)').matches || navigator.standalone === true;
  if (yaInstalada) { return; }

  var evento = null;

  // Chrome/Edge/Android avisan cuando la app se puede instalar
  window.addEventListener('beforeinstallprompt', function (e) {
    e.preventDefault();
    evento = e;
    btn.classList.remove('d-none');
  });

  btn.addEventListener('click', function () {
    if (evento) {
      evento.prompt();
      evento.userChoice.then(function () { evento = null; btn.classList.add('d-none'); });
    } else {
      // iPhone/iPad no tienen instalador automático: se explica el camino manual
      alert('Para instalar: toca el botón Compartir y elige «Agregar a pantalla de inicio».');
    }
  });

  window.addEventListener('appinstalled', function () { btn.classList.add('d-none'); });

  // En iOS Safari no existe beforeinstallprompt: se muestra el botón con la guía manual
  var esIOS = /iphone|ipad|ipod/i.test(navigator.userAgent);
  if (esIOS) { btn.classList.remove('d-none'); }
})();

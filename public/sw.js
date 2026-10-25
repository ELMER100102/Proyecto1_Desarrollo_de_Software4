// Service worker (public/sw.js). Una sola instancia sirve al sitio y al admin.
// Sin caché de datos: los pedidos y el stock siempre vienen frescos del servidor.
// Solo guarda la página "sin conexión" para mostrarla si se cae el internet.
const VERSION = 'v2';
const OFFLINE = '/offline.html';

self.addEventListener('install', (event) => {
  event.waitUntil(caches.open(VERSION).then((c) => c.add(OFFLINE)));
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys()
      .then((claves) => Promise.all(claves.filter((k) => k !== VERSION).map((k) => caches.delete(k))))
      .then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (event) => {
  // Solo navegaciones (cambiar de página): si no hay red, se muestra la página sin conexión
  if (event.request.mode === 'navigate') {
    event.respondWith(fetch(event.request).catch(() => caches.match(OFFLINE)));
  }
});

self.addEventListener('notificationclick', (event) => {
  event.notification.close();
  const destino = (event.notification.data && event.notification.data.url) || '/';

  event.waitUntil(
    self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((ventanas) => {
      for (const v of ventanas) {
        if (v.url.includes(destino) && 'focus' in v) { return v.focus(); }
      }
      return self.clients.openWindow(destino);
    })
  );
});

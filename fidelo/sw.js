/* Fidelo — service worker : install PWA + notifications push (payload vide → on lit le texte via push.php). */
const CACHE = 'fidelo-v1';
self.addEventListener('install', e => self.skipWaiting());
self.addEventListener('activate', e => e.waitUntil(self.clients.claim()));

/* réseau d'abord, léger repli cache pour la coquille */
self.addEventListener('fetch', e => {
  const url = new URL(e.request.url);
  if (e.request.method !== 'GET' || url.origin !== location.origin) return;
  e.respondWith(
    fetch(e.request).then(r => {
      if (url.pathname.endsWith('/index.php') || url.pathname.endsWith('/')) {
        const c = r.clone(); caches.open(CACHE).then(ca => ca.put(e.request, c));
      }
      return r;
    }).catch(() => caches.match(e.request))
  );
});

/* Push : la notification arrive vide (VAPID sans payload chiffré) → on récupère le message. */
self.addEventListener('push', e => {
  e.waitUntil((async () => {
    let data = { title: 'Fidelo', body: 'Vous avez une nouveauté.', url: './carte.php' };
    try { if (e.data) { const j = e.data.json(); if (j) data = Object.assign(data, j); } } catch (_) {}
    try {
      const r = await fetch('./push.php?a=peek', { credentials: 'include' });
      if (r.ok) { const j = await r.json(); if (j && j.body) data = Object.assign(data, j); }
    } catch (_) {}
    await self.registration.showNotification(data.title, {
      body: data.body, icon: './icon-192.png', badge: './icon-192.png', data: { url: data.url }
    });
  })());
});
self.addEventListener('notificationclick', e => {
  e.notification.close();
  e.waitUntil(clients.openWindow(e.notification.data && e.notification.data.url ? e.notification.data.url : './'));
});

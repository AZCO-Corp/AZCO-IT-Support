// v7 - force reload on activate + always network for navigations
self.addEventListener('install', function(e) { self.skipWaiting(); });

self.addEventListener('activate', function(e) {
  e.waitUntil(
    caches.keys().then(function(n) {
      return Promise.all(n.map(function(k) { return caches.delete(k); }));
    }).then(function() {
      return self.clients.claim();
    }).then(function() {
      // Force all open tabs/PWA windows to reload with fresh content
      return self.clients.matchAll({ type: 'window' }).then(function(clients) {
        clients.forEach(function(client) {
          client.navigate(client.url);
        });
      });
    })
  );
});

self.addEventListener('fetch', function(e) {
  var url = new URL(e.request.url);
  if (url.origin === self.location.origin) {
    e.respondWith(fetch(e.request, { cache: 'no-store' }).catch(function() {
      return fetch(e.request);
    }));
  } else {
    e.respondWith(fetch(e.request));
  }
});

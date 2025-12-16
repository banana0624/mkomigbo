// project-root/public/service-worker-igbo-calendar.js

const CACHE_NAME = 'igbo-calendar-v1';

// Files we want cached for offline use
const APP_SHELL = [
  '/platforms/igbo-calendar.php',
  '/lib/css/subjects.css',
  '/manifest-igbo-calendar.webmanifest',
  // Add icons so splash screen + icon are available offline
  '/lib/images/icons/igbo-calendar-192.png',
  '/lib/images/icons/igbo-calendar-512.png'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => cache.addAll(APP_SHELL))
  );
  self.skipWaiting();
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys =>
      Promise.all(
        keys.map(key => {
          if (key !== CACHE_NAME) {
            return caches.delete(key);
          }
          return null;
        })
      )
    )
  );
  self.clients.claim();
});

// Cache-first strategy for navigation and static assets
self.addEventListener('fetch', event => {
  const req = event.request;

  // We only intercept GET requests
  if (req.method !== 'GET') {
    return;
  }

  event.respondWith(
    caches.match(req).then(cached => {
      if (cached) {
        return cached;
      }
      return fetch(req).then(response => {
        // Optionally cache new GET responses under same cache
        const respClone = response.clone();
        caches.open(CACHE_NAME).then(cache => {
          cache.put(req, respClone);
        });
        return response;
      }).catch(() => {
        // You can return a fallback HTML or message if offline + not cached
        return cached || new Response(
          'Offline and resource not cached yet.',
          { status: 503, headers: { 'Content-Type': 'text/plain' } }
        );
      });
    })
  );
});
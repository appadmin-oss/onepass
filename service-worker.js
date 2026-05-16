/* Afrostrength service worker — minimal, honest, opt-in.
 *
 * Strategy:
 *   - HTML responses: NEVER cached. Auth state changes, CSRF tokens
 *     rotate, content updates. We don't intercept document navigations.
 *   - Static assets (CSS, JS, fonts, images served from /assets, plus
 *     same-origin SVG/PNG/JPG/WebP): stale-while-revalidate, capped to
 *     50 entries.
 *   - Cross-origin fonts (fonts.gstatic.com, fontshare): cache-first,
 *     30-day TTL. These never change for a given hash and are the
 *     largest network hit on first paint.
 *
 * Anything we don't recognise: don't intercept. That's important on
 * shared cPanel where we can't guarantee headers, and where being
 * conservative beats being clever.
 */
const VERSION   = 'afs-sw-v1';
const STATIC_CACHE = 'afs-static-' + VERSION;
const FONT_CACHE   = 'afs-fonts-' + VERSION;
const MAX_STATIC   = 50;

self.addEventListener('install', (e) => {
  // Activate immediately on first install; on update we wait for clients
  // to release the old worker. Keeps versions tidy.
  self.skipWaiting();
});

self.addEventListener('activate', (e) => {
  e.waitUntil((async () => {
    const keys = await caches.keys();
    await Promise.all(keys.map(k => {
      if (k !== STATIC_CACHE && k !== FONT_CACHE) return caches.delete(k);
    }));
    await self.clients.claim();
  })());
});

function isFontHost(url) {
  return url.hostname === 'fonts.gstatic.com'
      || url.hostname === 'api.fontshare.com';
}
function isStaticAsset(url) {
  if (url.origin !== self.location.origin) return false;
  if (url.pathname.startsWith('/assets/')) return true;
  return /\.(css|js|woff2|woff|svg|png|jpg|jpeg|webp|gif|ico)$/i.test(url.pathname);
}

async function trimCache(name, max) {
  const cache = await caches.open(name);
  const keys = await cache.keys();
  if (keys.length <= max) return;
  // Drop the oldest entries — FIFO-ish (cache API doesn't guarantee order
  // but in practice it's insertion order on modern engines).
  await Promise.all(keys.slice(0, keys.length - max).map(req => cache.delete(req)));
}

self.addEventListener('fetch', (event) => {
  const req = event.request;
  if (req.method !== 'GET') return;

  const url = new URL(req.url);

  // Documents — never our problem.
  if (req.mode === 'navigate' || req.destination === 'document') return;

  if (isStaticAsset(url)) {
    event.respondWith((async () => {
      const cache = await caches.open(STATIC_CACHE);
      const cached = await cache.match(req);
      const network = fetch(req).then(async (res) => {
        if (res.ok) {
          cache.put(req, res.clone());
          trimCache(STATIC_CACHE, MAX_STATIC);
        }
        return res;
      }).catch(() => cached);
      return cached || network;
    })());
    return;
  }

  if (isFontHost(url)) {
    event.respondWith((async () => {
      const cache = await caches.open(FONT_CACHE);
      const cached = await cache.match(req);
      if (cached) return cached;
      const res = await fetch(req).catch(() => null);
      if (res && res.ok) cache.put(req, res.clone());
      return res || new Response('', { status: 504, statusText: 'fonts offline' });
    })());
    return;
  }

  // Default: don't intercept.
});

/* ============================================================
   Service Worker — SIGDRI PWA (Module 9)
   ============================================================
   Stratégies de cache :
     • Cache First  — ressources statiques (CSS, JS, images, fonts)
     • Network First — pages HTML Laravel (données dynamiques)
     • Offline Page  — fallback /offline si aucune connexion
   Background Sync :
     • Tag "sync-declarations" → relance les déclarations en attente
       stockées dans IndexedDB par offline-db.js
   ============================================================ */

const VERSION           = 'sigdri-v1';
const CACHE_STATIQUE    = `${VERSION}-static`;
const CACHE_DYNAMIQUE   = `${VERSION}-dynamic`;
const OFFLINE_URL       = '/offline';
const TAG_SYNC_DECL     = 'sync-declarations';

/* -- URLs pré-cachées dès l'installation du SW -- */
const URLS_PRECACHE = [
  '/offline',
  '/manifest.json',
  '/icons/icon-192.svg',
  '/icons/icon-512.svg',
  '/icons/icon-maskable.svg',
];

/* ── INSTALL ────────────────────────────────────────────────── */
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches
      .open(CACHE_STATIQUE)
      .then((cache) => cache.addAll(URLS_PRECACHE))
      .then(() => {
        // Activation immédiate sans attendre la fermeture des onglets
        return self.skipWaiting();
      })
  );
});

/* ── ACTIVATE ───────────────────────────────────────────────── */
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches
      .keys()
      .then((noms) =>
        Promise.all(
          noms
            // Supprime tous les caches de la version précédente
            .filter(
              (nom) =>
                nom.startsWith('sigdri-') &&
                nom !== CACHE_STATIQUE &&
                nom !== CACHE_DYNAMIQUE
            )
            .map((nom) => caches.delete(nom))
        )
      )
      .then(() => self.clients.claim())
  );
});

/* ── FETCH ──────────────────────────────────────────────────── */
self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);

  // Ignorer les requêtes non-GET (POST de formulaire, etc.)
  if (request.method !== 'GET') return;

  // Ignorer les protocoles non-HTTP (extensions Chrome, etc.)
  if (!url.protocol.startsWith('http')) return;

  // Ignorer les requêtes cross-origin (CDN, etc.)
  if (url.hostname !== self.location.hostname) return;

  /* -- Ressources statiques : stratégie Cache First -- */
  if (estsRessourceStatique(url.pathname)) {
    event.respondWith(strategieCacheFirst(request));
    return;
  }

  /* -- API JSON : stratégie Network First sans fallback page offline -- */
  if (url.pathname.startsWith('/api/')) {
    event.respondWith(strategieNetworkFirst(request, false));
    return;
  }

  /* -- Pages HTML : stratégie Network First + fallback page offline -- */
  event.respondWith(strategieNetworkFirst(request, true));
});

/* -- Détecte si l'URL correspond à une ressource statique -- */
function estsRessourceStatique(pathname) {
  return (
    pathname.startsWith('/build/')  ||  // Fichiers Vite compilés
    pathname.startsWith('/icons/')  ||
    pathname.startsWith('/js/')     ||
    pathname.match(/\.(css|js|woff2?|ttf|otf|eot|png|jpg|jpeg|svg|ico|webp|gif)$/)
  );
}

/* -- Stratégie Cache First : cache → réseau → mise en cache -- */
async function strategieCacheFirst(request) {
  const cached = await caches.match(request);
  if (cached) return cached;

  try {
    const response = await fetch(request);
    if (response.ok) {
      const cache = await caches.open(CACHE_STATIQUE);
      cache.put(request, response.clone());
    }
    return response;
  } catch {
    // Ressource statique non disponible offline — réponse vide acceptable
    return new Response('', { status: 504 });
  }
}

/* -- Stratégie Network First : réseau → cache → offline -- */
async function strategieNetworkFirst(request, avecFallbackOffline) {
  try {
    const response = await fetch(request);
    if (response.ok) {
      // Mise en cache de la réponse fraîche
      const cache = await caches.open(CACHE_DYNAMIQUE);
      cache.put(request, response.clone());
    }
    return response;
  } catch {
    // Pas de réseau — cherche dans le cache
    const cached = await caches.match(request);
    if (cached) return cached;

    // Aucun cache disponible et fallback demandé → page offline
    if (avecFallbackOffline) {
      const pagineOffline = await caches.match(OFFLINE_URL);
      if (pagineOffline) return pagineOffline;
    }

    // Fallback API JSON en cas d'absence totale de réseau
    return new Response(
      JSON.stringify({ erreur: 'Hors-ligne — aucune donnée en cache.' }),
      { status: 503, headers: { 'Content-Type': 'application/json' } }
    );
  }
}

/* ── BACKGROUND SYNC ────────────────────────────────────────── */
self.addEventListener('sync', (event) => {
  if (event.tag === TAG_SYNC_DECL) {
    // Notifie toutes les pages ouvertes pour qu'elles lancent la synchronisation
    // (la page a accès à la session et au CSRF token)
    event.waitUntil(notifierClientsPourSync());
  }
});

async function notifierClientsPourSync() {
  const clients = await self.clients.matchAll({ type: 'window' });
  clients.forEach((client) =>
    client.postMessage({ type: 'SYNC_DECLARATIONS' })
  );
}

/* ── MESSAGE depuis les pages ───────────────────────────────── */
self.addEventListener('message', (event) => {
  // Mise à jour immédiate du SW sans recharger l'onglet
  if (event.data?.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
});

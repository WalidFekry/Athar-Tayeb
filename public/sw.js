/**
 * Athar Tayeb - Service Worker
 * Handles caching and offline support for the PWA
 *
 * Security notes:
 * - Dynamic PHP pages and API endpoints are NOT cached
 * - User session pages are excluded from cache
 * - POST requests and form submissions are never intercepted
 * - No sensitive data is stored in cache
 */

'use strict';

const CACHE_VERSION = 'v1.0.0';
const STATIC_CACHE = 'athar-tayeb-static-' + CACHE_VERSION;
const DYNAMIC_CACHE = 'athar-tayeb-dynamic-' + CACHE_VERSION;

// Static assets that are safe to cache (CSS, JS, fonts, images)
const STATIC_ASSETS = [
    '/assets/css/main.css',
    '/assets/css/footer-styles.css',
    '/assets/css/pwa.css',
    '/assets/js/main.js',
    '/assets/js/pwa.js',
    '/assets/images/icon-192.png',
    '/assets/images/icon-512.png',
    '/assets/images/placeholder-memorial.png',
    '/offline'
];

// Patterns to NEVER cache (dynamic, authenticated, or sensitive content)
const NEVER_CACHE_PATTERNS = [
    /^\/admin\//,          // Admin area
    /^\/api\//,            // API endpoints
    /\.(php)(\?.*)?$/,     // Raw PHP files with extension
    /\/logout/,            // Logout
    /\/login/,             // Login pages
    /google-analytics/,    // Analytics
    /googletagmanager/,    // Tag manager
    /fonts\.googleapis/,   // Google Fonts requests
    /fonts\.gstatic/       // Font files via Google CDN
];

// Pages that need fresh network data (avoid stale caching)
const NETWORK_FIRST_PATHS = [
    '/create',
    '/edit',
    '/contact',
    '/search',
    '/all',
    '/m/'
];

// ==========================================
// Install: Pre-cache static assets
// ==========================================
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then((cache) => {
                return cache.addAll(
                    STATIC_ASSETS.filter(url => !url.startsWith('http'))
                );
            })
            .then(() => {
                // Don't use skipWaiting() to avoid stale/new file conflicts
                // New SW will activate when all tabs close
            })
            .catch((err) => {
                console.warn('[SW] Install cache failed for some assets:', err);
            })
    );
});

// ==========================================
// Activate: Clean up old caches
// ==========================================
self.addEventListener('activate', (event) => {
    const allowedCaches = [STATIC_CACHE, DYNAMIC_CACHE];

    event.waitUntil(
        caches.keys()
            .then((cacheNames) => {
                return Promise.all(
                    cacheNames
                        .filter(name => name.startsWith('athar-tayeb-') && !allowedCaches.includes(name))
                        .map(name => {
                            console.log('[SW] Deleting old cache:', name);
                            return caches.delete(name);
                        })
                );
            })
            .then(() => self.clients.claim())
    );
});

// ==========================================
// Fetch: Handle requests with proper strategies
// ==========================================
self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);

    // Only handle GET requests — never intercept POST/PUT/DELETE
    if (request.method !== 'GET') {
        return;
    }

    // Skip cross-origin requests (CDNs, Google Fonts, Analytics, etc.)
    if (url.origin !== self.location.origin) {
        return;
    }

    // Never cache specific patterns (admin, api, php, login, logout)
    if (NEVER_CACHE_PATTERNS.some(pattern => pattern.test(url.pathname + url.search))) {
        return;
    }

    // Strategy 1: Cache First for static assets (CSS, JS, images, fonts)
    if (isStaticAsset(url.pathname)) {
        event.respondWith(cacheFirst(request));
        return;
    }

    // Strategy 2: Network First for dynamic/user-facing pages
    // Falls back to offline page if network fails
    if (isNavigationRequest(request)) {
        event.respondWith(networkFirstWithOfflineFallback(request, url));
        return;
    }

    // For everything else: try network, don't cache
    // (This handles any other GET requests gracefully)
});

// ==========================================
// Helper: Check if URL is a static asset
// ==========================================
function isStaticAsset(pathname) {
    return pathname.startsWith('/assets/') ||
           pathname.endsWith('.css') ||
           pathname.endsWith('.js') ||
           pathname.endsWith('.png') ||
           pathname.endsWith('.jpg') ||
           pathname.endsWith('.webp') ||
           pathname.endsWith('.svg') ||
           pathname.endsWith('.ico') ||
           pathname.endsWith('.woff') ||
           pathname.endsWith('.woff2');
}

// ==========================================
// Helper: Check if this is a page navigation
// ==========================================
function isNavigationRequest(request) {
    return request.mode === 'navigate' ||
           (request.headers.get('accept') || '').includes('text/html');
}

// ==========================================
// Strategy: Cache First (for static assets)
// ==========================================
function cacheFirst(request) {
    return caches.match(request)
        .then((cached) => {
            if (cached) {
                return cached;
            }
            return fetch(request)
                .then((response) => {
                    if (response && response.status === 200 && response.type === 'basic') {
                        const responseToCache = response.clone();
                        caches.open(STATIC_CACHE).then(cache => {
                            cache.put(request, responseToCache);
                        });
                    }
                    return response;
                })
                .catch(() => {
                    // Return null, the browser will handle the error naturally
                    return null;
                });
        });
}

// ==========================================
// Strategy: Network First with Offline Fallback
// For page navigation only — never serves stale user data
// ==========================================
function networkFirstWithOfflineFallback(request, url) {
    return fetch(request)
        .then((response) => {
            // Only cache successful, non-sensitive public pages
            if (response && response.status === 200) {
                // Cache the home page for offline use only
                if (url.pathname === '/' || url.pathname === '/index') {
                    const responseToCache = response.clone();
                    caches.open(DYNAMIC_CACHE).then(cache => {
                        cache.put(request, responseToCache);
                    });
                }
            }
            return response;
        })
        .catch(() => {
            // Network failed — try cache for home page
            if (url.pathname === '/' || url.pathname === '/index') {
                return caches.match(request).then(cached => cached || getOfflinePage());
            }
            // For other pages, show offline page
            return getOfflinePage();
        });
}

// ==========================================
// Helper: Get the offline fallback page
// ==========================================
function getOfflinePage() {
    return caches.match('/offline')
        .then(cached => {
            if (cached) return cached;
            // Minimal inline fallback if offline page is not cached
            return new Response(
                `<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>لا يوجد اتصال - أثر طيب</title>
<style>
body{font-family:'Cairo',sans-serif;background:#F9F6F2;color:#2B2B2B;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;text-align:center;direction:rtl}
.container{padding:2rem;max-width:400px}
.icon{font-size:4rem;margin-bottom:1rem}
h1{color:#5A7D4E;font-size:1.5rem}
p{color:#58624B;margin:1rem 0}
button{background:#5A7D4E;color:white;border:none;padding:.75rem 2rem;border-radius:12px;font-size:1rem;cursor:pointer;font-family:inherit}
button:hover{background:#4a6d3e}
</style>
</head>
<body>
<div class="container">
<div class="icon">🌿</div>
<h1>لا يوجد اتصال بالإنترنت</h1>
<p>يبدو أنك غير متصل بالإنترنت حالياً. تحقق من اتصالك وحاول مجدداً.</p>
<button onclick="location.reload()">إعادة المحاولة</button>
</div>
</body>
</html>`,
                {
                    headers: {
                        'Content-Type': 'text/html; charset=utf-8',
                        'Cache-Control': 'no-store'
                    }
                }
            );
        });
}

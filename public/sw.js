/*
 * HUIFAX display service worker: lets the display start, and keep running,
 * while the server is unreachable.
 *
 * - The display page and its content API: network first (with a timeout),
 *   falling back to the last good copy.
 * - Built assets and uploaded/weather images: cache first. Built assets are
 *   versioned by hash; images get new names when they change.
 *
 * Everything else (the admin panel, health checks) goes straight to the
 * network. Plain script on purpose: it is served as-is, outside the build.
 */

const CACHE_VERSION = 1;
const PAGE_CACHE = `huifax-page-v${CACHE_VERSION}`;
const ASSET_CACHE = `huifax-assets-v${CACHE_VERSION}`;
const NETWORK_TIMEOUT_MS = 8000;

const scope = new URL(self.registration.scope);
const path = (relative) => new URL(relative, scope).pathname;

const PAGE_PATH = scope.pathname;
const API_PATH = path('api/display');
const MANIFEST_PATH = path('build/manifest.json');
const BUILD_PREFIX = path('build/');
const IMAGE_PREFIXES = [path('storage/'), path('weather-icons/')];

self.addEventListener('install', (event) => {
    self.skipWaiting();
    event.waitUntil(refreshBuildAssets());
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        (async () => {
            const keep = [PAGE_CACHE, ASSET_CACHE];
            for (const name of await caches.keys()) {
                if (name.startsWith('huifax-') && !keep.includes(name)) {
                    await caches.delete(name);
                }
            }
            await self.clients.claim();
        })(),
    );
});

self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    if (request.method !== 'GET' || url.origin !== scope.origin) {
        return;
    }

    if (request.mode === 'navigate' && url.pathname === PAGE_PATH) {
        event.respondWith(networkFirst(event, request, { ignoreSearch: true }));
    } else if (url.pathname === API_PATH) {
        event.respondWith(networkFirst(event, request, { ignoreSearch: false }));
    } else if (url.pathname.startsWith(BUILD_PREFIX) || IMAGE_PREFIXES.some((p) => url.pathname.startsWith(p))) {
        event.respondWith(cacheFirst(request));
    }
});

/**
 * Fresh copy when the server answers in time, else the last good one.
 * Only complete 200 responses are stored; 304s pass through untouched.
 */
async function networkFirst(event, request, { ignoreSearch }) {
    const cache = await caches.open(PAGE_CACHE);

    try {
        const response = await fetchWithTimeout(request);

        if (response.status === 200) {
            const key = ignoreSearch ? PAGE_PATH : request.url;
            await cache.put(key, response.clone());
            event.waitUntil(afterFreshResponse(request, response.clone()));
        }

        return response;
    } catch (error) {
        const cached = await cache.match(ignoreSearch ? PAGE_PATH : request.url);
        return cached ?? Response.error();
    }
}

async function cacheFirst(request) {
    const cache = await caches.open(ASSET_CACHE);
    const cached = await cache.match(request.url);

    if (cached) {
        return cached;
    }

    const response = await fetch(request);
    if (response.status === 200) {
        await cache.put(request.url, response.clone());
    }
    return response;
}

function fetchWithTimeout(request) {
    return fetch(request, { signal: AbortSignal.timeout(NETWORK_TIMEOUT_MS) });
}

/**
 * Keep the caches in step with the latest content: fetch every image it
 * uses (so the display can start offline with all of them) and drop the
 * rest. The page embeds the same payload the API returns.
 */
async function afterFreshResponse(request, response) {
    if (new URL(request.url).pathname === API_PATH) {
        await syncImages(await response.json());
        return;
    }

    await refreshBuildAssets();

    const payload = embeddedPayload(await response.text());
    if (payload) {
        await syncImages(payload);
    }
}

function embeddedPayload(html) {
    const match = html.match(/<script type="application\/json" id="display-payload">([\s\S]*?)<\/script>/);
    try {
        return match ? JSON.parse(match[1]) : null;
    } catch {
        return null;
    }
}

/** Cache every file of the current build and drop files of older builds. */
async function refreshBuildAssets() {
    try {
        const response = await fetch(MANIFEST_PATH, { cache: 'no-store' });
        if (!response.ok) {
            return;
        }

        const manifest = await response.json();
        const files = new Set();
        for (const entry of Object.values(manifest)) {
            for (const file of [entry.file, ...(entry.css ?? []), ...(entry.assets ?? [])]) {
                if (file) {
                    files.add(new URL(`${BUILD_PREFIX}${file}`, scope).href);
                }
            }
        }

        const cache = await caches.open(ASSET_CACHE);
        const cached = new Set((await cache.keys()).map((req) => req.url));

        await Promise.all([...files].filter((url) => !cached.has(url)).map((url) => cache.add(url).catch(() => {})));

        for (const url of cached) {
            if (new URL(url).pathname.startsWith(BUILD_PREFIX) && !files.has(url)) {
                await cache.delete(url);
            }
        }
    } catch {
        // Offline or no build manifest: keep what is cached.
    }
}

/** Cache the images the content references and remove the ones it no longer does. */
async function syncImages(payload) {
    const used = new Set();
    collectUrls(payload, used);
    const isImage = (pathname) => IMAGE_PREFIXES.some((p) => pathname.startsWith(p));

    const cache = await caches.open(ASSET_CACHE);
    const cached = new Set();

    for (const request of await cache.keys()) {
        const url = new URL(request.url);
        if (isImage(url.pathname) && !used.has(url.pathname)) {
            await cache.delete(request);
        } else {
            cached.add(url.pathname);
        }
    }

    const missing = [...used].filter((pathname) => isImage(pathname) && !cached.has(pathname));
    await Promise.all(missing.map((pathname) => cache.add(new URL(pathname, scope).href).catch(() => {})));
}

function collectUrls(value, used) {
    if (typeof value === 'string') {
        try {
            const url = new URL(value, scope);
            if (url.origin === scope.origin) {
                used.add(url.pathname);
            }
        } catch {
            // Not a URL.
        }
    } else if (Array.isArray(value)) {
        value.forEach((item) => collectUrls(item, used));
    } else if (value && typeof value === 'object') {
        Object.values(value).forEach((item) => collectUrls(item, used));
    }
}

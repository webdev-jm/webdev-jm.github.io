let offlineSyncEnabled = true;

const STATIC_CACHE = "pwa-static-v7";
const API_CACHE = "pwa-api-v1";
const PAGES_CACHE = "pwa-pages-v1";
const IDB_NAME = "offline-sync-db";
const IDB_VERSION = 1;
const SYNC_TAG = "offline-queue";
const MAX_QUEUE = 100;

const STATIC_ASSETS = [
    "/offline",
    // Vendor CSS
    "/vendor/fontawesome-free/css/all.min.css",
    "/vendor/overlayScrollbars/css/OverlayScrollbars.min.css",
    "/vendor/adminlte/dist/css/adminlte.min.css",
    // Vendor JS
    "/vendor/jquery/jquery.min.js",
    "/vendor/bootstrap/js/bootstrap.bundle.min.js",
    "/vendor/overlayScrollbars/js/jquery.overlayScrollbars.min.js",
    "/vendor/adminlte/dist/js/adminlte.min.js",
    // FontAwesome webfonts (woff2 — modern browsers; fetched by all.min.css)
    "/vendor/fontawesome-free/webfonts/fa-solid-900.woff2",
    "/vendor/fontawesome-free/webfonts/fa-regular-400.woff2",
    "/vendor/fontawesome-free/webfonts/fa-brands-400.woff2",
    // Custom skins
    "/css/custom.css",
    "/css/neumorphic.css",
    // Favicon
    "/favicon.ico",
    "/favicon.png",
    "/favicons/favicon.png",
    // App images
    "/images/Default_pfp.svg.png",
    "/images/jm-logo-ai.png",
    // PWA icons — android
    "/images/pwa/android/launchericon-72x72.png",
    "/images/pwa/android/launchericon-96x96.png",
    "/images/pwa/android/launchericon-144x144.png",
    "/images/pwa/android/launchericon-192x192.png",
    "/images/pwa/android/launchericon-512x512.png",
    // PWA icons — ios
    "/images/pwa/ios/128.png",
    "/images/pwa/ios/152.png",
];

// ─── IndexedDB helpers ───────────────────────────────────────────────────────

function openDb() {
    return new Promise((resolve, reject) => {
        const req = indexedDB.open(IDB_NAME, IDB_VERSION);
        req.onupgradeneeded = (e) => {
            const db = e.target.result;
            if (!db.objectStoreNames.contains("sync-queue")) {
                const store = db.createObjectStore("sync-queue", {
                    keyPath: "id",
                    autoIncrement: true,
                });
                store.createIndex("status", "status", { unique: false });
                store.createIndex("createdAt", "createdAt", { unique: false });
            }
        };
        req.onsuccess = (e) => resolve(e.target.result);
        req.onerror = (e) => reject(e.target.error);
    });
}

function dbTransaction(storeName, mode, callback) {
    return openDb().then((db) => {
        return new Promise((resolve, reject) => {
            const tx = db.transaction(storeName, mode);
            const store = tx.objectStore(storeName);
            const result = callback(store);
            tx.oncomplete = () =>
                resolve(result instanceof IDBRequest ? result.result : result);
            tx.onerror = (e) => reject(e.target.error);
        });
    });
}

function getQueueCount(db) {
    return new Promise((resolve, reject) => {
        const tx = db.transaction("sync-queue", "readonly");
        const index = tx.objectStore("sync-queue").index("status");
        const req = index.count(IDBKeyRange.only("pending"));
        req.onsuccess = () => resolve(req.result);
        req.onerror = (e) => reject(e.target.error);
    });
}

function getAllPending(db) {
    return new Promise((resolve, reject) => {
        const tx = db.transaction("sync-queue", "readonly");
        const index = tx.objectStore("sync-queue").index("createdAt");
        const req = index.getAll();
        req.onsuccess = () =>
            resolve((req.result || []).filter((r) => r.status === "pending"));
        req.onerror = (e) => reject(e.target.error);
    });
}

function updateRecord(db, id, updates) {
    return new Promise((resolve, reject) => {
        const tx = db.transaction("sync-queue", "readwrite");
        const store = tx.objectStore("sync-queue");
        const getReq = store.get(id);
        getReq.onsuccess = () => {
            const record = Object.assign(getReq.result, updates);
            store.put(record);
        };
        tx.oncomplete = () => resolve();
        tx.onerror = (e) => reject(e.target.error);
    });
}

function deleteRecord(db, id) {
    return new Promise((resolve, reject) => {
        const tx = db.transaction("sync-queue", "readwrite");
        tx.objectStore("sync-queue").delete(id);
        tx.oncomplete = () => resolve();
        tx.onerror = (e) => reject(e.target.error);
    });
}

async function enqueueRequest(request) {
    const db = await openDb();
    const count = await getQueueCount(db);

    if (count >= MAX_QUEUE) {
        notifyClients({ type: "queue-full" });
        return;
    }

    let body = null;
    try {
        body = await request.clone().text();
    } catch (_) {
        body = "";
    }

    const headers = {};
    for (const [key, value] of request.headers.entries()) {
        const lower = key.toLowerCase();
        if (
            [
                "content-type",
                "x-csrf-token",
                "accept",
                "authorization",
            ].includes(lower)
        ) {
            headers[key] = value;
        }
    }

    const record = {
        url: request.url,
        method: request.method,
        headers,
        body,
        clientId: crypto.randomUUID(),
        status: "pending",
        attempts: 0,
        lastError: null,
        createdAt: Date.now(),
    };

    const tx = db.transaction("sync-queue", "readwrite");
    tx.objectStore("sync-queue").add(record);
    await new Promise((res, rej) => {
        tx.oncomplete = res;
        tx.onerror = (e) => rej(e.target.error);
    });

    const newCount = await getQueueCount(db);
    notifyClients({ type: "queued", count: newCount });
}

async function readCsrfCookie() {
    if ("cookieStore" in self) {
        try {
            const cookie = await cookieStore.get("XSRF-TOKEN");
            return cookie ? decodeURIComponent(cookie.value) : "";
        } catch (_) {
            return "";
        }
    }
    return "";
}

async function replayQueue(csrfToken) {
    const db = await openDb();
    const pending = await getAllPending(db);

    if (pending.length === 0) {
        return;
    }

    const items = pending.map((r) => ({
        idbId: r.id,
        attempts: r.attempts ?? 0,
        client_id: r.clientId,
        method: r.method,
        url: new URL(r.url).pathname + new URL(r.url).search,
        payload: (() => {
            try {
                return JSON.parse(r.body);
            } catch (_) {
                return {};
            }
        })(),
        client_timestamp: r.createdAt,
        headers: r.headers,
    }));

    const authHeader =
        items[0]?.headers?.["authorization"] ||
        items[0]?.headers?.["Authorization"] ||
        "";
    const xsrf = csrfToken || (await readCsrfCookie());

    let synced = 0;
    let failed = 0;

    try {
        const response = await fetch("/api/sync/batch", {
            method: "POST",
            credentials: "include",
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                ...(xsrf ? { "X-XSRF-TOKEN": xsrf } : {}),
                ...(authHeader ? { Authorization: authHeader } : {}),
            },
            body: JSON.stringify({
                items: items.map(
                    ({ idbId, headers, attempts, ...rest }) => rest,
                ),
            }),
        });

        if (!response.ok) {
            // Server error (5xx) → leave as pending for retry.
            // Client error (4xx) → permanently failed, remove from retry loop.
            const isPermanentFailure =
                response.status >= 400 && response.status < 500;
            for (const item of items) {
                if (isPermanentFailure) {
                    await updateRecord(db, item.idbId, {
                        status: "failed",
                        lastError: `HTTP ${response.status}`,
                    });
                    failed++;
                } else {
                    await updateRecord(db, item.idbId, {
                        status: "pending",
                        attempts: item.attempts + 1,
                    });
                }
            }
            notifyClients({ type: "sync-complete", synced, failed });
            return;
        }

        const data = await response.json();
        const resultMap = {};
        for (const result of data.results || []) {
            resultMap[result.client_id] = result;
        }

        for (const item of items) {
            const result = resultMap[item.client_id];
            if (result && result.status === "applied") {
                await deleteRecord(db, item.idbId);
                synced++;
            } else if (
                result &&
                (result.status === "conflict" || result.status === "rejected")
            ) {
                await updateRecord(db, item.idbId, {
                    status: result.status,
                    lastError: result.reason ?? null,
                });
                failed++;
            } else {
                await updateRecord(db, item.idbId, {
                    status: "pending",
                    attempts: item.attempts + 1,
                });
            }
        }
    } catch (_) {
        // True network failure — still offline, leave pending and do not notify.
        return;
    }

    notifyClients({ type: "sync-complete", synced, failed });
}

function notifyClients(payload) {
    self.clients
        .matchAll({ includeUncontrolled: true, type: "window" })
        .then((clients) => {
            for (const client of clients) {
                client.postMessage(payload);
            }
        });
}

// ─── Cache helpers ───────────────────────────────────────────────────────────

function isStaticAsset(pathname) {
    return (
        pathname.startsWith("/vendor/") ||
        pathname.startsWith("/images/") ||
        pathname.startsWith("/css/") ||
        pathname.startsWith("/build/") ||
        /\.(js|css|png|jpg|jpeg|svg|gif|ico|woff|woff2|ttf|eot)$/.test(pathname)
    );
}

async function cacheFirst(request) {
    const cached = await caches.match(request);
    if (cached) {
        return cached;
    }
    const response = await fetch(request);
    if (response.ok) {
        const cache = await caches.open(STATIC_CACHE);
        cache.put(request, response.clone());
    }
    return response;
}

function offlineFallback() {
    return caches.match("/offline").then(
        (r) =>
            r ||
            new Response("You are offline.", {
                status: 503,
                headers: { "Content-Type": "text/plain" },
            }),
    );
}

async function networkFirstWithApiCache(request) {
    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(API_CACHE);
            cache.put(request, response.clone());
        }
        return response;
    } catch (_) {
        const cached = await caches.match(request);
        return cached || offlineFallback();
    }
}

async function networkFirstWithPageCache(request) {
    const cache = await caches.open(PAGES_CACHE);
    try {
        const response = await fetch(request);
        if (response.ok) {
            cache.put(request, response.clone());
        }
        return response;
    } catch (_) {
        // Offline — serve cached version so the page still loads
        const cached = await cache.match(request);
        return cached || offlineFallback();
    }
}

async function handleMutation(request) {
    const clone = request.clone();
    try {
        return await fetch(request);
    } catch (_) {
        if (!offlineSyncEnabled) {
            return new Response(
                JSON.stringify({ queued: false, message: "Offline sync is disabled." }),
                { status: 503, headers: { "Content-Type": "application/json" } },
            );
        }
        await enqueueRequest(clone);
        self.registration.sync.register(SYNC_TAG).catch(() => {});
        return new Response(
            JSON.stringify({
                queued: true,
                message: "Request queued for sync when online.",
            }),
            {
                status: 202,
                headers: {
                    "Content-Type": "application/json",
                    "X-Offline-Queued": "true",
                },
            },
        );
    }
}

// ─── Service Worker lifecycle ─────────────────────────────────────────────────

self.addEventListener("install", (event) => {
    self.skipWaiting();
    event.waitUntil(
        caches.open(STATIC_CACHE).then((cache) => cache.addAll(STATIC_ASSETS)),
    );
});

self.addEventListener("activate", (event) => {
    const validCaches = [STATIC_CACHE, API_CACHE, PAGES_CACHE];
    event.waitUntil(
        caches
            .keys()
            .then((keys) =>
                Promise.all(
                    keys
                        .filter(
                            (key) =>
                                key.startsWith("pwa-") &&
                                !validCaches.includes(key),
                        )
                        .map((key) => caches.delete(key)),
                ),
            )
            .then(() => self.clients.claim()),
    );
});

self.addEventListener("fetch", (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Never intercept non-GET requests to Livewire or WebSocket upgrades
    if (
        url.pathname.startsWith("/livewire") ||
        request.headers.get("X-Livewire")
    ) {
        return;
    }

    // Never intercept cross-origin requests
    if (url.origin !== self.location.origin) {
        return;
    }

    if (["POST", "PUT", "PATCH", "DELETE"].includes(request.method)) {
        // Navigation requests are HTML form submissions — let the browser handle them.
        // Only queue XHR/fetch (Axios) mutations, identified by non-navigate mode or JSON accept header.
        if (
            request.mode === "navigate" ||
            request.headers.get("Accept")?.includes("text/html")
        ) {
            event.respondWith(fetch(request).catch(() => offlineFallback()));
            return;
        }
        event.respondWith(handleMutation(request));
        return;
    }

    if (url.pathname.startsWith("/api/")) {
        event.respondWith(networkFirstWithApiCache(request));
        return;
    }

    if (isStaticAsset(url.pathname)) {
        event.respondWith(cacheFirst(request));
        return;
    }

    event.respondWith(networkFirstWithPageCache(request));
});

self.addEventListener("sync", (event) => {
    if (event.tag === SYNC_TAG) {
        // Background Sync fires without a page — read CSRF from cookie if available
        event.waitUntil(readCsrfCookie().then((token) => replayQueue(token)));
    }
});

// iOS Safari / manual fallback: triggered by offline.js when window.online fires
self.addEventListener("message", (event) => {
    if (event.data?.type === "REPLAY_QUEUE") {
        // Page passes the CSRF token directly so we don't need the cookieStore API
        event.waitUntil(replayQueue(event.data.csrfToken ?? ""));
    }

    if (event.data?.type === "SET_OFFLINE_SYNC") {
        offlineSyncEnabled = event.data.enabled !== false;
    }
});

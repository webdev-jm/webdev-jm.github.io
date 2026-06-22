if (window.offlineSyncEnabled === false) {
    window.offlineQueue = { getPendingCount: () => Promise.resolve(0) };
    // Tell the service worker to stop queueing offline requests
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.ready.then((registration) => {
            if (registration.active) {
                registration.active.postMessage({ type: 'SET_OFFLINE_SYNC', enabled: false });
            }
        });
    }
} else {

const IDB_NAME = 'offline-sync-db';
const IDB_VERSION = 1;

function openDb() {
    return new Promise((resolve, reject) => {
        const req = indexedDB.open(IDB_NAME, IDB_VERSION);
        req.onupgradeneeded = (e) => {
            const db = e.target.result;
            if (!db.objectStoreNames.contains('sync-queue')) {
                const store = db.createObjectStore('sync-queue', { keyPath: 'id', autoIncrement: true });
                store.createIndex('status', 'status', { unique: false });
                store.createIndex('createdAt', 'createdAt', { unique: false });
            }
        };
        req.onsuccess = (e) => resolve(e.target.result);
        req.onerror = (e) => reject(e.target.error);
    });
}

function getPendingCount() {
    return openDb().then((db) => {
        return new Promise((resolve, reject) => {
            const tx = db.transaction('sync-queue', 'readonly');
            const req = tx.objectStore('sync-queue').index('status').count(IDBKeyRange.only('pending'));
            req.onsuccess = () => resolve(req.result);
            req.onerror = (e) => reject(e.target.error);
        });
    });
}

function dispatch(name, detail) {
    window.dispatchEvent(new CustomEvent(name, { detail }));
}

// Expose for UI use
window.offlineQueue = { getPendingCount };

if ('serviceWorker' in navigator) {
    // Inform the service worker of the current setting on every page load
    navigator.serviceWorker.ready.then((registration) => {
        if (registration.active) {
            registration.active.postMessage({ type: 'SET_OFFLINE_SYNC', enabled: true });
        }
    });

    // Handle messages from the service worker
    navigator.serviceWorker.addEventListener('message', (event) => {
        const { type, count, synced, failed } = event.data ?? {};

        if (type === 'queued') {
            dispatch('offline:queued', { count: count ?? 0 });
        }

        if (type === 'sync-complete') {
            dispatch('offline:synced', { synced: synced ?? 0, failed: failed ?? 0 });
        }

        if (type === 'queue-full') {
            dispatch('offline:queue-full', {});
        }
    });

    // Broadcast initial online status and sync on restore (iOS Safari fallback)
    window.addEventListener('online', () => {
        dispatch('offline:status-changed', { online: true });

        if (navigator.serviceWorker.controller) {
            // Pass CSRF token so the SW can authenticate the /api/sync/batch request
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
            navigator.serviceWorker.controller.postMessage({ type: 'REPLAY_QUEUE', csrfToken });
        }

        // Polling fallback: if the SW doesn't respond within 15s, read IDB directly
        const poll = setTimeout(() => {
            getPendingCount().then((count) => {
                if (count === 0) {
                    dispatch('offline:synced', { synced: 0, failed: 0 });
                }
            });
        }, 15000);

        const cleanup = () => clearTimeout(poll);
        window.addEventListener('offline:synced', cleanup, { once: true });
        window.addEventListener('offline:queued', cleanup, { once: true });
    });

    window.addEventListener('offline', () => {
        dispatch('offline:status-changed', { online: false });
    });
}

// Axios interceptor: detect the synthetic 202 the SW returns for queued mutations
if (window.axios) {
    window.axios.interceptors.response.use(
        (response) => {
            if (response.headers['x-offline-queued']) {
                getPendingCount().then((count) => {
                    dispatch('offline:queued', { count });
                });
            }
            return response;
        },
        (error) => Promise.reject(error)
    );
}

} // end offlineSyncEnabled guard

import Dexie from 'dexie';

const db = new Dexie('AppDatabase');

db.version(1).stores({
    posts:      'id, status, synced_at',         // id = client UUID (Dexie PK); server_id stored after sync
    sync_queue: '++queue_id, client_id, status', // auto-increment PK for FIFO ordering
});

/**
 * Save a post locally to IndexedDB and enqueue a POST to /api/posts for background sync.
 *
 * Uses crypto.randomUUID() — CSPRNG built into all modern browsers, no library required.
 * Returns the generated client UUID so the caller can reference the pending record.
 *
 * @param {{ title: string, body?: string }} postData
 * @returns {Promise<string>} client UUID
 */
export async function savePostLocally(postData) {
    const id = crypto.randomUUID();
    const client_created_at = Date.now();

    await db.posts.add({
        id,
        ...postData,
        client_created_at,
        status: 'pending',
    });

    await db.sync_queue.add({
        client_id: id,
        target_url: '/api/posts',
        method: 'POST',
        payload: JSON.stringify({ uuid: id, ...postData, client_created_at }),
        client_timestamp: client_created_at,
        status: 'pending',
    });

    // Fire-and-forget: the existing service worker intercepts this request.
    // If offline → enqueues to its own offline-sync-db and returns 202.
    // If online  → hits PostController::store() directly and returns 201.
    if (window.axios) {
        window.axios
            .post('/api/posts', { uuid: id, ...postData, client_created_at })
            .then((response) => {
                // Update local record with server-assigned auto-increment id
                db.posts
                    .where('id')
                    .equals(id)
                    .modify({ server_id: response.data.id, status: 'synced' });
                db.sync_queue
                    .where('client_id')
                    .equals(id)
                    .modify({ status: 'synced' });
            })
            .catch(() => {
                // SW will handle retry via Background Sync — no action needed here
            });
    }

    return id;
}

if (window.offlineSyncEnabled !== false) {
    window.db = db;
    window.savePostLocally = savePostLocally;
}

export { db };

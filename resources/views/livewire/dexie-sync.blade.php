<div>
    {{-- wire:ignore prevents Livewire from re-diffing this Alpine-managed subtree --}}
    <div
        wire:ignore
        x-data="{
            posts: [],
            newTitle: '',
            loading: false,

            init() {
                this.loadFromDexie();

                {{-- Livewire → Dexie: server posts bulk-put into IndexedDB, then re-render --}}
                $wire.on('dexie:update-posts', async (event) => {
                    if (event.posts && event.posts.length) {
                        await window.db.posts.bulkPut(event.posts);
                    }
                    await this.loadFromDexie();
                });
            },

            async loadFromDexie() {
                this.posts = await window.db.posts.orderBy('client_created_at').reverse().toArray();
            },

            {{-- Dexie-only write: server never contacted. UI updates instantly. --}}
            async saveOffline() {
                if (!this.newTitle.trim()) { return; }
                this.loading = true;
                await window.savePostLocally({ title: this.newTitle.trim() });
                this.newTitle = '';
                await this.loadFromDexie();
                this.loading = false;
            },

            {{-- Dexie → Livewire: triggers syncFromServer() which dispatches dexie:update-posts --}}
            async pullFromServer() {
                this.loading = true;
                await $wire.syncFromServer();
                this.loading = false;
            }
        }"
    >
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Offline Post Demo</h3>
                <div class="card-tools">
                    <small class="text-muted">Livewire ↔ Alpine ↔ Dexie bridge</small>
                </div>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <input
                        type="text"
                        class="form-control form-control-sm"
                        placeholder="Post title…"
                        x-model="newTitle"
                        @keydown.enter.prevent="saveOffline()"
                    >
                </div>
                <div class="d-flex">
                    <button
                        class="btn btn-sm btn-warning"
                        @click.prevent="saveOffline()"
                        :disabled="loading || !newTitle.trim()"
                    >
                        <i class="fas fa-database mr-1"></i> Save Offline
                    </button>
                    <button
                        class="btn btn-sm btn-primary ml-2"
                        @click.prevent="pullFromServer()"
                        :disabled="loading"
                    >
                        <i class="fas fa-cloud-download-alt mr-1" :class="{ 'fa-spin': loading }"></i>
                        Pull from Server
                    </button>
                </div>
            </div>
        </div>

        <div class="card mt-2">
            <div class="card-header">
                <h3 class="card-title">
                    Local Posts
                    <span class="badge badge-secondary ml-1" x-text="posts.length"></span>
                </h3>
            </div>
            <div class="card-body p-0">
                <template x-if="posts.length === 0">
                    <p class="text-muted text-center py-3 mb-0">No local posts yet. Save one or pull from server.</p>
                </template>
                <ul class="list-group list-group-flush" x-show="posts.length > 0">
                    <template x-for="post in posts" :key="post.id">
                        <li class="list-group-item py-2">
                            <div class="d-flex justify-content-between align-items-start">
                                <strong x-text="post.title"></strong>
                                <span
                                    class="badge ml-2"
                                    :class="{
                                        'badge-warning': post.status === 'pending',
                                        'badge-success': post.status === 'synced'
                                    }"
                                    x-text="post.status"
                                ></span>
                            </div>
                        </li>
                    </template>
                </ul>
            </div>
        </div>
    </div>
</div>

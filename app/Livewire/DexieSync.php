<?php

namespace App\Livewire;

use App\Models\Post;
use Livewire\Component;

class DexieSync extends Component
{
    /**
     * Fetches the authenticated user's 20 most recent posts from MySQL and dispatches
     * them to the browser. Alpine catches 'dexie:update-posts' and bulk-puts the records
     * into the Dexie posts store, mapping uuid → Dexie id (PK) and MySQL id → server_id.
     */
    public function syncFromServer(): void
    {
        $posts = Post::where('user_id', auth()->id())
            ->latest()
            ->take(20)
            ->get(['id', 'uuid', 'title', 'body', 'client_created_at'])
            ->map(fn (Post $post): array => [
                'id'               => $post->uuid,
                'server_id'        => $post->id,
                'title'            => $post->title,
                'body'             => $post->body,
                'client_created_at' => $post->client_created_at,
                'status'           => 'synced',
                'synced_at'        => now()->getTimestampMs(),
            ])
            ->values()
            ->all();

        $this->dispatch('dexie:update-posts', posts: $posts);
    }

    /**
     * Called by Alpine to explicitly persist an offline-saved post to MySQL.
     * Uses firstOrCreate so repeated calls with the same uuid are safe.
     */
    public function saveData(string $uuid, string $title, ?string $body = null): void
    {
        abort_unless(auth()->check(), 403);

        Post::firstOrCreate(
            ['uuid' => $uuid],
            [
                'user_id'           => auth()->id(),
                'title'             => $title,
                'body'              => $body,
                'client_created_at' => now()->getTimestampMs(),
            ]
        );
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.dexie-sync');
    }
}

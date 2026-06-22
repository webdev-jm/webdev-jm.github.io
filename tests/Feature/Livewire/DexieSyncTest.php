<?php

use App\Livewire\DexieSync;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
    $this->user = User::factory()->create();
});

describe('DexieSync', function () {
    it('renders successfully', function () {
        Livewire::actingAs($this->user)
            ->test(DexieSync::class)
            ->assertSuccessful();
    });

    it('syncFromServer dispatches dexie:update-posts with correct payload shape', function () {
        Post::factory()->create(['user_id' => $this->user->id]);

        Livewire::actingAs($this->user)
            ->test(DexieSync::class)
            ->call('syncFromServer')
            ->assertDispatched('dexie:update-posts', function (string $name, array $params): bool {
                $posts = $params['posts'];

                return count($posts) === 1
                    && $posts[0]['status'] === 'synced'
                    && isset($posts[0]['id'])
                    && isset($posts[0]['server_id'])
                    && isset($posts[0]['synced_at']);
            });
    });

    it('syncFromServer only returns posts belonging to the authenticated user', function () {
        Post::factory()->create(['user_id' => $this->user->id, 'title' => 'Mine']);
        Post::factory()->create(['title' => 'Other User']);

        Livewire::actingAs($this->user)
            ->test(DexieSync::class)
            ->call('syncFromServer')
            ->assertDispatched('dexie:update-posts', function (string $name, array $params): bool {
                return count($params['posts']) === 1
                    && $params['posts'][0]['title'] === 'Mine';
            });
    });

    it('syncFromServer limits results to 20 posts', function () {
        Post::factory()->count(25)->create(['user_id' => $this->user->id]);

        Livewire::actingAs($this->user)
            ->test(DexieSync::class)
            ->call('syncFromServer')
            ->assertDispatched('dexie:update-posts', function (string $name, array $params): bool {
                return count($params['posts']) === 20;
            });
    });

    it('syncFromServer maps uuid to Dexie id and MySQL id to server_id', function () {
        $post = Post::factory()->create(['user_id' => $this->user->id]);

        Livewire::actingAs($this->user)
            ->test(DexieSync::class)
            ->call('syncFromServer')
            ->assertDispatched('dexie:update-posts', function (string $name, array $params) use ($post): bool {
                $payload = $params['posts'][0];

                return $payload['id'] === $post->uuid
                    && $payload['server_id'] === $post->id;
            });
    });

    it('saveData persists a post to the database', function () {
        $uuid = (string) Str::uuid();

        Livewire::actingAs($this->user)
            ->test(DexieSync::class)
            ->call('saveData', $uuid, 'Offline Title', 'Offline body');

        expect(Post::where('uuid', $uuid)->exists())->toBeTrue();

        $this->assertDatabaseHas('posts', [
            'uuid'    => $uuid,
            'user_id' => $this->user->id,
            'title'   => 'Offline Title',
            'body'    => 'Offline body',
        ]);
    });

    it('saveData is idempotent for the same uuid', function () {
        $uuid = (string) Str::uuid();

        $component = Livewire::actingAs($this->user)->test(DexieSync::class);
        $component->call('saveData', $uuid, 'First Title');
        $component->call('saveData', $uuid, 'Second Title');

        expect(Post::where('uuid', $uuid)->count())->toBe(1)
            ->and(Post::where('uuid', $uuid)->value('title'))->toBe('First Title');
    });

    it('saveData aborts for unauthenticated users', function () {
        $uuid = (string) Str::uuid();

        Livewire::test(DexieSync::class)
            ->call('saveData', $uuid, 'No Auth Title')
            ->assertForbidden();
    });
});

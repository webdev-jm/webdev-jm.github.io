<?php

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class)->beforeEach(function () {
    $this->withoutVite();
});

// ── store ─────────────────────────────────────────────────────────────────────

it('rejects unauthenticated store requests', function () {
    $this->postJson('/api/posts', [])->assertStatus(401);
});

it('validates required fields on store', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/posts', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['uuid', 'title', 'client_created_at']);
});

it('rejects an invalid uuid on store', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/posts', [
            'uuid'              => 'not-a-uuid',
            'title'             => 'Test',
            'client_created_at' => now()->getTimestampMs(),
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['uuid']);
});

it('creates a post and returns 201 with server id and uuid', function () {
    $user = User::factory()->create();
    $uuid = (string) Str::uuid();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/posts', [
            'uuid'              => $uuid,
            'title'             => 'Hello World',
            'body'              => 'Some content',
            'client_created_at' => now()->getTimestampMs(),
        ])
        ->assertStatus(201)
        ->assertJsonStructure(['id', 'uuid', 'title', 'body'])
        ->assertJsonPath('uuid', $uuid)
        ->assertJsonPath('title', 'Hello World');

    $this->assertDatabaseHas('posts', [
        'uuid'    => $uuid,
        'user_id' => $user->id,
        'title'   => 'Hello World',
    ]);
});

it('rejects a duplicate uuid (idempotency guard)', function () {
    $user = User::factory()->create();
    $uuid = (string) Str::uuid();

    Post::factory()->create(['user_id' => $user->id, 'uuid' => $uuid]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/posts', [
            'uuid'              => $uuid,
            'title'             => 'Duplicate',
            'client_created_at' => now()->getTimestampMs(),
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['uuid']);
});

// ── update (3-way merge) ──────────────────────────────────────────────────────

it('rejects unauthenticated update requests', function () {
    $post = Post::factory()->create();
    $this->putJson("/api/posts/{$post->id}", [])->assertStatus(401);
});

it('validates required fields on update', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user, 'sanctum')
        ->putJson("/api/posts/{$post->id}", [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['original', 'updated', 'client_updated_at']);
});

it('merges non-overlapping field changes (happy path)', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create([
        'user_id' => $user->id,
        'title'   => 'Original Title',
        'body'    => 'Server changed body',
    ]);

    // Client had baseline { title: 'Original Title', body: 'Original Body' }
    // Client only changed title → safe to apply; server changed body → keep server body
    $this->actingAs($user, 'sanctum')
        ->putJson("/api/posts/{$post->id}", [
            'original'          => ['title' => 'Original Title', 'body' => 'Original Body'],
            'updated'           => ['title' => 'Client New Title', 'body' => 'Original Body'],
            'client_updated_at' => now()->getTimestampMs(),
        ])
        ->assertStatus(200)
        ->assertJsonPath('title', 'Client New Title')
        ->assertJsonPath('body', 'Server changed body');

    $this->assertDatabaseHas('posts', [
        'id'    => $post->id,
        'title' => 'Client New Title',
        'body'  => 'Server changed body',
    ]);
});

it('returns 200 without update when nothing changed', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create([
        'user_id' => $user->id,
        'title'   => 'Same Title',
        'body'    => 'Same Body',
    ]);

    $this->actingAs($user, 'sanctum')
        ->putJson("/api/posts/{$post->id}", [
            'original'          => ['title' => 'Same Title', 'body' => 'Same Body'],
            'updated'           => ['title' => 'Same Title', 'body' => 'Same Body'],
            'client_updated_at' => now()->getTimestampMs(),
        ])
        ->assertStatus(200);

    $this->assertDatabaseHas('posts', ['id' => $post->id, 'title' => 'Same Title']);
});

it('returns 409 when both client and server changed the same field', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create([
        'user_id' => $user->id,
        'title'   => 'Server Changed Title',
        'body'    => 'Body',
    ]);

    // Both client and server changed title from the original 'Original Title'
    $this->actingAs($user, 'sanctum')
        ->putJson("/api/posts/{$post->id}", [
            'original'          => ['title' => 'Original Title', 'body' => 'Body'],
            'updated'           => ['title' => 'Client Changed Title', 'body' => 'Body'],
            'client_updated_at' => now()->getTimestampMs(),
        ])
        ->assertStatus(409)
        ->assertJsonStructure(['message', 'conflicts', 'merged'])
        ->assertJsonPath('conflicts.title.original', 'Original Title')
        ->assertJsonPath('conflicts.title.client', 'Client Changed Title')
        ->assertJsonPath('conflicts.title.server', 'Server Changed Title');

    // Server record should NOT have been modified
    $this->assertDatabaseHas('posts', ['id' => $post->id, 'title' => 'Server Changed Title']);
});

it('merges clean fields and reports only conflicting ones on partial conflict', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create([
        'user_id' => $user->id,
        'title'   => 'Server Changed Title',
        'body'    => 'Original Body',
    ]);

    // Client changed both; server only changed title → body merge is clean, title conflicts
    $response = $this->actingAs($user, 'sanctum')
        ->putJson("/api/posts/{$post->id}", [
            'original'          => ['title' => 'Original Title', 'body' => 'Original Body'],
            'updated'           => ['title' => 'Client Title', 'body' => 'Client Body'],
            'client_updated_at' => now()->getTimestampMs(),
        ])
        ->assertStatus(409)
        ->assertJsonStructure(['conflicts', 'merged']);

    // merged should already include the non-conflicting body change
    expect($response->json('merged.body'))->toBe('Client Body');
});

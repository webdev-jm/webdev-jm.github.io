<?php

use App\Models\SyncRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class)->beforeEach(function () {
    $this->withoutVite();
});

it('rejects unauthenticated sync batch requests', function () {
    $this->postJson('/api/sync/batch', ['items' => []])
        ->assertStatus(401);
});

it('rejects unauthenticated sync status requests', function () {
    $this->getJson('/api/sync/status')
        ->assertStatus(401);
});

it('validates sync batch items', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/sync/batch', [
            'items' => [[
                'client_id' => Str::uuid(),
                'method' => 'GET', // not allowed
                'url' => '/api/user',
                'payload' => [],
                'client_timestamp' => now()->timestamp * 1000,
            ]],
        ])
        ->assertStatus(422);
});

it('rejects a batch with missing required fields', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/sync/batch', ['items' => [['method' => 'POST']]])
        ->assertStatus(422);
});

it('processes a valid sync batch and returns results', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/sync/batch', [
            'items' => [[
                'client_id' => (string) Str::uuid(),
                'method' => 'POST',
                'url' => '/api/user',
                'payload' => ['key' => 'value'],
                'client_timestamp' => now()->timestamp * 1000,
            ]],
        ])
        ->assertStatus(200)
        ->assertJsonStructure(['results' => [['client_id', 'status']]]);
});

it('stores the sync request in the database', function () {
    $user = User::factory()->create();
    $clientId = (string) Str::uuid();

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/sync/batch', [
            'items' => [[
                'client_id' => $clientId,
                'method' => 'POST',
                'url' => '/api/user',
                'payload' => ['key' => 'value'],
                'client_timestamp' => now()->timestamp * 1000,
            ]],
        ])
        ->assertStatus(200);

    $this->assertDatabaseHas('sync_requests', [
        'user_id' => $user->id,
        'client_id' => $clientId,
    ]);
});

it('is idempotent — duplicate client_id returns applied without reprocessing', function () {
    $user = User::factory()->create();
    $clientId = (string) Str::uuid();

    SyncRequest::create([
        'user_id' => $user->id,
        'client_id' => $clientId,
        'method' => 'POST',
        'url' => '/api/user',
        'payload' => ['key' => 'value'],
        'client_timestamp' => now()->timestamp * 1000,
        'status' => 'applied',
        'server_response' => ['id' => 1],
    ]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/sync/batch', [
            'items' => [[
                'client_id' => $clientId,
                'method' => 'POST',
                'url' => '/api/user',
                'payload' => ['key' => 'value'],
                'client_timestamp' => now()->timestamp * 1000,
            ]],
        ])
        ->assertStatus(200)
        ->assertJsonPath('results.0.status', 'applied');
});

it('returns sync status counts for authenticated user', function () {
    $user = User::factory()->create();

    SyncRequest::factory()->create(['user_id' => $user->id, 'status' => 'pending']);
    SyncRequest::factory()->create(['user_id' => $user->id, 'status' => 'applied']);
    SyncRequest::factory()->create(['user_id' => $user->id, 'status' => 'applied']);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/sync/status')
        ->assertStatus(200)
        ->assertJson([
            'pending' => 1,
            'applied' => 2,
            'conflict' => 0,
            'rejected' => 0,
        ]);
});

it('does not include other users sync requests in status', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    SyncRequest::factory()->count(3)->create(['user_id' => $other->id, 'status' => 'pending']);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/sync/status')
        ->assertStatus(200)
        ->assertJson(['pending' => 0, 'applied' => 0]);
});

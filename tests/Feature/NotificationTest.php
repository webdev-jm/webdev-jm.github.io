<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();

    $this->user = User::factory()->create();
});

// --- Notification index ---

it('redirects guests from notifications page', function () {
    $this->get(route('notifications'))->assertRedirect(route('login'));
});

it('authenticated user can view the notifications page', function () {
    $this->actingAs($this->user)->get(route('notifications'))->assertOk();
});

// --- Test notification ---

it('redirects guests from test-notification', function () {
    $this->get(route('test-notification'))->assertRedirect(route('login'));
});

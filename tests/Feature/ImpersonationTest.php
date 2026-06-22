<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();

    Permission::create(['name' => 'user impersonate', 'module' => 'Users', 'description' => '']);

    $adminRole = Role::create(['name' => 'impersonator']);
    $adminRole->givePermissionTo('user impersonate');

    $this->admin = User::factory()->create();
    $this->admin->assignRole('impersonator');

    $this->target = User::factory()->create();
});

// --- Access control ---

it('redirects guests from impersonate start', function () {
    $this->get(route('impersonate.start', encrypt(999)))->assertRedirect(route('login'));
});

it('user without impersonate permission cannot start impersonation', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('impersonate.start', encrypt($this->target->id)))
        ->assertStatus(403);
});

// --- Start impersonation ---

it('admin can start impersonating another user', function () {
    $this->actingAs($this->admin)
        ->get(route('impersonate.start', encrypt($this->target->id)))
        ->assertRedirect('/')
        ->assertSessionHas('impersonate_original_id', $this->admin->id);
});

it('admin cannot impersonate themselves', function () {
    $this->actingAs($this->admin)
        ->get(route('impersonate.start', encrypt($this->admin->id)))
        ->assertRedirect()
        ->assertSessionHas('message_error');
});

// --- Leave impersonation ---

it('admin can leave impersonation and return to original account', function () {
    $this->actingAs($this->admin)
        ->withSession(['impersonate_original_id' => $this->admin->id])
        ->get(route('impersonate.leave'))
        ->assertRedirect(route('user.index'));
});

it('leave impersonation without active session redirects to home', function () {
    $this->actingAs($this->admin)
        ->get(route('impersonate.leave'))
        ->assertRedirect('/');
});

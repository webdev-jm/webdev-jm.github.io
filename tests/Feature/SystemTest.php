<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();

    SystemSetting::create(['data_per_page' => 10, 'email_sending' => 0]);

    Permission::create(['name' => 'system logs', 'module' => 'System', 'description' => '']);
    Permission::create(['name' => 'system settings', 'module' => 'System', 'description' => '']);
    Permission::create(['name' => 'trash bin', 'module' => 'System', 'description' => '']);

    $logsRole = Role::create(['name' => 'logs-viewer']);
    $logsRole->givePermissionTo('system logs');

    $settingsRole = Role::create(['name' => 'settings-viewer']);
    $settingsRole->givePermissionTo('system settings');

    $trashRole = Role::create(['name' => 'trash-viewer']);
    $trashRole->givePermissionTo('trash bin');

    $this->logsUser = User::factory()->create();
    $this->logsUser->assignRole('logs-viewer');

    $this->settingsUser = User::factory()->create();
    $this->settingsUser->assignRole('settings-viewer');

    $this->trashUser = User::factory()->create();
    $this->trashUser->assignRole('trash-viewer');
});

// --- System logs ---

it('redirects guests from system logs', function () {
    $this->get(route('system-logs'))->assertRedirect(route('login'));
});

it('denies users without system logs permission', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('system-logs'))
        ->assertStatus(403);
});

it('allows users with system logs permission to view logs', function () {
    $this->actingAs($this->logsUser)->get(route('system-logs'))->assertOk();
});

// --- System settings ---

it('redirects guests from system settings', function () {
    $this->get(route('system-setting.index'))->assertRedirect(route('login'));
});

it('denies users without system settings permission', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('system-setting.index'))
        ->assertStatus(403);
});

it('allows users with system settings permission to view settings', function () {
    $this->actingAs($this->settingsUser)->get(route('system-setting.index'))->assertOk();
});

// --- Trash bin ---

it('redirects guests from trash bin', function () {
    $this->get(route('trash.index'))->assertRedirect(route('login'));
});

it('denies users without trash bin permission', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('trash.index'))
        ->assertStatus(403);
});

it('allows users with trash bin permission to view trash bin', function () {
    $this->actingAs($this->trashUser)->get(route('trash.index'))->assertOk();
});

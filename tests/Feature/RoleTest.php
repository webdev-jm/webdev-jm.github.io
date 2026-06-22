<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();

    Permission::create(['name' => 'role access', 'module' => 'Roles', 'description' => '']);
    Permission::create(['name' => 'role create', 'module' => 'Roles', 'description' => '']);
    Permission::create(['name' => 'role edit', 'module' => 'Roles', 'description' => '']);

    $this->assignablePermission = Permission::create(['name' => 'test assignable', 'module' => 'Test', 'description' => '']);

    $viewerRole = Role::create(['name' => 'role-viewer']);
    $viewerRole->givePermissionTo('role access');

    $creatorRole = Role::create(['name' => 'role-creator']);
    $creatorRole->givePermissionTo(['role access', 'role create']);

    $editorRole = Role::create(['name' => 'role-editor']);
    $editorRole->givePermissionTo(['role access', 'role create', 'role edit']);

    $this->viewer = User::factory()->create();
    $this->viewer->assignRole('role-viewer');

    $this->creator = User::factory()->create();
    $this->creator->assignRole('role-creator');

    $this->editor = User::factory()->create();
    $this->editor->assignRole('role-editor');
});

// --- Access control ---

it('redirects guests from roles index', function () {
    $this->get(route('role.index'))->assertRedirect(route('login'));
});

it('denies users without role access on index', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('role.index'))
        ->assertStatus(403);
});

it('allows users with role access to view roles index', function () {
    $this->actingAs($this->viewer)->get(route('role.index'))->assertOk();
});

// --- Create ---

it('denies users without role create on create form', function () {
    $this->actingAs($this->viewer)
        ->get(route('role.create'))
        ->assertStatus(403);
});

it('allows users with role create to access create form', function () {
    $this->actingAs($this->creator)->get(route('role.create'))->assertOk();
});

it('user with role create can store a new role', function () {
    $this->actingAs($this->creator)
        ->post(route('role.store'), [
            'name' => 'custom-role',
            'permissions' => [$this->assignablePermission->id],
        ])
        ->assertRedirect(route('role.index'));

    $this->assertDatabaseHas('roles', ['name' => 'custom-role']);
});

it('role store validates name is required', function () {
    $this->actingAs($this->creator)
        ->post(route('role.store'), ['permissions' => [$this->assignablePermission->id]])
        ->assertSessionHasErrors(['name']);
});

it('role store validates permissions are required', function () {
    $this->actingAs($this->creator)
        ->post(route('role.store'), ['name' => 'some-role'])
        ->assertSessionHasErrors(['permissions']);
});

// --- Show ---

it('user with role access can view a role', function () {
    $role = Role::create(['name' => 'viewable-role']);

    $this->actingAs($this->viewer)
        ->get(route('role.show', encrypt($role->id)))
        ->assertOk()
        ->assertSee('viewable-role');
});

// --- Edit / Update ---

it('denies users without role edit on edit form', function () {
    $role = Role::create(['name' => 'some-role']);

    $this->actingAs($this->creator)
        ->get(route('role.edit', encrypt($role->id)))
        ->assertStatus(403);
});

it('allows users with role edit to access edit form', function () {
    $role = Role::create(['name' => 'some-role']);

    $this->actingAs($this->editor)
        ->get(route('role.edit', encrypt($role->id)))
        ->assertOk();
});

it('user with role edit can update a role and sync permissions', function () {
    $role = Role::create(['name' => 'target-role']);
    $newPermission = Permission::create(['name' => 'another permission', 'module' => 'Test', 'description' => '']);

    $this->actingAs($this->editor)
        ->post(route('role.update', encrypt($role->id)), [
            'name' => 'updated-role-name',
            'permissions' => [$newPermission->id],
        ])
        ->assertRedirect();

    $role->refresh();
    expect($role->name)->toBe('updated-role-name');
    expect($role->permissions->pluck('name')->contains('another permission'))->toBeTrue();
});

<?php

use App\Models\OrgStructure;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();

    Permission::create(['name' => 'org structure access', 'module' => 'Org Structures', 'description' => '']);
    Permission::create(['name' => 'org structure create', 'module' => 'Org Structures', 'description' => '']);
    Permission::create(['name' => 'org structure edit', 'module' => 'Org Structures', 'description' => '']);

    $viewerRole = Role::create(['name' => 'org-viewer']);
    $viewerRole->givePermissionTo('org structure access');

    $creatorRole = Role::create(['name' => 'org-creator']);
    $creatorRole->givePermissionTo(['org structure access', 'org structure create']);

    $editorRole = Role::create(['name' => 'org-editor']);
    $editorRole->givePermissionTo(['org structure access', 'org structure create', 'org structure edit']);

    $this->viewer = User::factory()->create();
    $this->viewer->assignRole('org-viewer');

    $this->creator = User::factory()->create();
    $this->creator->assignRole('org-creator');

    $this->editor = User::factory()->create();
    $this->editor->assignRole('org-editor');
});

// --- Access control ---

it('redirects guests from org structures index', function () {
    $this->get(route('org-structure.index'))->assertRedirect(route('login'));
});

it('denies users without org structure access on index', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('org-structure.index'))
        ->assertStatus(403);
});

it('allows users with org structure access to view index', function () {
    $this->actingAs($this->viewer)->get(route('org-structure.index'))->assertOk();
});

// --- Create ---

it('denies users without org structure create on create form', function () {
    $this->actingAs($this->viewer)
        ->get(route('org-structure.create'))
        ->assertStatus(403);
});

it('allows users with org structure create to access create form', function () {
    $this->actingAs($this->creator)->get(route('org-structure.create'))->assertOk();
});

it('user with org structure create can store a new org structure', function () {
    $this->actingAs($this->creator)
        ->post(route('org-structure.store'), ['type' => 'IT Department'])
        ->assertRedirect(route('org-structure.index'));

    $this->assertDatabaseHas('org_structures', ['type' => 'IT Department']);
});

it('org structure store validates type is required', function () {
    $this->actingAs($this->creator)
        ->post(route('org-structure.store'), [])
        ->assertSessionHasErrors(['type']);
});

it('org structure store validates type must be unique', function () {
    OrgStructure::create(['type' => 'Duplicate Dept']);

    $this->actingAs($this->creator)
        ->post(route('org-structure.store'), ['type' => 'Duplicate Dept'])
        ->assertSessionHasErrors(['type']);
});

// --- Show ---

it('user with org structure access can view an org structure', function () {
    $orgStructure = OrgStructure::create(['type' => 'Finance']);

    $this->actingAs($this->viewer)
        ->get(route('org-structure.show', encrypt($orgStructure->id)))
        ->assertOk();
});

// --- Edit / Update ---

it('denies users without org structure edit on edit form', function () {
    $orgStructure = OrgStructure::create(['type' => 'HR']);

    $this->actingAs($this->creator)
        ->get(route('org-structure.edit', encrypt($orgStructure->id)))
        ->assertStatus(403);
});

it('allows users with org structure edit to access edit form', function () {
    $orgStructure = OrgStructure::create(['type' => 'HR']);

    $this->actingAs($this->editor)
        ->get(route('org-structure.edit', encrypt($orgStructure->id)))
        ->assertOk();
});

it('user with org structure edit can update an org structure', function () {
    $orgStructure = OrgStructure::create(['type' => 'Old Type']);

    $this->actingAs($this->editor)
        ->post(route('org-structure.update', encrypt($orgStructure->id)), ['type' => 'New Type'])
        ->assertRedirect();

    $this->assertDatabaseHas('org_structures', ['id' => $orgStructure->id, 'type' => 'New Type']);
});

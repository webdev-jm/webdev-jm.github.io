<?php

use App\Models\Permission;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();

    Permission::create(['name' => 'position access', 'module' => 'Positions', 'description' => '']);
    Permission::create(['name' => 'position create', 'module' => 'Positions', 'description' => '']);
    Permission::create(['name' => 'position edit', 'module' => 'Positions', 'description' => '']);

    $viewerRole = Role::create(['name' => 'position-viewer']);
    $viewerRole->givePermissionTo('position access');

    $creatorRole = Role::create(['name' => 'position-creator']);
    $creatorRole->givePermissionTo(['position access', 'position create']);

    $editorRole = Role::create(['name' => 'position-editor']);
    $editorRole->givePermissionTo(['position access', 'position create', 'position edit']);

    $this->viewer = User::factory()->create();
    $this->viewer->assignRole('position-viewer');

    $this->creator = User::factory()->create();
    $this->creator->assignRole('position-creator');

    $this->editor = User::factory()->create();
    $this->editor->assignRole('position-editor');
});

// --- Access control ---

it('redirects guests from positions index', function () {
    $this->get(route('position.index'))->assertRedirect(route('login'));
});

it('denies users without position access on index', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('position.index'))
        ->assertStatus(403);
});

it('allows users with position access to view position index', function () {
    $this->actingAs($this->viewer)->get(route('position.index'))->assertOk();
});

it('users with position access can search positions', function () {
    Position::create(['position' => 'Software Engineer']);
    Position::create(['position' => 'Product Manager']);

    $this->actingAs($this->viewer)
        ->get(route('position.index', ['search' => 'Software']))
        ->assertSee('Software Engineer')
        ->assertDontSee('Product Manager');
});

// --- Create ---

it('denies users without position create on create form', function () {
    $this->actingAs($this->viewer)
        ->get(route('position.create'))
        ->assertStatus(403);
});

it('allows users with position create to access create form', function () {
    $this->actingAs($this->creator)->get(route('position.create'))->assertOk();
});

it('user with position create can store a new position', function () {
    $this->actingAs($this->creator)
        ->post(route('position.store'), ['position' => 'Senior Developer'])
        ->assertRedirect(route('position.index'));

    $this->assertDatabaseHas('positions', ['position' => 'Senior Developer']);
});

it('position store validates position is required', function () {
    $this->actingAs($this->creator)
        ->post(route('position.store'), [])
        ->assertSessionHasErrors(['position']);
});

it('position store validates position must be unique', function () {
    Position::create(['position' => 'Duplicate Role']);

    $this->actingAs($this->creator)
        ->post(route('position.store'), ['position' => 'Duplicate Role'])
        ->assertSessionHasErrors(['position']);
});

// --- Show ---

it('user with position access can view a position', function () {
    $position = Position::create(['position' => 'Team Lead']);

    $this->actingAs($this->viewer)
        ->get(route('position.show', encrypt($position->id)))
        ->assertOk()
        ->assertSee('Team Lead');
});

// --- Edit / Update ---

it('denies users without position edit on edit form', function () {
    $position = Position::create(['position' => 'Analyst']);

    $this->actingAs($this->creator)
        ->get(route('position.edit', encrypt($position->id)))
        ->assertStatus(403);
});

it('allows users with position edit to access edit form', function () {
    $position = Position::create(['position' => 'Analyst']);

    $this->actingAs($this->editor)
        ->get(route('position.edit', encrypt($position->id)))
        ->assertOk();
});

it('user with position edit can update a position', function () {
    $position = Position::create(['position' => 'Old Title']);

    $this->actingAs($this->editor)
        ->post(route('position.update', encrypt($position->id)), ['position' => 'New Title'])
        ->assertRedirect();

    $this->assertDatabaseHas('positions', ['id' => $position->id, 'position' => 'New Title']);
});

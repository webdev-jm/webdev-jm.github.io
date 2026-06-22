<?php

use App\Models\Company;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();

    Permission::create(['name' => 'company access', 'module' => 'Companies', 'description' => '']);
    Permission::create(['name' => 'company create', 'module' => 'Companies', 'description' => '']);
    Permission::create(['name' => 'company edit', 'module' => 'Companies', 'description' => '']);

    $viewerRole = Role::create(['name' => 'company-viewer']);
    $viewerRole->givePermissionTo('company access');

    $creatorRole = Role::create(['name' => 'company-creator']);
    $creatorRole->givePermissionTo(['company access', 'company create']);

    $editorRole = Role::create(['name' => 'company-editor']);
    $editorRole->givePermissionTo(['company access', 'company create', 'company edit']);

    $this->viewer = User::factory()->create();
    $this->viewer->assignRole('company-viewer');

    $this->creator = User::factory()->create();
    $this->creator->assignRole('company-creator');

    $this->editor = User::factory()->create();
    $this->editor->assignRole('company-editor');
});

// --- Access control ---

it('redirects guests from company index', function () {
    $this->get(route('company.index'))->assertRedirect(route('login'));
});

it('denies users without company access on index', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('company.index'))
        ->assertStatus(403);
});

it('allows users with company access to view company index', function () {
    $this->actingAs($this->viewer)->get(route('company.index'))->assertOk();
});

it('users with company access can search companies', function () {
    Company::factory()->create(['name' => 'Acme Corp']);
    Company::factory()->create(['name' => 'Other Inc']);

    $this->actingAs($this->viewer)
        ->get(route('company.index', ['search' => 'Acme']))
        ->assertSee('Acme Corp')
        ->assertDontSee('Other Inc');
});

// --- Create ---

it('denies users without company create on create form', function () {
    $this->actingAs($this->viewer)
        ->get(route('company.create'))
        ->assertStatus(403);
});

it('allows users with company create to access create form', function () {
    $this->actingAs($this->creator)->get(route('company.create'))->assertOk();
});

it('user with company create can store a new company', function () {
    $this->actingAs($this->creator)
        ->post(route('company.store'), ['name' => 'New Company Ltd'])
        ->assertRedirect(route('company.index'));

    $this->assertDatabaseHas('companies', ['name' => 'New Company Ltd']);
});

it('company store validates name is required', function () {
    $this->actingAs($this->creator)
        ->post(route('company.store'), [])
        ->assertSessionHasErrors(['name']);
});

it('company store validates name must be unique', function () {
    Company::factory()->create(['name' => 'Duplicate Corp']);

    $this->actingAs($this->creator)
        ->post(route('company.store'), ['name' => 'Duplicate Corp'])
        ->assertSessionHasErrors(['name']);
});

// --- Show ---

it('user with company access can view a company', function () {
    $company = Company::factory()->create();

    $this->actingAs($this->viewer)
        ->get(route('company.show', encrypt($company->id)))
        ->assertOk()
        ->assertSee($company->name);
});

// --- Edit / Update ---

it('denies users without company edit on edit form', function () {
    $company = Company::factory()->create();

    $this->actingAs($this->creator)
        ->get(route('company.edit', encrypt($company->id)))
        ->assertStatus(403);
});

it('allows users with company edit to access edit form', function () {
    $company = Company::factory()->create();

    $this->actingAs($this->editor)
        ->get(route('company.edit', encrypt($company->id)))
        ->assertOk();
});

it('user with company edit can update a company', function () {
    $company = Company::factory()->create(['name' => 'Old Name']);

    $this->actingAs($this->editor)
        ->post(route('company.update', encrypt($company->id)), ['name' => 'Updated Name'])
        ->assertRedirect();

    $this->assertDatabaseHas('companies', ['id' => $company->id, 'name' => 'Updated Name']);
});

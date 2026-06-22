<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Company;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        $this->company = Company::factory()->create();

        // Setup granular permissions based on your migration structure
        $permissions = [
            'user access', 'user create', 'user edit',
            'user change password', 'user delete', 'user impersonate'
        ];

        foreach ($permissions as $permission) {
            Permission::create([
                'name' => $permission,
                'module' => 'user',
                'description' => "Allow {$permission}",
            ]);
        }

        // Setup administrative user
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo($permissions);

        $this->admin = User::factory()->create([
            'company_id' => $this->company->id
        ]);
        $this->admin->assignRole('admin');
    }

    /**
     * Test admin can view the user index blade view with search.
     */
    public function test_admin_can_view_user_list_with_search(): void
    {
        User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
        User::factory()->create(['name' => 'Jane Smith', 'email' => 'jane@example.com']);

        $response = $this->actingAs($this->admin)
            ->get(route('user.index', ['search' => 'John']));

        $response->assertStatus(200);

        // Assert the correct Blade template is used
        $response->assertViewIs('pages.users.index');

        // Assert the view has the 'users' variable
        $response->assertViewHas('users');

        $response->assertSee('John Doe');
    }

    /**
     * Test admin can access the edit blade view with correct data.
     */
    public function test_admin_can_access_edit_user_view(): void
    {
        $user = User::factory()->create();
        $encryptedId = encrypt($user->id);

        $response = $this->actingAs($this->admin)
            ->get(route('user.edit', $encryptedId));

        $response->assertStatus(200);

        // Verify the specific edit page template
        $response->assertViewIs('pages.users.edit');

        // Verify that the user data is passed correctly to the view
        $response->assertViewHas('user', function ($viewUser) use ($user) {
            return $viewUser->id === $user->id;
        });
    }

    public function test_admin_can_store_a_new_user(): void
    {
        Role::create(['name' => 'editor']);

        $response = $this->actingAs($this->admin)
            ->post(route('user.store'), [
                'name' => 'New User',
                'email' => 'newuser@example.com',
                'company_id' => encrypt($this->company->id),
                'role_ids' => 'editor',
            ]);

        $response->assertRedirect(route('user.index'));
        $this->assertDatabaseHas('users', ['email' => 'newuser@example.com']);

        $user = User::where('email', 'newuser@example.com')->first();
        $this->assertTrue($user->hasRole('editor'));
    }

    public function test_admin_can_soft_delete_and_restore_a_user(): void
    {
        $user = User::factory()->create();
        $encryptedId = encrypt($user->id);

        $user->delete();
        $this->assertTrue($user->fresh()->trashed());

        $response = $this->actingAs($this->admin)
            ->post(route('user.restore', $encryptedId));

        $response->assertRedirect();
        $this->assertFalse($user->fresh()->trashed());
    }
}

<?php

use App\Livewire\DarkmodeToggle;
use App\Livewire\DeleteModel;
use App\Livewire\SkinSwitcher;
use App\Livewire\Notification;
use App\Livewire\OnlineUsers;
use App\Livewire\OrgStructures\Maintenance;
use App\Livewire\Roles\Users as RolesUsers;
use App\Livewire\SystemSettings;
use App\Livewire\Tickets\NavbarIcon;
use App\Livewire\User\Password;
use App\Livewire\Users\Activities;
use App\Livewire\Users\Settings;
use App\Models\OrgStructure;
use App\Models\OrgStructureTree;
use App\Models\Permission;
use App\Models\Role;
use App\Models\SystemSetting;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
    $this->user = User::factory()->create();
});

// ---------------------------------------------------------------------------
// DarkmodeToggle
// ---------------------------------------------------------------------------

describe('DarkmodeToggle', function () {
    it('renders successfully', function () {
        Livewire::actingAs($this->user)
            ->test(DarkmodeToggle::class)
            ->assertSuccessful();
    });

    it('enables dark mode when it is off', function () {
        $this->user->update(['dark_mode' => 0]);

        Livewire::actingAs($this->user)
            ->test(DarkmodeToggle::class)
            ->call('changeMode');

        expect($this->user->fresh()->dark_mode)->toBe(1);
    });

    it('disables dark mode when it is on', function () {
        $this->user->update(['dark_mode' => 1]);

        Livewire::actingAs($this->user)
            ->test(DarkmodeToggle::class)
            ->call('changeMode');

        expect($this->user->fresh()->dark_mode)->toBe(0);
    });
});

// ---------------------------------------------------------------------------
// SkinSwitcher
// ---------------------------------------------------------------------------

describe('SkinSwitcher', function () {
    it('renders successfully', function () {
        Livewire::actingAs($this->user)
            ->test(SkinSwitcher::class)
            ->assertSuccessful();
    });

    it('switches skin to glass', function () {
        $this->user->update(['skin' => 'default']);

        Livewire::actingAs($this->user)
            ->test(SkinSwitcher::class)
            ->call('switchSkin', 'glass')
            ->assertRedirect();

        expect($this->user->fresh()->skin)->toBe('glass');
    });

    it('switches skin to neumorphic', function () {
        $this->user->update(['skin' => 'default']);

        Livewire::actingAs($this->user)
            ->test(SkinSwitcher::class)
            ->call('switchSkin', 'neumorphic')
            ->assertRedirect();

        expect($this->user->fresh()->skin)->toBe('neumorphic');
    });

    it('switches skin back to default', function () {
        $this->user->update(['skin' => 'glass']);

        Livewire::actingAs($this->user)
            ->test(SkinSwitcher::class)
            ->call('switchSkin', 'default')
            ->assertRedirect();

        expect($this->user->fresh()->skin)->toBe('default');
    });

    it('switches skin to claymorphic', function () {
        $this->user->update(['skin' => 'default']);

        Livewire::actingAs($this->user)
            ->test(SkinSwitcher::class)
            ->call('switchSkin', 'claymorphic')
            ->assertRedirect();

        expect($this->user->fresh()->skin)->toBe('claymorphic');
    });

    it('contains claymorphic in the skins array', function () {
        $component = Livewire::actingAs($this->user)
            ->test(SkinSwitcher::class);

        expect($component->get('skins'))->toHaveKey('claymorphic');
    });

    it('switches from claymorphic back to default', function () {
        $this->user->update(['skin' => 'claymorphic']);

        Livewire::actingAs($this->user)
            ->test(SkinSwitcher::class)
            ->call('switchSkin', 'default')
            ->assertRedirect();

        expect($this->user->fresh()->skin)->toBe('default');
    });

    it('ignores unknown skin values', function () {
        $this->user->update(['skin' => 'default']);

        Livewire::actingAs($this->user)
            ->test(SkinSwitcher::class)
            ->call('switchSkin', 'invalid-skin');

        expect($this->user->fresh()->skin)->toBe('default');
    });
});

// ---------------------------------------------------------------------------
// OnlineUsers
// ---------------------------------------------------------------------------

describe('OnlineUsers', function () {
    it('renders successfully', function () {
        Livewire::actingAs($this->user)
            ->test(OnlineUsers::class)
            ->assertSuccessful();
    });

    it('shows users active in the last hour', function () {
        $activeUser = User::factory()->create(['last_activity' => now()->subMinutes(30)]);
        $inactiveUser = User::factory()->create(['last_activity' => now()->subHours(2)]);

        Livewire::actingAs($this->user)
            ->test(OnlineUsers::class)
            ->assertSee($activeUser->name)
            ->assertDontSee($inactiveUser->name);
    });

    it('does not show users with no activity', function () {
        $noActivityUser = User::factory()->create(['last_activity' => null]);

        Livewire::actingAs($this->user)
            ->test(OnlineUsers::class)
            ->assertDontSee($noActivityUser->name);
    });
});

// ---------------------------------------------------------------------------
// User\Password
// ---------------------------------------------------------------------------

describe('User\Password', function () {
    it('renders successfully', function () {
        Livewire::actingAs($this->user)
            ->test(Password::class, ['user' => $this->user, 'type' => 'admin'])
            ->assertSuccessful();
    });

    it('updates password without current password check for non-profile type', function () {
        Livewire::actingAs($this->user)
            ->test(Password::class, ['user' => $this->user, 'type' => 'admin'])
            ->set('password', 'newpassword123')
            ->set('password_confirmation', 'newpassword123')
            ->call('submitForm')
            ->assertHasNoErrors();

        expect(Hash::check('newpassword123', $this->user->fresh()->password))->toBeTrue();
    });

    it('requires current password when type is profile', function () {
        Livewire::actingAs($this->user)
            ->test(Password::class, ['user' => $this->user, 'type' => 'profile'])
            ->set('password', 'newpassword123')
            ->set('password_confirmation', 'newpassword123')
            ->call('submitForm')
            ->assertHasErrors(['current_password']);
    });

    it('rejects wrong current password for profile type', function () {
        Livewire::actingAs($this->user)
            ->test(Password::class, ['user' => $this->user, 'type' => 'profile'])
            ->set('current_password', 'wrongpassword')
            ->set('password', 'newpassword123')
            ->set('password_confirmation', 'newpassword123')
            ->call('submitForm')
            ->assertHasErrors(['current_password']);
    });

    it('updates password with correct current password for profile type', function () {
        Livewire::actingAs($this->user)
            ->test(Password::class, ['user' => $this->user, 'type' => 'profile'])
            ->set('current_password', 'p4ssw0rd')
            ->set('password', 'newpassword123')
            ->set('password_confirmation', 'newpassword123')
            ->call('submitForm')
            ->assertHasNoErrors();

        expect(Hash::check('newpassword123', $this->user->fresh()->password))->toBeTrue();
    });

    it('requires password confirmation to match', function () {
        Livewire::actingAs($this->user)
            ->test(Password::class, ['user' => $this->user, 'type' => 'admin'])
            ->set('password', 'newpassword123')
            ->set('password_confirmation', 'mismatch')
            ->call('submitForm')
            ->assertHasErrors(['password']);
    });

    it('resets fields after successful password update', function () {
        Livewire::actingAs($this->user)
            ->test(Password::class, ['user' => $this->user, 'type' => 'admin'])
            ->set('password', 'newpassword123')
            ->set('password_confirmation', 'newpassword123')
            ->call('submitForm')
            ->assertSet('password', '')
            ->assertSet('password_confirmation', '');
    });
});

// ---------------------------------------------------------------------------
// Users\Settings
// ---------------------------------------------------------------------------

describe('Users\Settings', function () {
    it('renders with user name and email populated', function () {
        Livewire::actingAs($this->user)
            ->test(Settings::class, ['user' => $this->user])
            ->assertSet('name', $this->user->name)
            ->assertSet('email', $this->user->email);
    });

    it('saves updated name and email', function () {
        Livewire::actingAs($this->user)
            ->test(Settings::class, ['user' => $this->user])
            ->set('name', 'Updated Name')
            ->set('email', 'updated@example.com')
            ->call('saveSettings')
            ->assertHasNoErrors();

        expect($this->user->fresh()->name)->toBe('Updated Name')
            ->and($this->user->fresh()->email)->toBe('updated@example.com');
    });

    it('requires name', function () {
        Livewire::actingAs($this->user)
            ->test(Settings::class, ['user' => $this->user])
            ->set('name', '')
            ->call('saveSettings')
            ->assertHasErrors(['name']);
    });

    it('requires email to be unique', function () {
        User::factory()->create(['email' => 'taken@example.com']);

        Livewire::actingAs($this->user)
            ->test(Settings::class, ['user' => $this->user])
            ->set('email', 'taken@example.com')
            ->call('saveSettings')
            ->assertHasErrors(['email']);
    });

    it('allows saving own email without unique violation', function () {
        Livewire::actingAs($this->user)
            ->test(Settings::class, ['user' => $this->user])
            ->set('name', $this->user->name)
            ->set('email', $this->user->email)
            ->call('saveSettings')
            ->assertHasNoErrors();
    });

    it('shows success message after saving', function () {
        Livewire::actingAs($this->user)
            ->test(Settings::class, ['user' => $this->user])
            ->set('name', 'New Name')
            ->set('email', $this->user->email)
            ->call('saveSettings')
            ->assertSet('msg', 'Profile details has been updated.');
    });
});

// ---------------------------------------------------------------------------
// Users\Activities
// ---------------------------------------------------------------------------

describe('Users\Activities', function () {
    it('renders successfully', function () {
        Livewire::actingAs($this->user)
            ->test(Activities::class, ['user' => $this->user])
            ->assertSuccessful();
    });

    it('filters activities by search term', function () {
        activity('updated')
            ->causedBy($this->user)
            ->performedOn($this->user)
            ->withProperties(['old' => [], 'changes' => []])
            ->log('profile details updated');

        activity('created')
            ->causedBy($this->user)
            ->performedOn($this->user)
            ->log('user created something else');

        Livewire::actingAs($this->user)
            ->test(Activities::class, ['user' => $this->user])
            ->set('search', 'profile details')
            ->assertSee('profile details updated')
            ->assertDontSee('user created something else');
    });
});

// ---------------------------------------------------------------------------
// SystemSettings
// ---------------------------------------------------------------------------

describe('SystemSettings', function () {
    beforeEach(function () {
        SystemSetting::create(['data_per_page' => 10, 'email_sending' => 0]);
    });

    it('renders with existing settings populated', function () {
        Livewire::actingAs($this->user)
            ->test(SystemSettings::class)
            ->assertSet('data_per_page', 10)
            ->assertSet('email_sending', 0);
    });

    it('saves updated settings and redirects', function () {
        Livewire::actingAs($this->user)
            ->test(SystemSettings::class)
            ->set('data_per_page', 25)
            ->set('email_sending', 1)
            ->call('saveSetting')
            ->assertRedirect(route('system-setting.index'));

        $setting = SystemSetting::first();
        expect($setting->data_per_page)->toBe(25)
            ->and($setting->email_sending)->toBe(1);
    });

    it('requires data_per_page', function () {
        Livewire::actingAs($this->user)
            ->test(SystemSettings::class)
            ->set('data_per_page', '')
            ->call('saveSetting')
            ->assertHasErrors(['data_per_page']);
    });
});

// ---------------------------------------------------------------------------
// DeleteModel
// ---------------------------------------------------------------------------

describe('DeleteModel', function () {
    it('renders successfully', function () {
        Livewire::actingAs($this->user)
            ->test(DeleteModel::class)
            ->assertSuccessful();
    });

    it('sets model via setModel call with encrypted id', function () {
        $target = User::factory()->create();

        Livewire::actingAs($this->user)
            ->test(DeleteModel::class)
            ->call('setModel', 'User', encrypt($target->id))
            ->assertSet('type', 'User')
            ->assertSet('name', $target->name);
    });

    it('throws exception for invalid model type in setModel', function () {
        Livewire::actingAs($this->user)
            ->test(DeleteModel::class)
            ->call('setModel', 'InvalidType', encrypt(1));
    })->throws(InvalidArgumentException::class);

    it('shows error for wrong password on delete attempt', function () {
        $target = User::factory()->create();

        Livewire::actingAs($this->user)
            ->test(DeleteModel::class)
            ->call('setModel', 'User', encrypt($target->id))
            ->set('password', 'wrongpassword')
            ->call('submitForm')
            ->assertSet('error_message', 'incorrect password.');

        $this->assertDatabaseHas('users', ['id' => $target->id]);
    });

    it('deletes model with correct password and redirects', function () {
        $target = User::factory()->create();

        Livewire::actingAs($this->user)
            ->test(DeleteModel::class)
            ->call('setModel', 'User', encrypt($target->id))
            ->set('password', 'p4ssw0rd')
            ->call('submitForm')
            ->assertRedirect('/users');

        $this->assertDatabaseMissing('users', ['id' => $target->id, 'deleted_at' => null]);
    });

    it('requires password field', function () {
        $target = User::factory()->create();

        Livewire::actingAs($this->user)
            ->test(DeleteModel::class)
            ->call('setModel', 'User', encrypt($target->id))
            ->call('submitForm')
            ->assertHasErrors(['password']);
    });
});

// ---------------------------------------------------------------------------
// Notification
// ---------------------------------------------------------------------------

describe('Notification', function () {
    it('renders successfully', function () {
        Livewire::actingAs($this->user)
            ->test(Notification::class)
            ->assertSuccessful();
    });

    it('initialises with zero unread count when user has no notifications', function () {
        Livewire::actingAs($this->user)
            ->test(Notification::class)
            ->assertSet('count', 0);
    });

    it('marks a notification as read', function () {
        Illuminate\Support\Facades\Notification::send(
            $this->user,
            new class extends Illuminate\Notifications\Notification
            {
                public function via($notifiable): array
                {
                    return ['database'];
                }

                public function toArray($notifiable): array
                {
                    return ['title' => 'Test', 'message' => 'Test notification'];
                }
            }
        );

        $notification = $this->user->notifications()->first();

        Livewire::actingAs($this->user)
            ->test(Notification::class)
            ->call('markAsRead', $notification->id);

        expect($this->user->unreadNotifications()->count())->toBe(0);
    });

    it('loads up to 5 notifications', function () {
        $anonymousNotification = new class extends Illuminate\Notifications\Notification
        {
            public function via($notifiable): array
            {
                return ['database'];
            }

            public function toArray($notifiable): array
            {
                return ['title' => 'Test', 'message' => 'Test message'];
            }
        };

        foreach (range(1, 7) as $i) {
            Illuminate\Support\Facades\Notification::send($this->user, $anonymousNotification);
        }

        $component = Livewire::actingAs($this->user)
            ->test(Notification::class);

        expect($component->get('notifications'))->toHaveCount(5);
    });
});

// ---------------------------------------------------------------------------
// Roles\Users
// ---------------------------------------------------------------------------

describe('Roles\Users', function () {
    it('renders users assigned to a role', function () {
        $role = Role::create(['name' => 'editor']);
        $roleUser = User::factory()->create();
        $roleUser->assignRole('editor');
        $otherUser = User::factory()->create();

        Livewire::actingAs($this->user)
            ->test(RolesUsers::class, ['role' => $role])
            ->assertSee($roleUser->name)
            ->assertDontSee($otherUser->name);
    });

    it('renders successfully with no users in role', function () {
        $role = Role::create(['name' => 'empty-role']);

        Livewire::actingAs($this->user)
            ->test(RolesUsers::class, ['role' => $role])
            ->assertSuccessful();
    });
});

// ---------------------------------------------------------------------------
// Tickets\NavbarIcon
// ---------------------------------------------------------------------------

describe('Tickets\NavbarIcon', function () {
    beforeEach(function () {
        Permission::create(['name' => 'ticket response', 'module' => 'ticket', 'description' => 'Can respond to tickets']);
    });

    it('renders successfully', function () {
        Livewire::actingAs($this->user)
            ->test(NavbarIcon::class)
            ->assertSuccessful();
    });

    it('counts only own open and in_progress tickets for regular user', function () {
        Ticket::factory()->create(['user_id' => $this->user->id, 'status' => 'open']);
        Ticket::factory()->create(['user_id' => $this->user->id, 'status' => 'resolved']);
        Ticket::factory()->create(['status' => 'open']); // another user's ticket

        $component = Livewire::actingAs($this->user)->test(NavbarIcon::class);

        expect($component->get('count'))->toBe(1);
    });

    it('counts all open and in_progress tickets for responder', function () {
        $responderRole = Role::create(['name' => 'responder']);
        $responderRole->givePermissionTo('ticket response');
        $responder = User::factory()->create();
        $responder->assignRole('responder');

        Ticket::factory()->create(['status' => 'open']);
        Ticket::factory()->create(['status' => 'in_progress']);
        Ticket::factory()->create(['status' => 'resolved']);

        $component = Livewire::actingAs($responder)->test(NavbarIcon::class);

        expect($component->get('count'))->toBe(2);
    });

    it('limits recent tickets to 5', function () {
        Ticket::factory()->count(8)->create(['user_id' => $this->user->id]);

        $component = Livewire::actingAs($this->user)->test(NavbarIcon::class);

        expect($component->get('recentTickets'))->toHaveCount(5);
    });
});

// ---------------------------------------------------------------------------
// OrgStructures\Maintenance
// ---------------------------------------------------------------------------

describe('OrgStructures\Maintenance', function () {
    beforeEach(function () {
        $this->orgStructure = OrgStructure::create(['user_id' => null, 'type' => 'department']);
    });

    it('renders successfully', function () {
        Livewire::actingAs($this->user)
            ->test(Maintenance::class, ['org_structure' => $this->orgStructure])
            ->assertSuccessful();
    });

    it('mounts in add mode', function () {
        Livewire::actingAs($this->user)
            ->test(Maintenance::class, ['org_structure' => $this->orgStructure])
            ->assertSet('type', 'add');
    });

    it('creates a new structure tree entry', function () {
        Livewire::actingAs($this->user)
            ->test(Maintenance::class, ['org_structure' => $this->orgStructure])
            ->set('title', 'CEO')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('org_structure_trees', [
            'org_structure_id' => $this->orgStructure->id,
            'title' => 'CEO',
        ]);
    });

    it('requires title when saving', function () {
        Livewire::actingAs($this->user)
            ->test(Maintenance::class, ['org_structure' => $this->orgStructure])
            ->set('title', '')
            ->call('save')
            ->assertHasErrors(['title']);
    });

    it('resets form after saving', function () {
        Livewire::actingAs($this->user)
            ->test(Maintenance::class, ['org_structure' => $this->orgStructure])
            ->set('title', 'Manager')
            ->call('save')
            ->assertSet('title', null)
            ->assertSet('type', 'add');
    });

    it('switches to edit mode with correct data', function () {
        $tree = OrgStructureTree::create([
            'org_structure_id' => $this->orgStructure->id,
            'title' => 'VP of Engineering',
            'user_id' => null,
            'reports_to_id' => null,
        ]);

        Livewire::actingAs($this->user)
            ->test(Maintenance::class, ['org_structure' => $this->orgStructure])
            ->call('editStructure', encrypt($tree->id))
            ->assertSet('type', 'edit')
            ->assertSet('title', 'VP of Engineering');
    });

    it('updates an existing structure tree entry', function () {
        $tree = OrgStructureTree::create([
            'org_structure_id' => $this->orgStructure->id,
            'title' => 'Old Title',
            'user_id' => null,
            'reports_to_id' => null,
        ]);

        Livewire::actingAs($this->user)
            ->test(Maintenance::class, ['org_structure' => $this->orgStructure])
            ->call('editStructure', encrypt($tree->id))
            ->set('title', 'New Title')
            ->call('save')
            ->assertHasNoErrors();

        expect($tree->fresh()->title)->toBe('New Title');
    });

    it('confirms and deletes a structure', function () {
        $tree = OrgStructureTree::create([
            'org_structure_id' => $this->orgStructure->id,
            'title' => 'To Delete',
            'user_id' => null,
            'reports_to_id' => null,
        ]);

        Livewire::actingAs($this->user)
            ->test(Maintenance::class, ['org_structure' => $this->orgStructure])
            ->call('confirmDelete', $tree->id)
            ->assertSet('delete_id', $tree->id)
            ->call('deleteStructure');

        $this->assertSoftDeleted('org_structure_trees', ['id' => $tree->id]);
    });

    it('cancels delete confirmation', function () {
        Livewire::actingAs($this->user)
            ->test(Maintenance::class, ['org_structure' => $this->orgStructure])
            ->call('confirmDelete', 99)
            ->call('cancelDelete')
            ->assertSet('delete_id', null);
    });
});

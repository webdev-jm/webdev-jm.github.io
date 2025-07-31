<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions_arr = [
            'Org Structures' => [
                'org structure access'  => 'Allow user to access org structure list and details.',
                'org structure create'  => 'Allow user to create org structure.',
                'org structure edit'    => 'Allow user to edit org structure.',
                'org structure delete'  => 'Allow user to delete org structure.',
            ],
            'Positions' => [
                'position access'   => 'Allow user to access position list and details',
                'position create'   => 'Allow user to create position.',
                'position edit'     => 'Allow user to edit position details.',
                'position delete'   => 'Allow user to delete position.'
            ],
            'Companies' => [
                'company access'    => 'Allow user to access company list and details.',
                'company create'    => 'Allow user to create company.',
                'company edit'      => 'Allow user to edit company details.',
                'company delete'    => 'Allow user to delete company.'
            ],
            'Users' => [
                'user access'           => 'Allow user to access user list and details',
                'user create'           => 'Allow user to create user.',
                'user edit'             => 'Allow user to edit user details.',
                'user change password'  => 'Allow user to change password of a user.',
                'user delete'           => 'Allow user to delete user.'
            ],
            'Roles' => [
                'role access'   => 'Allow user to access role list and details',
                'role create'   => 'Allow user to create role.',
                'role edit'     => 'Allow user to edit role details.',
                'role delete'   => 'Allow user to delete role.'
            ],
            'System' => [
                'system settings'   => 'Allow user to access system settings.',
                'system logs'       => 'Allow user to access system logs.'
            ]
        ];

        foreach($permissions_arr as $module => $permissions) {
            foreach($permissions as $permission => $description) {
                Permission::create([
                    'name' => $permission,
                    'module' => $module,
                    'description' => $description,
                ]);
            }
        }
    }
}

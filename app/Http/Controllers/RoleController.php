<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleAddRequest;
use App\Http\Requests\RoleEditRequest;

use Illuminate\Http\Request;

use App\Models\Role;
use App\Models\Permission;

use App\Http\Traits\SettingTrait;

class RoleController extends Controller
{
    use SettingTrait;

    public function index(Request $request) {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // get search param
        $search = trim($request->get('search') ?? '');

        // retrieve roles data
        $roles = Role::orderBy('id', 'DESC')
            ->when(!empty($search), function($query) use($search) {
                $query->where('name', 'like', '%'.$search.'%');
            })
            ->paginate($this->getDataPerPage())->onEachSide(1)
            ->appends(request()->query());

        return view('pages.roles.index')->with([
            'roles' => $roles,
            'search' => $search,
        ]);
    }

    public function create() {

        $permissions = Permission::all();
        $permissions_arr = [];
        foreach($permissions as $permission) {
            $permissions_arr[$permission->module][$permission->id] = [
                'name' => $permission->name,
                'description' => $permission->description
            ];
        }

        return view('pages.roles.create')->with([
            'permissions' => $permissions_arr
        ]);
    }

    public function store(RoleAddRequest $request) {
        $role = Role::create(['name' => $request->name])->givePermissionTo($request->permissions);

        // logs
        activity('created')
            ->performedOn($role)
            ->log(':causer.name has created a role [ :subject.name ]');

        return redirect()->route('role.index')->with([
            'message_success' => __('adminlte::roles.role_create_success')
        ]);
    }

    public function show($id) {
        $role = Role::findOrFail(decrypt($id));

        $permissions_arr = array();
        foreach($role->permissions as $permission) {
            $permissions_arr[$permission->module][$permission->id] = [
                'name' => $permission->name,
                'description' => $permission->description
            ];
        }

        return view('pages.roles.show')->with([
            'role' => $role,
            'permissions_arr' => $permissions_arr
        ]);
    }

    public function edit($id) {
        $id = decrypt($id);
        $role = Role::findOrFail($id);

        $permissions = Permission::all();
        $permissions_arr = [];
        foreach($permissions as $permission) {
            $permissions_arr[$permission->module][$permission->id] = [
                'name' => $permission->name,
                'description' => $permission->description
            ];
        }

        return view('pages.roles.edit')->with([
            'role' => $role,
            'permissions' => $permissions_arr
        ]);
    }

    public function update(RoleEditRequest $request, $id) {
        $id = decrypt($id);
        $role = Role::findOrFail($id);

        $changes_arr['old'] = $role->getOriginal();
        $changes_arr['old']['arr'] = $role->permissions()->pluck('name');

        $role->update([
            'name' => $request->name
        ]);
        $role->syncPermissions($request->permissions);

        $changes_arr['changes'] = $role->getChanges();
        $changes_arr['changes']['arr'] = $role->permissions()->pluck('name');

        // logs
        activity('updated')
        ->performedOn($role)
        ->withProperties($changes_arr)
        ->log(':causer.name has updated role :subject.name');

        return back()->with([
            'message_success' => __('adminlte::roles.role_update_success')
        ]);
    }

}

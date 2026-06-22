<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserAddRequest;
use App\Http\Requests\UserEditRequest;
use App\Http\Traits\SettingTrait;
use App\Models\Company;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    use SettingTrait;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = trim($request->get('search'));

        $users = User::orderBy('created_at', 'DESC')
            ->when(! empty($search), function ($query) use ($search) {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%');
            })
            ->paginate($this->getDataPerPage())
            ->appends(request()->query());

        return view('pages.users.index')->with([
            'search' => $search,
            'users' => $users,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $companies = Company::select('id', 'name')->orderBy('name')->get();
        $companies_arr = [];
        foreach ($companies as $company) {
            $companies_arr[encrypt($company->id)] = $company->name;
        }

        $positions = Position::select('id', 'position')->orderBy('position')->get();
        $positions_arr = [];
        foreach ($positions as $position) {
            $positions_arr[encrypt($position->id)] = $position->position;
        }

        $roles = Role::orderBy('name', 'ASC')
            ->get();

        return view('pages.users.create')->with([
            'companies' => $companies_arr,
            'positions' => $positions_arr,
            'roles' => $roles,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserAddRequest $request)
    {
        $email_arr = explode('@', $request->email);
        $password = Hash::make(reset($email_arr).'123!');

        $user = new User([
            'company_id' => decrypt($request->company_id),
            'position_id' => ! empty($request->position_id) ? decrypt($request->position_id) : null,
            'name' => $request->name,
            'email' => $request->email,
            'password' => $password,
        ]);
        $user->save();

        $role_ids = explode(',', $request->role_ids);
        $user->assignRole($role_ids);

        // logs
        activity('created')
            ->performedOn($user)
            ->log(':causer.name has created user :subject.name');

        return redirect()->route('user.index')->with([
            'message_success' => __('adminlte::users.user_create_success'),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $user = User::findOrFail(decrypt($id));

        return view('pages.users.show')->with([
            'user' => $user,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $user = User::findOrFail(decrypt($id));

        $companies = Company::select('id', 'name')->orderBy('name')->get();
        $companies_arr = [];
        $company_selected_id = '';
        foreach ($companies as $company) {
            $encrypted_id = encrypt($company->id);
            if ($user->company_id == $company->id) {
                $company_selected_id = $encrypted_id;
            }

            $companies_arr[$encrypted_id] = $company->name;
        }

        $positions = Position::select('id', 'position')->orderBy('position')->get();
        $positions_arr = [];
        $position_selected_id = '';
        foreach ($positions as $position) {
            $encrypted_id = encrypt($position->id);
            if ($user->position_id == $position->id) {
                $position_selected_id = $encrypted_id;
            }

            $positions_arr[$encrypted_id] = $position->position;
        }

        $roles = Role::orderBy('name', 'ASC')
            ->get();

        $user_roles = $user->roles->pluck('name')->toArray();

        return view('pages.users.edit')->with([
            'user' => $user,
            'companies' => $companies_arr,
            'positions' => $positions_arr,
            'roles' => $roles,
            'company_selected_id' => $company_selected_id,
            'position_selected_id' => $position_selected_id,
            'user_roles' => $user_roles,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserEditRequest $request, $id)
    {
        $user = User::findOrFail(decrypt($id));
        $user_roles = $user->roles->pluck('name')->toArray();

        $changes_arr['old'] = $user->getOriginal();
        $changes_arr['old']['arr'] = $user->roles->pluck('name');

        $user->update([
            'company_id' => decrypt($request->company_id),
            'position_id' => ! empty($request->position_id) ? decrypt($request->position_id) : null,
            'name' => $request->name,
            'email' => $request->email,
        ]);

        $role_ids = explode(',', $request->role_ids);
        $user->syncRoles($role_ids);

        $changes_arr['changes'] = $user->getChanges();
        $changes_arr['changes']['arr'] = $user->roles->pluck('name');

        // logs
        activity('updated')
            ->performedOn($user)
            ->withProperties($changes_arr)
            ->log(':causer.name has updated user :subject.name');

        return back()->with([
            'message_success' => __('adminlte::users.user_update_success'),
        ]);
    }

    public function profile($id)
    {
        $user = User::findOrFail(decrypt($id));

        return view('profile')->with([
            'user' => $user,
        ]);
    }

    /**
     * Display a listing of the soft-deleted users.
     */
    public function trash(Request $request)
    {
        $search = trim($request->get('search'));

        // Use onlyTrashed() to get ONLY the soft-deleted records
        $users = User::onlyTrashed()
            ->orderBy('deleted_at', 'DESC')
            ->when(! empty($search), function ($query) use ($search) {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%');
            })
            ->paginate($this->getDataPerPage())
            ->appends(request()->query());

        return view('pages.users.trash')->with([
            'search' => $search,
            'users' => $users,
        ]);
    }

    /**
     * Restore a soft-deleted user.
     */
    public function restore($id)
    {
        // Find the user even if they are trashed, then restore them
        $user = User::onlyTrashed()->findOrFail(decrypt($id));
        $user->restore();

        activity('restored')
            ->performedOn($user)
            ->log(':causer.name has restored user :subject.name');

        return back()->with([
            'message_success' => 'User restored successfully.',
        ]);
    }

    /**
     * Permanently delete a user from the database.
     */
    public function forceDelete($id)
    {
        $user = User::onlyTrashed()->findOrFail(decrypt($id));

        // Optional: Delete their profile picture from storage here before deleting the record

        $user->forceDelete();

        return back()->with([
            'message_success' => 'User permanently deleted.',
        ]);
    }
}

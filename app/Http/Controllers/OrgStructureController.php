<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrgStructure;

use App\Http\Traits\SettingTrait;

use App\Http\Requests\OrgStructureAddRequest;
use App\Http\Requests\OrgStructureEditRequest;

class OrgStructureController extends Controller
{
    use SettingTrait;

    public function index(Request $request) {
        $search = trim($request->get('search'));

        $org_structures = OrgStructure::orderBy('created_at', 'DESC')
            ->when(!empty($search), function($query) use($search) {
                $query->where('type', 'like', '%'.$search.'%');
            })
            ->paginate($this->getDataPerPage())
            ->appends(request()->query());

        return view('pages.org-structures.index')->with([
            'search' => $search,
            'org_structures' => $org_structures
        ]);
    }

    public function create() {
        return view('pages.org-structures.create');
    }

    public function store(OrgStructureAddRequest $request) {
        $org_structure = new OrgStructure([
            'type' => $request->type
        ]);
        $org_structure->save();

        // logs
        activity('created')
            ->performedOn($org_structure)
            ->log(':causer.name has created a Org structure :subject.type');

        return redirect()->route('org-structure.index')->with([
            'message_success' => __('adminlte::org-structures.org_structure_create_success')
        ]);
    }

    public function show($id) {
        $org_structure = OrgStructure::findOrFail(decrypt($id));

        return view('pages.org-structures.show')->with([
            'org_structure' => $org_structure
        ]);
    }

    public function edit($id) {
        $org_structure = OrgStructure::findOrFail(decrypt($id));

        return view('pages.org-structures.edit')->with([
            'org_structure' => $org_structure
        ]);
    }

    public function update(OrgStructureEditRequest $request, $id) {
        $org_structure = OrgStructure::findOrFail(decrypt($id));

        $changes_arr['old'] = $org_structure->getOriginal();

        $org_structure->update([
            'type' => $request->type
        ]);

        $changes_arr['changes'] = $org_structure->getChanges();

        // logs
        activity('updated')
            ->performedOn($org_structure)
            ->withProperties($changes_arr)
            ->log(':causer.name has updated a org structure :subject.type');

        return back()->with([
            'message_success' => __('adminlte::org-structures.org_structure_update_success')
        ]);
    }
}

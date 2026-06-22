<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrgStructureAddRequest;
use App\Http\Requests\OrgStructureEditRequest;
use App\Http\Traits\SettingTrait;
use App\Models\OrgStructure;
use App\Models\OrgStructureTree;
use App\Models\User;
use Illuminate\Http\Request;

class OrgStructureController extends Controller
{
    use SettingTrait;

    public function index(Request $request)
    {
        $search = trim($request->get('search'));

        $org_structures = OrgStructure::orderBy('created_at', 'DESC')
            ->when(! empty($search), function ($query) use ($search) {
                $query->where('type', 'like', '%'.$search.'%');
            })
            ->paginate($this->getDataPerPage())
            ->appends(request()->query());

        return view('pages.org-structures.index')->with([
            'search' => $search,
            'org_structures' => $org_structures,
        ]);
    }

    public function create()
    {
        $users = User::select('id', 'name')->orderBy('name')->get();

        return view('pages.org-structures.create')->with([
            'users' => $users,
        ]);
    }

    public function store(OrgStructureAddRequest $request)
    {
        $org_structure = new OrgStructure([
            'user_id' => $request->user_id ? decrypt($request->user_id) : null,
            'type' => $request->type,
        ]);
        $org_structure->save();

        // logs
        activity('created')
            ->performedOn($org_structure)
            ->log(':causer.name has created a Org structure :subject.type');

        return redirect()->route('org-structure.index')->with([
            'message_success' => __('adminlte::org-structures.org_structure_create_success'),
        ]);
    }

    public function show($id)
    {
        $org_structure = OrgStructure::findOrFail(decrypt($id));
        // and the structyre is in OrgStructureTree model
        $structures = OrgStructureTree::with('user')->orderBy('reports_to_id', 'DESC')->where('org_structure_id', $org_structure->id)->get();
        $data_arr = [];

        // Loop through each structure and group them by their parent_id
        foreach ($structures as $structure) {
            $parent_id = $structure->reports_to_id ?? 'head'; // If reports_to_id is null, default to 'head'
            $data_arr[$parent_id] = $data_arr[$parent_id] ?? [];
            $data_arr[$parent_id][] = $structure;
        }

        // Generate the chart data recursively starting from the root node
        $chart_data = $this->generateChartData($data_arr, 'head');

        return view('pages.org-structures.show')->with([
            'org_structure' => $org_structure,
            'chart_data' => $chart_data[0] ?? [],
        ]);
    }

    // Helper function to generate the chart data recursively
    private function generateChartData(array $data_arr, string $parent_id, int $level = 0): array
    {
        $chart_data = [];

        // Loop through each child of the current node (if any) and generate their chart data recursively
        foreach ($data_arr[$parent_id] ?? [] as $key => $data) {
            $relationship = '1'.(count($data_arr[$parent_id]) > 1 ? '1' : '0'); // Determine the relationship between the current node and its parent
            $child_arr = $this->generateChartData($data_arr, $data->id, $level + 1); // Generate the chart data for the current node's children

            // Add the chart data for the current node to the array
            $chart_data[] = [
                'id' => $data->id, // Set the ID of the node
                'collapsed' => false,
                'verticalLevel' => $level, // Set the vertical level of the node
                'name' => ! empty($data->user_id) && $data->user ? $data->user->name : 'Vacant', // If the node has no user, label it as "Vacant"
                'title' => $data->title, // Retrieve the job title for the node
                'relationship' => $relationship, // Set the relationship between the node and its parent
                'children' => $child_arr, // Set the chart data for the node's children
            ];
        }

        // Return the chart data for the current node
        return $chart_data;
    }

    public function edit($id)
    {
        $org_structure = OrgStructure::findOrFail(decrypt($id));

        $users = User::select('id', 'name')->orderBy('name')->get();

        return view('pages.org-structures.edit')->with([
            'org_structure' => $org_structure,
            'users' => $users,
        ]);
    }

    public function update(OrgStructureEditRequest $request, $id)
    {
        $org_structure = OrgStructure::findOrFail(decrypt($id));

        $changes_arr['old'] = $org_structure->getOriginal();

        $org_structure->update([
            'user_id' => $request->user_id ? decrypt($request->user_id) : null,
            'type' => $request->type,
        ]);

        $changes_arr['changes'] = $org_structure->getChanges();

        // logs
        activity('updated')
            ->performedOn($org_structure)
            ->withProperties($changes_arr)
            ->log(':causer.name has updated a org structure :subject.type');

        return back()->with([
            'message_success' => __('adminlte::org-structures.org_structure_update_success'),
        ]);
    }
}

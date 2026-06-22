<?php

namespace App\Livewire\OrgStructures;

use App\Http\Traits\SettingTrait;
use App\Models\OrgStructureTree;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class Maintenance extends Component
{
    use SettingTrait;
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $type;

    public $org_structure;

    public $users;

    public $reports_to_id;

    public $user_id;

    public $title;

    public $selected_structure;

    public $delete_id;

    public function render()
    {
        $structure_trees = OrgStructureTree::where('org_structure_id', $this->org_structure->id)
            ->with('user', 'org_structure')
            ->orderBy('reports_to_id', 'ASC')
            ->paginate($this->getDataPerPage(), ['*'], 'structure-page');

        // Pre-fetch all parent structures in one query to avoid N+1
        $parentIds = $structure_trees->pluck('reports_to_id')->filter()->unique()->values();
        $parentStructures = $parentIds->isNotEmpty()
            ? OrgStructureTree::whereIn('id', $parentIds)->with('user')->get()->keyBy('id')
            : collect();

        $reports_to_arr = [];
        foreach ($structure_trees as $structure) {
            if (! empty($structure->reports_to_id)) {
                $structure_data = $parentStructures->get($structure->reports_to_id);
                $reports_to_arr[$structure->id] = $structure_data
                    ? $structure_data->title.' - '.(! empty($structure_data->user_id) && $structure_data->user ? $structure_data->user->name : 'Vacant')
                    : '';
            } else {
                $reports_to_arr[$structure->id] = '';
            }
        }

        $reportsToOptions = $this->org_structure->structure_trees->map(fn ($tree) => [
            'value' => $tree->id,
            'label' => ($tree->user->name ?? __('adminlte::org-structures.vacant')).' - '.$tree->title,
        ])->values()->all();

        return view('livewire.org-structures.maintenance', with([
            'structure_trees' => $structure_trees,
            'reports_to_arr' => $reports_to_arr,
            'reportsToOptions' => $reportsToOptions,
        ]));
    }

    public function mount($org_structure)
    {
        $this->users = User::select('id', 'name')->orderBy('name')->get();

        $this->type = 'add';
    }

    public function save()
    {
        $this->validate([
            'title' => [
                'required',
            ],
        ]);

        if ($this->type == 'add') {

            $org_structure_tree = new OrgStructureTree([
                'org_structure_id' => $this->org_structure->id,
                'user_id' => $this->user_id ?: null,
                'reports_to_id' => $this->reports_to_id ?: null,
                'title' => $this->title,
            ]);
            $org_structure_tree->save();

            // logs
            activity('created')
                ->performedOn($org_structure_tree)
                ->log(':causer.name has created a Org structure tree :subject.title');

        } elseif ($this->type == 'edit') {
            $changes_arr['old'] = $this->selected_structure->getOriginal();

            $this->selected_structure->update([
                'user_id' => $this->user_id ?? null,
                'reports_to_id' => $this->reports_to_id ?? null,
                'title' => $this->title,
            ]);

            $changes_arr['changes'] = $this->selected_structure->getChanges();

            // logs
            activity('updated')
                ->performedOn($this->selected_structure)
                ->withProperties($changes_arr)
                ->log(':causer.name has updated a Org structure tree :subject.title');
        }

        $this->resetForm();
        $this->updateChart();
    }

    public function resetForm()
    {
        $this->reports_to_id = null;
        $this->user_id = null;
        $this->title = null;
        $this->selected_structure = null;
        $this->type = 'add';
    }

    public function editStructure($tree_id)
    {
        $this->type = 'edit';

        $this->selected_structure = OrgStructureTree::findOrFail(decrypt($tree_id));
        // For edit, set the IDs as plain values (not encrypted) to match the <option value> in the edit form
        $this->reports_to_id = $this->selected_structure->reports_to_id ?? null;
        $this->user_id = $this->selected_structure->user_id ?? null;
        $this->title = $this->selected_structure->title;
    }

    public function addNew()
    {
        $this->type = 'add';
        $this->resetForm();
    }

    public function confirmDelete(int $id): void
    {
        $this->delete_id = $id;
    }

    public function cancelDelete(): void
    {
        $this->delete_id = null;
    }

    public function deleteStructure(): void
    {
        $structure = OrgStructureTree::findOrFail($this->delete_id);

        activity('deleted')
            ->performedOn($structure)
            ->withProperties($structure->toArray())
            ->log(':causer.name has deleted Org structure tree :subject.title');

        $structure->delete();

        $this->delete_id = null;
        $this->resetPage('structure-page');
        $this->updateChart();
    }

    public function updateChart()
    {
        $structures = OrgStructureTree::orderBy('reports_to_id', 'DESC')->where('org_structure_id', $this->org_structure->id)->get();
        $data_arr = [];

        // Loop through each structure and group them by their parent_id
        foreach ($structures as $structure) {
            $parent_id = $structure->reports_to_id ?? 'head'; // If reports_to_id is null, default to 'head'
            $data_arr[$parent_id] = $data_arr[$parent_id] ?? [];
            $data_arr[$parent_id][] = $structure;
        }

        // Generate the chart data recursively starting from the root node
        $chart_data = $this->generateChartData($data_arr, 'head');

        $this->dispatch('refresh-org-chart', new_data: $chart_data[0] ?? []);
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
}

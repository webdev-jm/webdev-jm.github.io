<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $activeTab = 'users';
    public $search = '';

    public $fullName;
    public $options_arr = [];

    /**
     * ADD A NEW MODEL HERE — everything else is automatic.
     *
     * 'tab_key' => [
     *     'label'      => 'Display Name',       // Tab label
     *     'icon'       => 'fas fa-icon',         // FontAwesome icon
     *     'model'      => ModelClass::class,     // Eloquent model
     *     'type'       => 'singular',            // Passed to restore/forceDelete
     *     'searchable' => ['col1', 'col2'],       // Columns to search (first = display column)
     *     'columns'    => [                      // Table header labels
     *         'col_name' => 'Header Label',
     *     ],
     * ]
     */
    protected function modelConfig(): array
    {
        return [
            'users' => [
                'label'      => 'Users',
                'icon'       => 'fas fa-users',
                'model'      => \App\Models\User::class,
                'type'       => 'user',
                'searchable' => ['name', 'email'],
                'columns'    => [
                    'name'  => 'Name',
                    'email' => 'Email',
                ],
            ],
            'companies' => [
                'label'      => 'Companies',
                'icon'       => 'fas fa-building',
                'model'      => \App\Models\Company::class,
                'type'       => 'company',
                'searchable' => ['name'],
                'columns'    => [
                    'name' => 'Company Name',
                ],
            ],
            'positions' => [
                'label'      => 'Positions',
                'icon'       => 'fas fa-briefcase',
                'model'      => \App\Models\Position::class,
                'type'       => 'position',
                'searchable' => ['position'],
                'columns'    => [
                    'position' => 'Position Title',
                ],
            ],
            'org_structures' => [
                'label'      => 'Org Structures',
                'icon'       => 'fas fa-code-branch',
                'model'      => \App\Models\OrgStructure::class,
                'type'       => 'org_structure',
                'searchable' => ['type'],
                'columns'    => [
                    'type' => 'Type',
                ],
            ]
        ];
    }

    public function setTab($tab)
    {
        abort_unless(array_key_exists($tab, $this->modelConfig()), 403);
        $this->activeTab = $tab;
        $this->resetPage();
        $this->search = '';
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function restore($id, $modelType)
    {
        $model = $this->getModel($modelType, $id);
        if ($model) {
            $model->restore();
            $this->dispatch('toast-message', [
                'type'    => 'success',
                'message' => ucfirst($modelType) . ' restored successfully.',
            ]);
        }
    }

    public function forceDelete($id, $modelType)
    {
        $model = $this->getModel($modelType, $id);
        if ($model) {
            $model->forceDelete();
            $this->dispatch('toast-message', [
                'type'    => 'success',
                'message' => ucfirst($modelType) . ' permanently deleted.',
            ]);
        }
    }

    private function getModel($modelType, $id)
    {
        $map = array_column($this->modelConfig(), 'model', 'type');

        if (!array_key_exists($modelType, $map)) {
            return null;
        }

        return $map[$modelType]::onlyTrashed()->findOrFail($id);
    }

    #[Computed]
    public function counts(): array
    {
        return array_map(
            fn($config) => $config['model']::onlyTrashed()->count(),
            $this->modelConfig()
        );
    }

    #[Computed]
    public function records()
    {
        $config = $this->modelConfig()[$this->activeTab];

        $query = $config['model']::onlyTrashed()
            ->where(function ($q) use ($config) {
                foreach ($config['searchable'] as $i => $column) {
                    $method = $i === 0 ? 'where' : 'orWhere';
                    $q->$method($column, 'like', "%{$this->search}%");
                }
            })
            ->orderBy('deleted_at', 'desc');

        return $query->paginate(10);
    }
};
?>

<div>
    <div class="row">
        <div class="col-12">
            <livewire:forms.select-field
                wire:model="fullName"
                label="Full Name"
                :options="\App\Models\User::all()"
                :searchable="true"
                :searchMin="0"
                :allowClear="true"
                name="fullName"
                optionValue="id"
                optionLabel="name"
            />
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card card-danger card-tabs">
                <div class="card-header p-0 pt-1">
                    <ul class="nav nav-tabs" role="tablist">
                        @foreach($this->modelConfig() as $tabKey => $config)
                            <li class="nav-item">
                                <a class="nav-link {{ $activeTab === $tabKey ? 'active' : '' }}"
                                   wire:click.prevent="setTab('{{ $tabKey }}')" href="#">
                                    <i class="{{ $config['icon'] }} mr-1"></i>
                                    {{ $config['label'] }} ({{ $this->counts[$tabKey] }})
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="card-body p-0">
                    <div class="p-3 border-bottom bg-light">
                        <div class="input-group input-group-sm" style="width: 300px;">
                            <input type="text" wire:model.live.debounce.300ms="search"
                                   class="form-control"
                                   placeholder="Search deleted {{ $this->modelConfig()[$activeTab]['label'] }}...">
                            <div class="input-group-append">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                        </div>
                    </div>

                    @php
                        $activeConfig = $this->modelConfig()[$activeTab];
                    @endphp

                    <div class="table-responsive">
                        <table class="table table-hover table-striped m-0 align-middle">
                            <thead>
                                <tr>
                                    @foreach($activeConfig['columns'] as $col => $label)
                                        <th>{{ $label }}</th>
                                    @endforeach
                                    <th>Deleted At</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($this->records as $record)
                                    <tr wire:key="row-{{ $record->id }}">
                                        @foreach($activeConfig['columns'] as $col => $label)
                                            <td>
                                                {{-- Profile image for the first column of users --}}
                                                @if($activeTab === 'users' && $loop->first)
                                                    <img src="{{ $record->adminlte_image() }}" alt="Profile"
                                                         class="mr-2" style="width:30px;height:30px;object-fit:cover;border-radius:50%;">
                                                @endif
                                                {{ $record->$col }}
                                            </td>
                                        @endforeach
                                        <td>{{ $record->deleted_at->format('M d, Y h:i A') }}</td>
                                        <td class="text-right">
                                            <button wire:click="restore({{ $record->id }}, '{{ $activeConfig['type'] }}')"
                                                    wire:confirm="Are you sure you want to restore this record?"
                                                    class="btn btn-sm btn-success mr-1" title="Restore">
                                                <i class="fas fa-trash-restore"></i> Restore
                                            </button>
                                            <button wire:click="forceDelete({{ $record->id }}, '{{ $activeConfig['type'] }}')"
                                                    wire:confirm="WARNING: This will permanently delete this record. Are you sure?"
                                                    class="btn btn-sm btn-danger" title="Permanently Delete">
                                                <i class="fas fa-times-circle"></i> Destroy
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ count($activeConfig['columns']) + 2 }}" class="text-center py-5 text-muted">
                                            <i class="fas fa-check-circle fa-3x mb-3 text-success d-block"></i>
                                            <h5>No deleted {{ strtolower($activeConfig['label']) }} found.</h5>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($this->records instanceof \Illuminate\Pagination\LengthAwarePaginator && $this->records->hasPages())
                    <div class="card-footer d-flex justify-content-end">
                        {{ $this->records->links(data: ['scrollTo' => false]) }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

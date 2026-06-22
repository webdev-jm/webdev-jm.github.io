<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{__('adminlte::org-structures.org_structure_maintenance')}}</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- LIST -->
                <div class="col-lg-6">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">{{__('adminlte::org-structures.structure_list')}}</h3>
                        </div>
                        <div class="card-body table-responsive p-0" style="max-height: 400px; overflow: auto;">
                            <table class="table table-sm table-striped table-hover mb-0 rounded">
                                <thead>
                                    <tr>
                                        <th>{{__('adminlte::users.user')}}</th>
                                        <th>{{__('adminlte::org-structures.title')}}</th>
                                        <th>{{__('adminlte::org-structures.reports_to')}}</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($structure_trees as $structure)
                                        <tr>
                                            <td class="align-middle">{{$structure->user->name ?? 'Vacant'}}</td>
                                            <td class="align-middle">{{$structure->title ?? '-'}}</td>
                                            <td class="align-middle">{{$reports_to_arr[$structure->id]}}</td>
                                            <td class="align-middle text-right">
                                                @if($delete_id === $structure->id)
                                                    <span class="text-danger text-xs mr-1">{{__('adminlte::utilities.delete')}}?</span>
                                                    <button class="btn btn-xs btn-danger" wire:click="deleteStructure" wire:loading.attr="disabled">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <button class="btn btn-xs btn-secondary" wire:click="cancelDelete">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                @else
                                                    <button class="btn btn-xs btn-primary" wire:click.prevent="editStructure('{{encrypt($structure->id)}}')">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-xs btn-danger" wire:click="confirmDelete({{$structure->id}})">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-3">
                                                {{__('adminlte::org-structures.no_structures')}}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer">
                            {{$structure_trees->links()}}
                        </div>
                    </div>
                </div>
                <!-- FORM -->
                <div class="col-lg-6">
                    <div class="card card-outline {{$type === 'add' ? 'card-primary' : 'card-success'}}">
                        <div class="card-header">
                            <h3 class="card-title">
                                {{$type === 'add'
                                    ? __('adminlte::org-structures.org_structure_form')
                                    : __('adminlte::utilities.edit').' '.__('adminlte::org-structures.org_structure')}}
                            </h3>
                            @if($type === 'edit')
                                <div class="card-tools">
                                    <button class="btn btn-xs btn-primary" wire:click.prevent="addNew()">
                                        <i class="fa fa-plus"></i>
                                        {{__('adminlte::org-structures.new_org_structure')}}
                                    </button>
                                </div>
                            @endif
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-6">
                                    <livewire:forms.select-field
                                        id="reports_to_id"
                                        name="reports_to_id"
                                        label="{{ __('adminlte::org-structures.reports_to') }}"
                                        :options="$reportsToOptions"
                                        :encryptModel="false"
                                        :searchable="true"
                                        :allowClear="true"
                                        size="sm"
                                        wire:model.live="reports_to_id"
                                    />
                                </div>

                                <div class="col-lg-6">
                                    <livewire:forms.select-field
                                        id="user_id"
                                        name="user_id"
                                        label="{{ __('adminlte::users.user') }}"
                                        :options="$users"
                                        :encryptModel="false"
                                        :searchable="true"
                                        :allowClear="true"
                                        optionValue="id"
                                        optionLabel="name"
                                        placeholder="{{ __('adminlte::org-structures.vacant') }}"
                                        size="sm"
                                        wire:model.live="user_id"
                                    />
                                </div>

                                <div class="col-lg-6">
                                    <livewire:forms.input-text
                                        id="title"
                                        name="title"
                                        label="{{ __('adminlte::org-structures.title') }}"
                                        placeholder="{{ __('adminlte::org-structures.title') }}"
                                        size="sm"
                                        wire:model.live="title"
                                    />
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-right">
                            <button class="btn btn-sm btn-secondary" wire:click.prevent="resetForm()" wire:loading.attr="disabled">
                                <i class="fa fa-recycle"></i>
                                {{__('adminlte::utilities.reset')}}
                            </button>
                            <button class="btn btn-sm btn-primary" wire:click.prevent="save()" wire:loading.attr="disabled" wire:target="save">
                                <span wire:loading.remove wire:target="save">
                                    <i class="fa fa-save"></i>
                                    {{__('adminlte::utilities.save')}}
                                </span>
                                <span wire:loading wire:target="save">
                                    <i class="fa fa-spinner fa-spin"></i>
                                    {{__('adminlte::utilities.save')}}...
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@extends('layouts.app')

{{-- Customize layout sections --}}
@section('subtitle', __('adminlte::org-structures.update_org_structure'))
@section('content_header_title', __('adminlte::org-structures.org_structures'))
@section('content_header_subtitle', __('adminlte::org-structures.update_org_structure'))

{{-- Content body: main page content --}}
@section('content_body')
    {{ html()->form('POST', route('org-structure.update', encrypt($org_structure->id)))->open() }}

        <div class="card">
            <div class="card-header py-2">
                <div class="row">
                    <div class="col-lg-6 align-middle">
                        <strong class="text-lg">{{__('adminlte::org-structures.update_org_structure')}}</strong>
                    </div>
                    <div class="col-lg-6 text-right">
                        <a href="{{route('org-structure.index')}}" class="btn btn-secondary btn-xs">
                            <i class="fa fa-caret-left"></i>
                            {{__('adminlte::utilities.back')}}
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">

                <div class="row">
                    
                    <div class="col-lg-4">
                        <livewire:forms.input-text
                            name="type"
                            id="type"
                            :value="$org_structure->type"
                            label="{{ __('adminlte::utilities.type') }}"
                            placeholder="{{ __('adminlte::utilities.type') }}"
                            size="sm"
                        />
                    </div>

                    <div class="col-lg-4">
                        <livewire:forms.select-field
                            name="user_id"
                            id="user_id"
                            :value="$org_structure->user_id"
                            label="{{ __('adminlte::users.user') }}"
                            :options="$users"
                            :searchable="true"
                            :searchMin="0"
                            :allowClear="true"
                            optionValue="id"
                            optionLabel="name"
                            size="sm"
                        />
                    </div>

                </div>

            </div>
            <div class="card-footer text-right">
                {{ html()->submit('<i class="fa fa-save"></i> '.__('adminlte::org-structures.update_org_structure'))->class(['btn', 'btn-primary', 'btn-sm']) }}
            </div>
        </div>

    {{ html()->form()->close() }}
@stop

{{-- Push extra CSS --}}
@push('css')
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@endpush

{{-- Push extra scripts --}}
@push('js')
    <script>
        $(function() {
        });
    </script>
@endpush
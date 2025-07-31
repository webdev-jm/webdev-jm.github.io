@extends('layouts.app')

{{-- Customize layout sections --}}
@section('subtitle', __('adminlte::roles.role_list'))
@section('content_header_title', __('adminlte::roles.roles'))
@section('content_header_subtitle', __('adminlte::roles.role_list'))

{{-- Content body: main page content --}}
@section('content_body')
    <div class="card">
        <div class="card-header py-2">
            <div class="row">
                <div class="col-lg-6 align-middle">
                    <strong class="text-lg">{{__('adminlte::roles.role_list')}}</strong>
                </div>
                <div class="col-lg-6 text-right">
                    @can('role create')
                        <a href="{{route('role.create')}}" class="btn btn-primary btn-xs">
                            <i class="fa fa-file"></i>
                            {{__('adminlte::roles.new_role')}}
                        </a>
                    @endcan
                </div>
            </div>
        </div>
        <div class="card-body">
            
            {{ html()->form('GET', route('role.index'))->open() }}
                <div class="row mb-1">
                    <div class="col-lg-4">
                        <div class="form-group">
                            {{ html()->label(__('adminlte::utilities.search'), 'search')->class('mb-0') }}
                            {{ html()->input('text', 'search', $search)->placeholder(__('adminlte::utilities.search'))->class(['form-control', 'form-control-sm'])}}
                        </div>
                    </div>
                </div>
            {{ html()->form()->close() }}
            
            <div class="row">
                <div class="col-12 table-responsive">
                    <table class="table table-sm table-striped table-hover mb-0 rounded">
                        <thead class="text-center bg-dark">
                            <tr class="text-center">
                                <th>{{__('adminlte::utilities.name')}}</th>
                                <th>{{__('adminlte::users.users')}}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($roles as $role)
                                <tr>
                                    <td class="align-middle text-center">
                                        {{$role->name}}
                                    </td>
                                    <td class="align-middle text-center">
                                        {{$role->users()->count()}}
                                    </td>
                                    <td class="align-middle text-right p-0 pr-1">
                                        <a href="{{route('role.show', encrypt($role->id, 'roles'))}}" class="btn btn-info btn-xs mb-0 ml-0">
                                            <i class="fa fa-list"></i>
                                            {{__('adminlte::utilities.view')}}
                                        </a>
                                        @can('role edit')
                                            <a href="{{route('role.edit', encrypt($role->id, 'roles'))}}" class="btn btn-success btn-xs mb-0 ml-0">
                                                <i class="fa fa-pen-alt"></i>
                                                {{__('adminlte::utilities.edit')}}
                                            </a>
                                        @endcan
                                        @can('role delete')
                                            <a href="#" class="btn btn-danger btn-xs mb-0 ml-0 btn-delete" data-id="{{encrypt($role->id)}}">
                                                <i class="fa fa-trash-alt"></i>
                                                {{__('adminlte::utilities.delete')}}
                                            </a>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
        <div class="card-footer">
            {{$roles->links()}}
        </div>
    </div>
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
            $('body').on('click', '.btn-delete', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                Livewire.dispatch('setDeleteModel', {type: 'Role', model_id: id});
                $('#modal-delete').modal('show');
            });
        });
    </script>
@endpush
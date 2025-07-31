@extends('layouts.app')

{{-- Customize layout sections --}}
@section('subtitle', __('adminlte::roles.role_details'))
@section('content_header_title', __('adminlte::roles.roles'))
@section('content_header_subtitle', __('adminlte::roles.role_details'))

{{-- Content body: main page content --}}
@section('content_body')
    <div class="row">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-6 align-middle">
                            <strong class="text-lg">{{__('adminlte::roles.role_details')}}</strong>
                        </div>
                        <div class="col-lg-6 text-right">
                            <a href="{{route('role.index')}}" class="btn btn-secondary btn-xs">
                                <i class="fa fa-caret-left"></i>
                                {{__('adminlte::utilities.back')}}
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body py-1">
                    <ul class="list-group list-group-unbordered">
                        <li class="list-group-item py-1 border-top-0">
                            <b class="text-uppercase">{{__('adminlte::roles.role_name')}}:</b>
                            <span class="float-right">{{$role->name ?? '-'}}</span>
                        </li>
                        <li class="list-group-item py-1">
                            <b class="text-uppercase">{{__('adminlte::utilities.created_at')}}:</b>
                            <span class="float-right">{{$role->created_at ?? '-'}}</span>
                        </li>
                        <li class="list-group-item py-1 border-bottom-0">
                            <b class="text-uppercase">{{__('adminlte::utilities.updated_at')}}:</b>
                            <span class="float-right">{{$role->updated_at ?? '-'}}</span>
                        </li>
                    </ul>
                </div>
            </div>
            
        </div>

        <div class="col-lg-8">
            <livewire:roles.users :role="$role"/>
        </div>

        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{__('adminlte::roles.permissions')}}</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($permissions_arr as $module => $permission_data)
                            <div class="col-lg-4">
                                <div class="card card-primary card-outline">
                                    <div class="card-header">
                                        <h3 class="card-title">{{$module}}</h3>
                                    </div>
                                    <div class="card-body py-0">
                                        <ul class="list-group list-group-unbordered">
                                            @foreach($permission_data as $id => $permission)
                                            <li class="list-group-item p-1">
                                                <b class="d-block">{{ucwords($permission['name'])}}</b>
                                                <small>{{$permission['description']}}</small>
                                            </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
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
    </script>
@endpush
@extends('layouts.app')

{{-- Customize layout sections --}}
@section('subtitle', __('adminlte::users.user_details'))
@section('content_header_title', __('adminlte::users.users'))
@section('content_header_subtitle', __('adminlte::users.user_details'))

{{-- Content body: main page content --}}
@section('content_body')
    <div class="row">
        <!-- USER DETAILS -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-6 align-middle">
                            <strong class="text-lg">{{__('adminlte::users.user_details')}}</strong>
                        </div>
                        <div class="col-6 text-right">
                            <a href="{{route('user.index')}}" class="btn btn-secondary btn-xs">
                                <i class="fa fa-caret-left"></i>
                                {{__('adminlte::utilities.back')}}
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body py-1">

                    <div class="text-center">
                        <img class="profile-user-img img-fluid img-circle"
                            src="{{$user->adminlte_image()}}"
                            alt="User profile picture">
                    </div>

                    <ul class="list-group list-group-unbordered">
                        <li class="list-group-item py-1 border-top-0">
                            <b class="text-uppercase">{{__('adminlte::utilities.status')}}:</b>
                            <span class="float-right">
                                @if($user->isOnline())
                                    {{__('adminlte::utilities.online')}}
                                    <i class="fa fa-circle text-success"></i>
                                @else
                                    {{__('adminlte::utilities.offline')}}
                                    <i class="fa fa-circle text-secondary"></i>
                                @endif
                            </span>
                        </li>
                        <li class="list-group-item py-1 border-top-0">
                            <b class="text-uppercase">{{__('adminlte::utilities.name')}}:</b>
                            <span class="float-right">{{$user->name ?? '-'}}</span>
                        </li>
                        <li class="list-group-item py-1">
                            <b class="text-uppercase">{{__('adminlte::utilities.email')}}:</b>
                            <span class="float-right">{{$user->email ?? '-'}}</span>
                        </li>
                        <li class="list-group-item py-1">
                            <b class="text-uppercase">{{__('adminlte::roles.roles')}}:</b>
                            <span class="float-right">{{implode(', ', $user->roles->pluck('name')->toArray()) ?? '-'}}</span>
                        </li>
                        <li class="list-group-item py-1">
                            <b class="text-uppercase">{{__('adminlte::companies.company')}}:</b>
                            <span class="float-right">{{$user->company->name ?? '-'}}</span>
                        </li>
                        <li class="list-group-item py-1">
                            <b class="text-uppercase">{{__('adminlte::positions.position')}}:</b>
                            <span class="float-right">{{$user->position->position ?? '-'}}</span>
                        </li>
                        <li class="list-group-item py-1">
                            <b class="text-uppercase">{{__('adminlte::utilities.created_at')}}:</b>
                            <span class="float-right">{{$user->created_at ?? '-'}}</span>
                        </li>
                        <li class="list-group-item py-1 border-bottom-0">
                            <b class="text-uppercase">{{__('adminlte::utilities.updated_at')}}:</b>
                            <span class="float-right">{{$user->updated_at ?? '-'}}</span>
                        </li>
                    </ul>
                </div>
            </div>

            @can('user change password')
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{__('adminlte::profile.change_password')}}</h3>
                    </div>
                    <div class="card-body">
                        <livewire:user.password :user="$user" type="user"/>
                    </div>
                </div>
            @endcan
        </div>
        <!--  -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <strong class="text-lg">{{__('adminlte::users.user_activities')}}</strong>
                </div>
                <div class="card-body">
                    <livewire:users.activities :user="$user"/>
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
@extends('layouts.app')

{{-- Customize layout sections --}}

@section('subtitle', __('adminlte::profile.profile'))
@section('content_header_title', __('adminlte::profile.profile'))
@section('content_header_subtitle', __('adminlte::utilities.details'))

{{-- Content body: main page content --}}

@section('content_body')
    <div class="row">
        <div class="col-md-3">

            <!-- Profile Image -->
            <div class="card card-primary card-outline">
                <div class="card-body box-profile">
                    <div class="text-center">
                        <img class="profile-user-img img-fluid img-circle"
                            src="{{$user->adminlte_image()}}"
                            alt="User profile picture">
                    </div>

                    <h3 class="profile-username text-center">{{$user->name}}</h3>

                    <p class="text-muted text-center">{{implode(', ', $user->getRoleNames()->toArray()) ?? '-'}}</p>

                    <ul class="list-group list-group-unbordered mb-3">
                        <li class="list-group-item">
                            <b>{{ __('adminlte::profile.followers') }}</b> <a class="float-right">1,322</a>
                        </li>
                        <li class="list-group-item">
                            <b>{{ __('adminlte::profile.following') }}</b> <a class="float-right">543</a>
                        </li>
                        <li class="list-group-item">
                            <b>{{ __('adminlte::profile.friends') }}</b> <a class="float-right">13,287</a>
                        </li>
                    </ul>
                </div>
            </div>
            
        </div>
        <!-- /.col -->
        <div class="col-md-9">
            <div class="card">
                <div class="card-header p-2">
                    <ul class="nav nav-pills">
                        <li class="nav-item"><a class="nav-link active" href="#activity" data-toggle="tab">{{__('adminlte::profile.activities')}}</a></li>
                        <li class="nav-item"><a class="nav-link" href="#settings" data-toggle="tab">{{__('adminlte::utilities.settings')}}</a></li>
                        <li class="nav-item"><a class="nav-link" href="#password" data-toggle="tab">{{__('adminlte::profile.change_password')}}</a></li>
                        <li class="nav-item"><a class="nav-link" href="#profile" data-toggle="tab">{{__('adminlte::profile.profile_picture')}}</a></li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <div class="active tab-pane" id="activity">
                            <livewire:users.activities :user="$user"/>
                        </div>

                        <div class="tab-pane" id="settings">
                            <livewire:users.settings :user="$user"/>
                        </div>

                        <div class="tab-pane" id="password">
                            <livewire:user.password :user="$user" type="profile"/>
                        </div>

                        <div class="tab-pane" id="profile">
                            <livewire:users.profile :user="$user"/>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
        
    </div>
@stop

{{-- Push extra CSS --}}

@push('css')
    {{-- Add here extra stylesheets --}}
    <style>
        .profile-img {
            width: 200px;
            height: 200px;
            border: 3px solid gray;
            padding: 3px;
        }
    </style>
@endpush

{{-- Push extra scripts --}}

@push('js')
@endpush
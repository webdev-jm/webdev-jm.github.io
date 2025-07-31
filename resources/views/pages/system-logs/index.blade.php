@extends('layouts.app')

{{-- Customize layout sections --}}
@section('subtitle', __('adminlte::systemlogs.system_logs'))
@section('content_header_title', __('adminlte::systemlogs.system_logs'))
@section('content_header_subtitle', __('adminlte::systemlogs.system_log_list'))

{{-- Content body: main page content --}}
@section('content_body')
    {{ html()->form('GET', route('system-logs'))->open() }}

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{__('adminlte::systemlogs.system_logs')}}</h3>
            </div>
            <div class="card-body">

                <div class="row mb-1">
                    <div class="col-lg-4">
                        <div class="form-group">
                            {{ html()->label(__('adminlte::utilities.search'), 'search')->class('mb-0') }}
                            {{ html()->input('text', 'search', $search)->placeholder(__('adminlte::utilities.search'))->class(['form-control', 'form-control-sm'])}}
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 table-responsive">
                        <table class="table table-sm table-striped table-hover mb-0 rounded">
                            <thead class="text-center bg-dark">
                                <tr>
                                    <th>{{__('adminlte::systemlogs.log_name')}}</th>
                                    <th>{{__('adminlte::systemlogs.log_description')}}</th>
                                    <th>{{__('adminlte::users.user')}}</th>
                                    <th>{{__('adminlte::systemlogs.changes')}}</th>
                                    <th>{{__('adminlte::systemlogs.timestamp')}}</th>
                                </tr>
                            </thead>
                            <tbody class="text-center">
                                @foreach($activities as $activity)
                                    <tr>
                                        <td>{{$activity->log_name}}</td>
                                        <td>{{$activity->description}}</td>
                                        <td>
                                            {{$activity->causer->name}}
                                        </td>
                                        <td class="p-1 text-xs">
                                            @if($activity->log_name == 'updated' && !empty($updates[$activity->id]))
                                                <ul class="list-group">
                                                    @foreach($updates[$activity->id] as $column => $data)
                                                        <li class="list-group-item p-0">
                                                            <p class="m-0 p-0">
                                                                <b>{{$column}}:</b> {!!$data['old']!!}
                                                            </p>
                                                            <p class="m-0 p-0 d-inline">
                                                                <b>to:</b> {!!$data['new']!!}
                                                            </p>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </td>
                                        <td>{{date('F j, Y H:i:s a', strtotime($activity->created_at))}}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
            <div class="card-footer">
                {{$activities->links()}}
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
    </script>
@endpush
@extends('layouts.app')

{{-- Customize layout sections --}}

@section('subtitle', 'Notifications')
@section('content_header_title', 'Notifications')
@section('content_header_subtitle', 'List')

{{-- Content body: main page content --}}

@section('content_body')
    <div class="card">
        <div class="card-header py-2">
            <div class="row">
                <div class="col-lg-12 align-middle">
                    <strong class="text-lg">NOTIFICATION LIST</strong>
                </div>
            </div>
        </div>
        <div class="card-body">

            {{ html()->form('GET', route('notifications'))->open() }}
                <div class="row mb-1">
                    <div class="col-lg-4">
                        <div class="form-group">
                            {{ html()->label('SEARCH', 'search')->class('mb-0') }}
                            {{ html()->input('text', 'search', $search)->placeholder('Search')->class(['form-control', 'form-control-sm'])}}
                        </div>
                    </div>
                </div>
            {{ html()->form()->close() }}
            
            <div class="row">
                <div class="col-12 table-responsive">
                    <table class="table table-sm table-striped table-hover bg-white mb-0 rounded">
                        <thead class="text-center bg-dark">
                            <tr class="text-center">
                                <th></th>
                                <th class="text-left">NAME</th>
                                <th>EMAIL</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($notifications as $notification)
                                <tr>
                                    <td>{{$notification->data['title']}}</td>
                                    <td>
                                        <a href="{{$notification->data['action_url']}}">
                                            {{$notification->data['message']}}
                                        </a>
                                    </td>
                                    <td>{{$notification->created_at->diffForHumans()}}</td>
                                    <td>
                                        @if(empty($notification->read_at))
                                        <i class="fa fa-circle text-danger"></i>
                                        @endif
                                    </td>
                                </tr>
                                @php
                                    $notification->markAsRead();
                                @endphp
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
        <div class="card-footer">
            {{$notifications->links()}}
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
@endpush
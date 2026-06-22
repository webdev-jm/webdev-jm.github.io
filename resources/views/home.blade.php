@extends('layouts.app')

{{-- Customize layout sections --}}

@section('subtitle', __('adminlte::adminlte.welcome'))
@section('content_header_title', __('adminlte::adminlte.home'))
@section('content_header_subtitle', __('adminlte::adminlte.welcome'))

{{-- Content body: main page content --}}

@section('content_body')
<div class="glass p-4">
    <p>{{ __('adminlte::adminlte.welcome_message') }}</p>
    <a href="{{route('test-notification')}}" class="btn btn-primary btn-sm">
        {{ __('adminlte::adminlte.test_notification') }}
    </a>

    <h1 class="text-3xl font-bold underline">
        {{ __('adminlte::adminlte.hello_world') }}
    </h1>
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

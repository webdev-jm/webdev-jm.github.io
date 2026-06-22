@extends('layouts.app')

{{-- Customize layout sections --}}
@section('subtitle', __('adminlte::utilities.system_settings'))
@section('content_header_title', __('adminlte::utilities.system_settings'))
@section('content_header_subtitle', __('adminlte::utilities.system_settings'))

{{-- Content body: main page content --}}
@section('content_body')
    <livewire:system-settings/>
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